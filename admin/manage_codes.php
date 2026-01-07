<?php include 'admin_auth.php'; ?>
<?php
// admin/manage_codes.php
require_once 'header.php'; // เรียก header ของ admin (ถ้ามี) หรือใช้ header หลักแล้วเช็คสิทธิ์เอา

// ลบโค้ด
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM redeem_codes WHERE id = ?")->execute([$_GET['delete']]);
    echo "<script>window.location='manage_codes.php';</script>";
}

// เพิ่มโค้ด
if (isset($_POST['add_code'])) {
    $code = strtoupper(trim($_POST['code']));
    $reward = $_POST['reward'];
    $max_uses = $_POST['max_uses'];

    try {
        $stmt = $pdo->prepare("INSERT INTO redeem_codes (code, reward, max_uses) VALUES (?, ?, ?)");
        $stmt->execute([$code, $reward, $max_uses]);
        echo "<script>Swal.fire('สำเร็จ', 'สร้างโค้ดเรียบร้อย', 'success');</script>";
    } catch (PDOException $e) {
        echo "<script>Swal.fire('Error', 'โค้ดซ้ำหรือเกิดข้อผิดพลาด', 'error');</script>";
    }
}

$codes = $pdo->query("SELECT * FROM redeem_codes ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-6xl mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-white"><i class="fa-solid fa-ticket text-theme-main"></i> จัดการโค้ดรางวัล</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="glass p-6 rounded-2xl border border-slate-700 sticky top-6">
                <h3 class="text-xl font-bold mb-4 border-b border-slate-700 pb-2 text-white">สร้างโค้ดใหม่</h3>
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-400 mb-1">รหัสโค้ด (ภาษาอังกฤษ/ตัวเลข)</label>
                        <input type="text" name="code" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white uppercase focus:border-theme-main focus:outline-none" placeholder="เช่น FREE50" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 mb-1">จำนวนเงินรางวัล (บาท)</label>
                        <input type="number" step="0.01" name="reward" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none" placeholder="เช่น 50.00" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 mb-1">จำนวนสิทธิ์ (คน)</label>
                        <input type="number" name="max_uses" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none" value="100" required>
                    </div>
                    <button type="submit" name="add_code" class="w-full bg-theme-main hover:bg-purple-600 text-white font-bold py-3 rounded-xl shadow-lg transition">
                        <i class="fa-solid fa-plus-circle"></i> สร้างโค้ด
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="glass rounded-2xl overflow-hidden border border-slate-700">
                <table class="w-full text-left text-gray-300">
                    <thead class="bg-slate-800 text-gray-400 uppercase text-sm">
                        <tr>
                            <th class="p-4">โค้ด</th>
                            <th class="p-4">รางวัล</th>
                            <th class="p-4 text-center">ใช้งานแล้ว</th>
                            <th class="p-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php foreach($codes as $c): ?>
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-white tracking-wider">
                                <?php echo $c->code; ?>
                            </td>
                            <td class="p-4 text-theme-main font-bold">
                                +<?php echo number_format($c->reward, 2); ?>
                            </td>
                            <td class="p-4 text-center">
                                <span class="bg-slate-900 px-3 py-1 rounded-full text-xs border border-slate-600">
                                    <?php echo $c->used_count; ?> / <?php echo $c->max_uses; ?>
                                </span>
                                <?php if($c->used_count >= $c->max_uses): ?>
                                    <span class="text-xs text-red-400 ml-2">(เต็ม)</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <a href="manage_codes.php?delete=<?php echo $c->id; ?>" onclick="return confirm('ยืนยันการลบโค้ดนี้?')" class="text-red-400 hover:text-red-500 transition p-2">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($codes)): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">
                                ยังไม่มีโค้ดกิจกรรม
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>