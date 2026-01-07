<?php
include '../db.php';

// ดักทางเข้าที่นี่ที่เดียว
if (!isset($_SESSION['user_id'])) {
    trigger404();
}

// ถ้าผ่าน ให้ดึงไฟล์ API ที่ต้องการมาทำงาน
$action = $_GET['action'] ?? '';
$file = "endpoints/{$action}.php";

if (file_exists($file)) {
    include $file;
} else {
    trigger404();
}