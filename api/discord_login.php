<?php
// 1. ย้าย db.php ขึ้นมาไว้บรรทัดแรกสุด (ก่อน session_start)
// เพื่อให้ db.php ตั้งค่า ini_set() ได้ก่อนที่ Session จะเริ่ม

require_once '../db.php'; 

// ตรวจสอบว่า Session เริ่มหรือยัง ถ้ายังให้เริ่ม (ป้องกัน Error ซ้ำซ้อน)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// ⚙️ ดึงค่า Discord Config จาก Database
// ==========================================
$stmt = $pdo->prepare("SELECT discord_client_id, discord_client_secret, discord_redirect_uri FROM settings LIMIT 1");
$stmt->execute();

// 2. แก้ Fatal Error: บังคับให้ดึงข้อมูลเป็น Array (PDO::FETCH_ASSOC)
$config = $stmt->fetch(PDO::FETCH_ASSOC); 

if (!$config || empty($config['discord_client_id']) || empty($config['discord_client_secret'])) {
    die("❌ Error: กรุณาตั้งค่า Discord Login ในระบบหลังบ้านก่อน (Admin -> Settings)");
}

$discord_client_id = $config['discord_client_id'];
$discord_client_secret = $config['discord_client_secret'];
$redirect_uri = $config['discord_redirect_uri']; 
// ==========================================

// 3. ถ้าไม่มี Code ส่งมา ให้ Redirect ไปขออนุญาตที่ Discord
if (!isset($_GET['code'])) {
    $params = [
        'client_id' => $discord_client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'identify email'
    ];
    header('Location: https://discord.com/api/oauth2/authorize?' . http_build_query($params));
    exit;
}

// 4. ถ้ามี Code แล้ว ให้นำไปแลก Access Token
if (isset($_GET['code'])) {
    // แลก Code เป็น Token
    $token_request = [
        'client_id' => $discord_client_id,
        'client_secret' => $discord_client_secret,
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $redirect_uri
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_request));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $token_response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // เช็คว่าได้ Token ไหม
    if (!isset($token_response['access_token'])) {
        // Debug Error message เพื่อดูว่าผิดตรงไหน
        echo "<h3>Error Connect Discord:</h3>";
        echo "<pre>"; print_r($token_response); echo "</pre>";
        die("ตรวจสอบ Client Secret หรือ Redirect URI ในหน้าตั้งค่าอีกครั้ง");
    }

    // 5. ใช้ Token ดึงข้อมูล User Profile
    $access_token = $token_response['access_token'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/users/@me');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $user_info = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // เตรียมข้อมูล
    $discord_id = $user_info['id'];
    $discord_username = $user_info['username'];
    $email = $user_info['email'] ?? '';
    $avatar = $user_info['avatar'] 
        ? "https://cdn.discordapp.com/avatars/$discord_id/{$user_info['avatar']}.png" 
        : "https://cdn.discordapp.com/embed/avatars/0.png";

    // 6. ตรวจสอบในฐานข้อมูล (เปลี่ยน fetch เป็น FETCH_ASSOC เหมือนกันเพื่อความชัวร์)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // --- กรณี A: เคยเชื่อมต่อแล้ว (Login) ---
        // เนื่องจาก fetch แบบ ASSOC ต้องใช้ key เป็น string (ไม่ใช่ ->)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['point'] = $user['point'];
        $_SESSION['profile_img'] = $user['profile_img'];

    } else {
        // --- กรณี B: ยังไม่เคยเชื่อมต่อ (Register ใหม่) ---
        $username = $discord_username;
        $checkUser = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $checkUser->execute([$username]);
        if ($checkUser->fetchColumn() > 0) {
            $username = $username . "_" . rand(100, 999);
        }

        $random_pass = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, point, discord_id, email, profile_img) VALUES (?, ?, 'member', 0, ?, ?, ?)");
        
        if ($stmt->execute([$username, $random_pass, $discord_id, $email, $avatar])) {
            $new_user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'member';
            $_SESSION['point'] = 0;
            $_SESSION['profile_img'] = $avatar;
        } else {
            die("Error: สมัครสมาชิกไม่สำเร็จ");
        }
    }

    // Login สำเร็จ -> ไปหน้าแรก
    echo "<script>window.location='../index.php';</script>";
    exit;
}
?>