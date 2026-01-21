<?php
/**
 * DATABASE CONNECTION & SECURITY CONFIGURATION
 * Stealth Mode: ส่งค่า 404 Not Found เมื่อมีการบุกรุก
 */

// -------------------------------------------------------------------------
// 🕵️ STEALTH FUNCTION: ฟังก์ชันส่งหน้า 404 เพื่อตบตาแฮกเกอร์
// -------------------------------------------------------------------------
function trigger404() {
    header("HTTP/1.1 404 Not Found");
    if (file_exists(__DIR__ . '/404.php')) {
        include(__DIR__ . '/404.php');
    } else {
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head><title>404 Not Found</title></head><body>
        <h1>Not Found</h1>
        <p>The requested URL was not found on this server.</p>
        <hr><address>Apache Server at ' . $_SERVER['HTTP_HOST'] . ' Port 80</address>
        </body></html>';
    }
    exit;
}

// 1. ป้องกันการเข้าถึงไฟล์ db.php โดยตรงผ่าน URL
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    trigger404();
}

// -------------------------------------------------------------------------
// 🛡️ SECURITY HEADERS
// -------------------------------------------------------------------------
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';");

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// บน Render เป็น HTTPS อยู่แล้ว เปิดบรรทัดนี้ได้เลยครับ
ini_set('session.cookie_secure', 1); 

ob_start(); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set("Asia/Bangkok");

// -------------------------------------------------------------------------
// 🛡️ CSRF PROTECTION
// -------------------------------------------------------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// -------------------------------------------------------------------------
// 🔌 DATABASE CONNECTION (PDO) - ปรับปรุงสำหรับ Render
// -------------------------------------------------------------------------
// พยายามดึงค่าจาก Environment Variables ถ้าไม่มีให้ใช้ localhost (สำหรับรันในเครื่องตัวเอง)
$host     = getenv('DB_HOST')     ?: 'localhost';
$dbname   = getenv('DB_NAME')     ?: 'shop_db';
$username = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASS')     ?: '';
$port     = getenv('DB_PORT')     ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    // ตั้งค่า Options สำหรับ PDO ให้รองรับ SSL
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        // บรรทัดสำคัญ: บังคับใช้ SSL Connection
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch(PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage()); 
    die("<h1>Service Unavailable</h1><p>The server is temporarily unable to service your request.</p>");
}

// -------------------------------------------------------------------------
// ⚙️ FETCH SITE CONFIGURATION
// -------------------------------------------------------------------------
try {
    $stmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
    $stmt->execute();
    $web_config = $stmt->fetch();
} catch (Exception $e) {
    $web_config = null;
}

if (!$web_config) {
    $web_config = (object)[
        'site_name' => 'My Shop',
        'site_color' => 'purple',
        'site_logo' => '',
        'marquee_text' => '',
        'background_img' => ''
    ];
}

// 👮 AUTHENTICATION FUNCTIONS
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function checkAdmin($pdo) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        trigger404();
    }
}
?>

