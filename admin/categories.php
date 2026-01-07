<?php include 'admin_auth.php'; ?>
<?php
// admin/categories.php
require_once 'header.php';

// Add/Edit Logic
if (isset($_POST['save_cat'])) {
    $name = $_POST['name'];
    $img = $_POST['img_url']; // ใส่เป็น URL ไปก่อนง่ายๆ
    
    if(!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, img=? WHERE id=?");
        $stmt->execute([$name, $img, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, img) VALUES (?, ?)");
        $stmt->execute([$name, $img]);
    }
    echo "<script>window.location='categories.php';</script>";
}

// Delete Logic
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    echo "<script>window.location='categories.php';</script>";
}

$cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$edit_cat = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_cat = $stmt->fetch();
}
?>

<div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="glass p-6 rounded-2xl border border-slate-700 h-fit">
        <h3 class="text-xl font-bold mb-4"><?php echo $edit_cat ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่'; ?></h3>
        <form method="POST">
            <?php if($edit_cat): ?><input type="hidden" name="id" value="<?php echo $edit_cat->id; ?>"><?php endif; ?>
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">ชื่อหมวดหมู่</label>
                <input type="text" name="name" value="<?php echo $edit_cat->name ?? ''; ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-2 text-white" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">ลิงก์รูปภาพ (URL)</label>
                <input type="text" name="img_url" value="<?php echo $edit_cat->img ?? ''; ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-2 text-white" placeholder="https://...">
            </div>
            <button type="submit" name="save_cat" class="w-full bg-theme-main hover:bg-purple-600 text-white py-2 rounded-lg">บันทึก</button>
            <?php if($edit_cat): ?>
                <a href="categories.php" class="block text-center mt-2 text-gray-400 text-sm">ยกเลิก</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="md:col-span-2 glass rounded-2xl overflow-hidden border border-slate-700">
        <table class="w-full text-left">
            <thead class="bg-slate-800 text-gray-400">
                <tr>
                    <th class="p-4">รูป</th>
                    <th class="p-4">ชื่อ</th>
                    <th class="p-4 text-right">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700 text-gray-300">
                <?php foreach($cats as $c): ?>
                <tr>
                    <td class="p-4"><img src="<?php echo $c->img; ?>" class="w-10 h-10 rounded-full bg-slate-600"></td>
                    <td class="p-4"><?php echo $c->name; ?></td>
                    <td class="p-4 text-right">
                        <a href="categories.php?edit=<?php echo $c->id; ?>" class="text-blue-400 mr-2 hover:underline">แก้ไข</a>
                        <a href="categories.php?delete=<?php echo $c->id; ?>" onclick="return confirm('ยืนยันลบ?')" class="text-red-400 hover:underline">ลบ</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo "</div></main></body></html>"; ?>