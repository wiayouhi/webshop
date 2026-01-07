<?php
// 404.php

// 1. เช็คว่าเป็น AJAX (ระบบเรียก) หรือไม่
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// 2. เช็คว่าเป็นการเรียกหาไฟล์ JSON หรือไม่ (บางครั้ง Browser จะส่ง Accept header มา)
$is_json_request = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// --- ปรับเงื่อนไขตรงนี้ ---
// ถ้าเป็น AJAX หรือ ระบบเรียกมาจริงๆ ค่อยพ่น JSON
if ($is_ajax || $is_json_request) {
    header("HTTP/1.1 404 Not Found");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => '404 Not Found: ไม่พบข้อมูลหรือเซสชันหมดอายุ'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ถ้าไม่ใช่ (คือคนเปิดผ่าน Browser ตรงๆ) ให้ไหลลงไปแสดง HTML ด้านล่าง
header("HTTP/1.1 404 Not Found");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - ไม่พบหน้านี้ | My Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap');
        body { font-family: 'Kanit', sans-serif; background-color: #0f172a; }
        .bg-glow { box-shadow: 0 0 50px -12px rgba(139, 92, 246, 0.5); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen text-white">

    <div class="text-center px-4">
        <div class="relative inline-block mb-8">
            <h1 class="text-[120px] md:text-[180px] font-bold leading-none text-slate-800 select-none">404</h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fa-solid fa-ghost text-6xl md:text-8xl text-purple-500 animate-bounce"></i>
            </div>
        </div>

        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">อุ๊ปส์! หาหน้านี้ไม่เจอ</h2>
        <p class="text-gray-400 mb-10 max-w-md mx-auto">
            หน้าที่คุณกำลังตามหาอาจจะถูกย้าย หรือคุณยังไม่ได้เข้าสู่ระบบ <br>
            ลองกลับไปตั้งหลักที่หน้าแรกดูไหม?
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-full font-semibold transition-all duration-300 transform hover:scale-105 bg-glow">
                <i class="fa-solid fa-house mr-2"></i> กลับหน้าหลัก
            </a>
            <button onclick="history.back()" class="bg-slate-800 hover:bg-slate-700 text-white px-8 py-3 rounded-full font-semibold transition-all duration-300">
                <i class="fa-solid fa-arrow-left mr-2"></i> ย้อนกลับ
            </button>
        </div>

        <div class="mt-20 text-gray-600 text-sm italic">
            "บางอย่างหายไปในความมืด..."
        </div>
    </div>

</body>
</html>