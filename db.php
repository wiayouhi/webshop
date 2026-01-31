<?php
/**
 * DATABASE CONNECTION & SECURITY CONFIGURATION
 * Stealth Mode: ส่งค่า 404 Not Found เมื่อมีการบุกรุก
 */

// -------------------------------------------------------------------------
// 🕵️ STEALTH FUNCTION: ฟังก์ชันส่งหน้า 404 เพื่อตบตาแฮกเกอร์
// -------------------------------------------------------------------------
function trigger404() {
    // ส่ง HTTP Status 404 บอก Browser/Scanner ว่าไม่พบไฟล์นี้
    header("HTTP/1.1 404 Not Found");

    // ดึงไฟล์ 404.php มาแสดง (ถ้ามี) โดยที่ URL เดิมจะไม่เปลี่ยน
    if (file_exists(__DIR__ . '/404.php')) {
        include(__DIR__ . '/404.php');
    } else {
        // กรณีไม่มีไฟล์ 404.php ให้แสดงหน้ามาตรฐานของ Apache/Nginx
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head><title>404 Not Found</title></head><body>
        <h1>Not Found</h1>
        <p>The requested URL was not found on this server.</p>
        <hr><address>Apache Server at ' . $_SERVER['HTTP_HOST'] . ' Port 80</address>
        </body></html>';
    }
    exit; // หยุดการทำงานทันที
}

// 1. ป้องกันการเข้าถึงไฟล์ db.php โดยตรงผ่าน URL
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    trigger404();
}

// -------------------------------------------------------------------------
// 🛡️ SECURITY HEADERS: ป้องกัน Clickjacking, Sniffing และ XSS
// -------------------------------------------------------------------------
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';");

// บังคับการตั้งค่า Cookie ให้ปลอดภัย
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// หากใช้งาน HTTPS (SSL) ให้เปิดบรรทัดล่างนี้:
// ini_set('session.cookie_secure', 1); 

ob_start(); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set("Asia/Bangkok");

// -------------------------------------------------------------------------
// 🛡️ CSRF PROTECTION: สร้าง Token สำหรับฟอร์มป้องกันการปลอมแปลงคำสั่ง
// -------------------------------------------------------------------------
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

// -------------------------------------------------------------------------
// 🔌 DATABASE CONNECTION (PDO)
// -------------------------------------------------------------------------
$host = 'localhost';
$dbname = 'shop_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch(PDOException $e) {
    // เก็บ Error ไว้ใน Log (ห้ามแสดงบนหน้าเว็บเด็ดขาด)
    error_log("Database Connection Error: " . $e->getMessage()); 
    // แกล้งตายหรือบอกแค่ว่าระบบขัดข้องชั่วคราว
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

// ค่า Default หากไม่มีข้อมูลในฐานข้อมูล
if (!$web_config) {
    $web_config = (object)[
        'site_name' => 'My Shop',
        'site_color' => 'purple',
        'site_logo' => '',
        'marquee_text' => '',
        'background_img' => ''
    ];
}

// -------------------------------------------------------------------------
// 👮 AUTHENTICATION FUNCTIONS
// -------------------------------------------------------------------------

// ฟังก์ชันตรวจสอบการล็อกอินทั่วไป
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// ฟังก์ชันตรวจสอบแอดมิน (Stealth Mode)
// ใครก็ตามที่ไม่ใช่ Admin เข้ามาหน้านี้ จะเห็นเป็น 404 ทันที
function checkAdmin($pdo) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        trigger404();
    }
}
?>