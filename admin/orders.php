<?php include 'admin_auth.php'; ?>
<?php
// admin/orders.php
require_once 'header.php';

// Logic: เปลี่ยนสถานะ
if(isset($_GET['complete'])) {
    $oid = $_GET['complete'];
    $pdo->prepare("UPDATE orders SET status = 'success' WHERE id = ?")->execute([$oid]);
    echo "<script>Swal.fire('เรียบร้อย', 'ปิดงานสำเร็จ', 'success').then(() => window.location='orders.php');</script>";
}
if(isset($_GET['cancel'])) {
    // *ควรเพิ่มระบบคืนเงินตรงนี้ถ้าจะทำจริงจัง*
    $oid = $_GET['cancel'];
    $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$oid]);
    echo "<script>window.location='orders.php';</script>";
}

// ดึงรายการที่รอเติม (Pending)
$orders = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.status = 'pending' ORDER BY o.id ASC")->fetchAll();
?>

<div class="container mx-auto">
    <h2 class="text-3xl font-bold mb-6">รายการรอเติมเกม (Pending Orders)</h2>

    <div class="glass rounded-xl overflow-hidden border border-slate-700">
        <table class="w-full text-left">
            <thead class="bg-slate-800 text-gray-400">
                <tr>
                    <th class="p-4">Order ID</th>
                    <th class="p-4">User</th>
                    <th class="p-4">รายการ</th>
                    <th class="p-4">ข้อมูล (UID/Pass)</th>
                    <th class="p-4 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="text-gray-300 divide-y divide-slate-700">
                <?php if(count($orders) == 0): ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">ไม่มีรายการค้าง</td></tr>
                <?php endif; ?>

                <?php foreach($orders as $o): ?>
                <tr class="hover:bg-slate-800/50">
                    <td class="p-4">#<?php echo $o->id; ?></td>
                    <td class="p-4 text-theme-main font-bold"><?php echo $o->username; ?></td>
                    <td class="p-4"><?php echo $o->product_name; ?></td>
                    <td class="p-4 font-mono text-yellow-400 select-all bg-slate-900 rounded p-1">
                        <?php echo $o->note; ?>
                    </td>
                    <td class="p-4 text-center flex justify-center gap-2">
                        <a href="orders.php?complete=<?php echo $o->id; ?>" onclick="return confirm('เติมให้ลูกค้าเสร็จแล้วใช่ไหม?')" class="bg-green-600 hover:bg-green-500 text-white px-3 py-1 rounded text-sm">
                            <i class="fa-solid fa-check"></i> เสร็จสิ้น
                        </a>
                        <a href="orders.php?cancel=<?php echo $o->id; ?>" onclick="return confirm('ยกเลิกรายการ?')" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-sm">
                            <i class="fa-solid fa-xmark"></i> ยกเลิก
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>