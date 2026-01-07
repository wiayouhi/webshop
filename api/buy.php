<?php
require_once 'api_auth.php';
header('Content-Type: application/json; charset=utf-8');
?>
<?php
// api/buy.php
header('Content-Type: application/json');
require_once '../db.php';

// 1. ตรวจสอบล็อกอิน
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบก่อนทำรายการ']);
    exit;
}

// 2. รับค่า
if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลสินค้าไม่ถูกต้อง']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1; 

if ($qty < 1) {
    echo json_encode(['status' => 'error', 'message' => 'จำนวนสินค้าต้องมากกว่า 0']);
    exit;
}

try {
    // เริ่ม Transaction
    $pdo->beginTransaction();

    // 3. ดึงข้อมูลสินค้าและราคาล่าสุด
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("ไม่พบสินค้านี้ในระบบ");
    }

    // 4. คำนวณราคารวม
    $total_price = $product->price * $qty;

    // 5. ดึงข้อมูล User (เงินปัจจุบัน) เพื่อเช็คอีกครั้ง
    $stmt = $pdo->prepare("SELECT point FROM users WHERE id = ? FOR UPDATE"); 
    $stmt->execute([$user_id]);
    $user_point = $stmt->fetchColumn();

    if ($user_point < $total_price) {
        throw new Exception("ยอดเงินของคุณไม่เพียงพอ (ขาด " . number_format($total_price - $user_point, 2) . " บาท)");
    }

    // 6. เช็คสต็อกและดึงของ
    // *** แก้ไขจุดที่ Error: ใช้ bindValue เพื่อบังคับเป็นตัวเลข (INT) สำหรับ LIMIT ***
    $stmt = $pdo->prepare("SELECT id, contents FROM stocks WHERE product_id = ? AND is_sold = 0 LIMIT ? FOR UPDATE");
    $stmt->bindValue(1, $product_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $qty, PDO::PARAM_INT);
    $stmt->execute();
    $stocks_to_buy = $stmt->fetchAll();

    if (count($stocks_to_buy) < $qty) {
        throw new Exception("สินค้าคงเหลือไม่พอ (เหลือ " . count($stocks_to_buy) . " ชิ้น)");
    }

    // --- เริ่มกระบวนการตัดยอด ---

    // 7. ตัดเงิน User
    $stmt = $pdo->prepare("UPDATE users SET point = point - ? WHERE id = ?");
    $stmt->execute([$total_price, $user_id]);

    // อัปเดต Session ให้แสดงเงินล่าสุดทันที
    if(isset($_SESSION['point'])) {
        $_SESSION['point'] -= $total_price;
    }

    // 8. บันทึกการซื้อและอัปเดตสต็อก
    $bought_items_str = ""; 
    
    foreach ($stocks_to_buy as $stock_item) {
        // อัปเดตสถานะว่าขายแล้ว
        $stmt = $pdo->prepare("UPDATE stocks SET is_sold = 1 WHERE id = ?");
        $stmt->execute([$stock_item->id]);

       // บันทึกลงตาราง Orders (เพิ่ม status เป็น success ทันที)
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, product_name, price, data_received, status) VALUES (?, ?, ?, ?, ?, 'success')");
        $stmt->execute([$user_id, $product_id, $product->name, $product->price, $stock_item->contents]);

        $bought_items_str .= "<div class='bg-slate-800 p-3 rounded mt-2 text-left border border-slate-600 text-green-400 font-mono break-all select-all'>" . htmlspecialchars($stock_item->contents) . "</div>";
    }

    // จบ Transaction
    $pdo->commit();

    // 9. ส่งค่ากลับ
    echo json_encode([
        'status' => 'success',
        'message' => "ซื้อสำเร็จ!<br>คุณได้รับสินค้า:<br>" . $bought_items_str
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>