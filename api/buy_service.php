<?php
require_once 'api_auth.php';
header('Content-Type: application/json; charset=utf-8');
?>
<?php
// api/buy_service.php

// 1. เริ่ม Buffer และปิด Error ที่จะโชว์หน้าเว็บ (ป้องกัน JSON พัง)
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL); // ให้ไปลง log แทนการ echo ออกมา

session_start();
require_once '../db.php'; // เรียกไฟล์ DB

// เตรียมตัวแปร response
$response = [];

// ... ส่วน Logic การเช็ค Login และ ค่าต่างๆ (เหมือนเดิม) ...
if (!isset($_SESSION['user_id'])) {
    cleanAndResponse(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบ']);
}

$user_id = $_SESSION['user_id'];
// รับค่าและแปลงเป็นตัวเลขกัน Error
$package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
$note = isset($_POST['note']) ? $_POST['note'] : '';

if ($package_id == 0) {
    cleanAndResponse(['status' => 'error', 'message' => 'ข้อมูลแพ็กเกจไม่ถูกต้อง']);
}

try {
    $pdo->beginTransaction();

    // 1. ดึงข้อมูลแพ็กเกจ (ตรวจสอบว่าบริการเปิดอยู่ด้วย is_active = 1)
    $pkgStmt = $pdo->prepare("
        SELECT sp.*, s.name as game_name 
        FROM service_packages sp 
        JOIN services s ON sp.service_id = s.id 
        WHERE sp.id = ? AND s.is_active = 1
    ");
    $pkgStmt->execute([$package_id]);
    $package = $pkgStmt->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        throw new Exception("ไม่พบแพ็กเกจสินค้า หรือบริการนี้ปิดปรับปรุง");
    }

    $price = floatval($package['price']);
    $product_name = $package['game_name'] . " (" . $package['name'] . ")";

    // 2. เช็คเงิน User (Lock row เพื่อความชัวร์)
    $stmt = $pdo->prepare("SELECT point FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user_point = $stmt->fetchColumn();

    if ($user_point < $price) {
        throw new Exception("ยอดเงินไม่เพียงพอ (ขาด " . number_format($price - $user_point, 2) . " บาท)");
    }

    // 3. หักเงิน
    $new_point = $user_point - $price;
    $stmt = $pdo->prepare("UPDATE users SET point = ? WHERE id = ?");
    $stmt->execute([$new_point, $user_id]);
    $_SESSION['point'] = $new_point; // อัปเดต session

    // 4. บันทึก Order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, product_id, product_name, price, data_received, note, status, purchased_at) 
        VALUES (?, ?, ?, ?, '-', ?, 'pending', NOW())
    ");
    $stmt->execute([$user_id, 0, $product_name, $price, $note]);

    $pdo->commit();
    
    // ส่งค่า Success
    cleanAndResponse(['status' => 'success']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    cleanAndResponse(['status' => 'error', 'message' => $e->getMessage()]);
    
}

// ฟังก์ชันสำหรับล้าง Buffer และส่ง JSON
function cleanAndResponse($data) {
    ob_end_clean(); // ล้างทุกอย่างที่เคย echo ก่อนหน้านี้ (เช่น spaces ใน db.php)
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
