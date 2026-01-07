<?php include 'admin_auth.php'; ?>
<?php
// admin/products.php
require_once 'header.php';

// Logic: ลบสินค้า
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // ลบรูปภาพเก่า (ถ้ามี)
    $stmt = $pdo->prepare("SELECT img FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("../" . $img)) { unlink("../" . $img); }

    // ลบจาก Database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "<script>Swal.fire('ลบสำเร็จ', 'ลบสินค้าเรียบร้อยแล้ว', 'success').then(() => window.location='products.php');</script>";
    }
}

// ดึงข้อมูลสินค้า + หมวดหมู่ + จำนวนสต็อกที่เหลือ
$sql = "SELECT p.*, c.name as category_name, 
        (SELECT COUNT(*) FROM stocks s WHERE s.product_id = p.id AND s.is_sold = 0) as stock_count
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$products = $pdo->query($sql)->fetchAll();
?>

<div class="container mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-bold">จัดการสินค้า</h2>
            <p class="text-gray-400">รายการสินค้าและสต็อกทั้งหมดในระบบ</p>
        </div>
        <a href="product_form.php" class="bg-theme-main hover:bg-purple-600 text-white px-6 py-3 rounded-xl shadow-lg shadow-purple-500/30 transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> เพิ่มสินค้าใหม่
        </a>
    </div>

    <div class="glass rounded-xl overflow-hidden border border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-800 text-gray-400 uppercase text-sm">
                    <tr>
                        <th class="p-4">รูปภาพ</th>
                        <th class="p-4">ชื่อสินค้า</th>
                        <th class="p-4">หมวดหมู่</th>
                        <th class="p-4 text-center">ราคา</th>
                        <th class="p-4 text-center">สต็อก (พร้อมขาย)</th>
                        <th class="p-4 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700 text-gray-300">
                    <?php foreach($products as $p): ?>
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="p-4">
                            <img src="../<?php echo $p->img; ?>" class="w-16 h-16 object-cover rounded-lg border border-slate-600 bg-slate-900">
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-white text-lg"><?php echo $p->name; ?></div>
                            <?php if($p->is_gacha): ?>
                                <span class="text-xs bg-yellow-500 text-black px-2 py-0.5 rounded font-bold">สุ่มรางวัล</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <span class="bg-slate-700 text-xs px-2 py-1 rounded-full"><?php echo $p->category_name ?? 'ไม่มีหมวดหมู่'; ?></span>
                        </td>
                        <td class="p-4 text-center font-bold text-theme-main">฿<?php echo number_format($p->price, 2); ?></td>
                        <td class="p-4 text-center">
                            <span class="px-3 py-1 rounded-full text-sm font-bold <?php echo $p->stock_count > 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                                <?php echo number_format($p->stock_count); ?> ชิ้น
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="product_form.php?id=<?php echo $p->id; ?>" class="bg-blue-600 hover:bg-blue-500 text-white p-2 rounded-lg transition" title="แก้ไข / เติมของ">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $p->id; ?>)" class="bg-red-600 hover:bg-red-500 text-white p-2 rounded-lg transition" title="ลบสินค้า">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(count($products) == 0): ?>
                        <tr><td colspan="6" class="p-10 text-center text-gray-500">ยังไม่มีสินค้า</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลสินค้าและสต็อกทั้งหมดจะหายไป!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            background: '#1e293b', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = `products.php?delete=${id}`;
            }
        })
    }
</script>

<?php 
// ปิด tag HTML (เพราะเรา include header มาแล้ว แต่ยังไม่ได้ปิดส่วนท้าย)
echo "</div></main></body></html>"; 
?>