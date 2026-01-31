<?php
// check_balance.php
require_once 'api_auth.php';
header('Content-Type: application/json');
session_start();
require_once '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT point FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if($u) {
        $_SESSION['point'] = $u['point'];
        echo json_encode(['status'=>'success', 'point'=>number_format($u['point'],2)]);
    } else { echo json_encode(['status'=>'error']); }
} catch(Exception $e) { echo json_encode(['status'=>'error']); }
?>