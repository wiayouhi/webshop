<?php
header('Content-Type: application/json');
session_start();
require_once '../db.php'; // ตรวจสอบ path ไฟล์ db.php ให้ถูกต้อง

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    // ดึงยอดเงินล่าสุดจาก Database
    $stmt = $pdo->prepare("SELECT point FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // อัปเดต Session และส่งค่ากลับ
        $_SESSION['point'] = $user['point'];
        echo json_encode([
            'success' => true,
            'points' => floatval($user['point'])
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>