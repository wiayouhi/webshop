<?php
require_once 'api_auth.php';
// api/redeem.php
header('Content-Type: application/json; charset=utf-8');

// ปิด Error HTML เพื่อไม่ให้ JSON พัง
ini_set('display_errors', 0);
error_reporting(0);

// เริ่ม Session ถ้ายั่งไม่ได้เริ่ม (เพื่อป้องกัน error session ซ้ำกับ api_auth.php)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// เรียกไฟล์เชื่อมต่อฐานข้อมูล
$db_path = '../db.php';
if (file_exists($db_path)) {
    require_once $db_path;
} else {
    // กรณีหาไฟล์ไม่เจอ ให้ลอง path ปัจจุบัน
    if(file_exists('db.php')) require_once 'db.php';
}

// --- ส่วนสำคัญ: แปลงค่าที่ส่งมา (รองรับทั้ง JSON และ POST ปกติ) ---
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

// ถ้ามีการส่งแบบ JSON ให้เอาค่ามาใส่ตัวแปร $code
if (isset($input['code'])) {
    $code = $input['code'];
} elseif (isset($_POST['code'])) {
    $code = $_POST['code'];
} else {
    $code = '';
}
// -----------------------------------------------------------

// เช็คว่าเชื่อมต่อ DB ได้ไหม
if (!isset($pdo)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้']);
    exit;
}

// 1. ตรวจสอบล็อกอิน
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบก่อน']);
    exit;
}

// 2. เช็คว่ามีโค้ดไหม
if (empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกโค้ด']);
    exit;
}

$code = strtoupper(trim($code)); // แปลงเป็นตัวใหญ่
$user_id = $_SESSION['user_id'];

try {
    // เช็คว่ามีตารางไหม
    $checkTable = $pdo->query("SHOW TABLES LIKE 'redeem_codes'");
    if($checkTable->rowCount() == 0) {
        throw new Exception("ไม่พบตาราง redeem_codes ในฐานข้อมูล");
    }

    $pdo->beginTransaction();

    // 3. ค้นหาโค้ด
    $stmt = $pdo->prepare("SELECT * FROM redeem_codes WHERE code = ? FOR UPDATE");
    $stmt->execute([$code]);
    $redeem = $stmt->fetch();

    if (!$redeem) {
        throw new Exception("ไม่พบโค้ดนี้ในระบบ หรือโค้ดไม่ถูกต้อง");
    }

    // 4. เช็คเงื่อนไข
    if ($redeem->used_count >= $redeem->max_uses) {
        throw new Exception("โค้ดนี้สิทธิ์เต็มแล้ว");
    }

    // เช็คประวัติการใช้
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM redeem_history WHERE user_id = ? AND code_id = ?");
    $stmt->execute([$user_id, $redeem->id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("คุณเคยใช้โค้ดนี้ไปแล้ว");
    }

    // 5. ให้รางวัล
    $stmt = $pdo->prepare("UPDATE users SET point = point + ? WHERE id = ?");
    $stmt->execute([$redeem->reward, $user_id]);

    // 6. อัปเดตจำนวนการใช้
    $stmt = $pdo->prepare("UPDATE redeem_codes SET used_count = used_count + 1 WHERE id = ?");
    $stmt->execute([$redeem->id]);

    // 7. บันทึกประวัติ
    $stmt = $pdo->prepare("INSERT INTO redeem_history (user_id, code_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $redeem->id]);

    // อัปเดต Session
    if(isset($_SESSION['point'])) {
        $_SESSION['point'] += $redeem->reward;
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success', 
        'message' => 'สำเร็จ! คุณได้รับเงินรางวัล ' . number_format($redeem->reward, 2) . ' เครดิต'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>