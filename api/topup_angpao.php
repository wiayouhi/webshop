<?php
require_once 'api_auth.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php'; // ตรวจสอบว่า Path ถูกต้องตามโครงสร้างโปรเจกต์

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

// ตรวจสอบการส่งค่าลิงก์
if (!isset($_POST['link'])) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบลิงก์ซองของขวัญ']);
    exit;
}

$link = trim($_POST['link']);
$user_id = $_SESSION['user_id'];

// 1. ตรวจสอบรูปแบบลิงก์ TrueMoney
if (strpos($link, 'gift.truemoney.com') === false) {
    echo json_encode(['status' => 'error', 'message' => 'รูปแบบลิงก์ไม่ถูกต้อง']);
    exit;
}

// 2. เช็คลิงก์ซ้ำในฐานข้อมูล
$stmt = $pdo->prepare("SELECT COUNT(*) FROM topups WHERE reference_code = ? AND status = 'success'");
$stmt->execute([$link]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'ซองนี้ถูกใช้งานไปแล้ว']);
    exit;
}

// 3. ดึงค่า Config จากตาราง settings
$stmt = $pdo->prepare("SELECT payment_tm_phone, payment_tm_api_url FROM settings LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$my_phone = $config->payment_tm_phone ?? '';
$api_url = $config->payment_tm_api_url ?? '';

if (empty($my_phone)) {
    echo json_encode(['status' => 'error', 'message' => 'ระบบยังไม่ได้ตั้งค่าเบอร์รับเงิน (ติดต่อแอดมิน)']);
    exit;
}

if (empty($api_url)) {
    echo json_encode(['status' => 'error', 'message' => 'ระบบยังไม่ได้ตั้งค่า API URL (ติดต่อแอดมิน)']);
    exit;
}

// 4. เรียกใช้งาน API
// ตรวจสอบว่าใน URL มี ? หรือยัง เพื่อเชื่อม Parameter ให้ถูก
$separator = (strpos($api_url, '?') !== false) ? '&' : '?';
$target_url = $api_url . $separator . "link=" . urlencode($link) . "&phone=" . urlencode($my_phone);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_TIMEOUT, 20); 
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อ API ตรวจสอบซองได้']);
    exit;
}

// 5. แปลงผลลัพธ์และอัปเดตยอดเงิน (แก้ไขจุดนี้)
$result = json_decode($response, true);
$is_success = false;
$amount_received = 0;

// ดึงค่า status มาตรวจสอบโดยแปลงเป็นตัวพิมพ์ใหญ่ทั้งหมดเพื่อให้ชัวร์
// Python ส่งมาเป็น "SUCCESS" แต่บางที PHP เช็ค "success" โค้ดนี้จะรองรับทั้งคู่
$status_check = isset($result['status']) ? strtoupper(strval($result['status'])) : '';

if ($status_check === 'SUCCESS' || $status_check === '200' || (isset($result['status']) && $result['status'] === true)) {
    $is_success = true;
    $amount_received = floatval($result['amount']); // แปลงเป็นตัวเลข
} elseif (isset($result['code']) && $result['code'] == 200) {
    // เผื่อ API บางเจ้าใช้ key 'code'
    $is_success = true;
    $amount_received = floatval($result['amount']);
} else {
    // ดึงข้อความ error
    $error_msg = $result['message'] ?? ($result['msg'] ?? 'ซองไม่ถูกต้องหรือหมดอายุ');
    echo json_encode(['status' => 'error', 'message' => $error_msg]);
    exit;
}

if ($is_success && $amount_received > 0) {
    try {
        $pdo->beginTransaction();

        // เพิ่ม Point ให้ User
        $stmt = $pdo->prepare("UPDATE users SET point = point + ? WHERE id = ?");
        $stmt->execute([$amount_received, $user_id]);

        // บันทึกประวัติการเติมเงิน
        $stmt = $pdo->prepare("INSERT INTO topups (user_id, amount, method, reference_code, status) VALUES (?, ?, 'angpao', ?, 'success')");
        $stmt->execute([$user_id, $amount_received, $link]);

        // อัปเดตยอดเงินใน Session เพื่อให้หน้าเว็บแสดงผลทันที
        if(isset($_SESSION['point'])) {
            $_SESSION['point'] += $amount_received;
        }

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'amount' => $amount_received,
            'message' => 'เติมเงินสำเร็จ'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดทางฐานข้อมูล']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ยอดเงินไม่ถูกต้อง (0 บาท)']);
}
?>