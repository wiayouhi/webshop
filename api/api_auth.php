<?php
// api/api_auth.php
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    // เรียกใช้ฟังก์ชัน trigger404 ที่เราเขียนไว้ใน db.php
    // ฟังก์ชันนี้จะส่ง Header 404 และแสดงหน้า HTML 404 ปกติ (ไม่ใช่ JSON)
    if (function_exists('trigger404')) {
        trigger404();
    } else {
        // กรณีฟังก์ชันไม่มี ให้ส่ง Header เองแล้วตายทันที
        header("HTTP/1.1 404 Not Found");
        echo "<h1>404 Not Found</h1>";
        echo "The requested URL was not found on this server.";
        exit;
    }
}
// ถ้าผ่านตรงนี้ไปได้ แสดงว่า Login แล้ว ถึงจะทำงานส่วนที่เป็น JSON ต่อได้