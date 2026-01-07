<?php
// admin/admin_auth.php
require_once __DIR__ . '/../db.php'; // ถอยออกไป 1 ชั้นเพื่อดึงไฟล์ db.php

// เรียกใช้ฟังก์ชันตรวจสอบ Admin
checkAdmin($pdo);

// ถ้าไม่ใช่ Admin โค้ดจะหยุดที่ฟังก์ชันและเด้งไปหน้าหลักทันที
?>