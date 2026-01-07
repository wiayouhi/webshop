<?php include 'admin_auth.php'; ?>
<?php
// admin/manage_services.php
require_once '../db.php';
require_once 'header.php';

// --- ส่วนจัดการ Logic ---

// 1. เพิ่มเกมใหม่ (อัปเดตให้บันทึก description)
if (isset($_POST['add_game'])) {
    $name = $_POST['name'];
    $img = $_POST['image'];
    $type = $_POST['input_type'];
    $desc = $_POST['description']; // <--- รับค่ารายละเอียด
    
    $pdo->prepare("INSERT INTO services (name, image, input_type, description) VALUES (?, ?, ?, ?)")
        ->execute([$name, $img, $type, $desc]);
        
    echo "<script>window.location='manage_services.php';</script>";
}

// 2. เพิ่มแพ็กเกจราคา
if (isset($_POST['add_pkg'])) {
    $sid = $_POST['service_id'];
    $pname = $_POST['pkg_name'];
    $pprice = $_POST['pkg_price'];
    $pdo->prepare("INSERT INTO service_packages (service_id, name, price) VALUES (?, ?, ?)")
        ->execute([$sid, $pname, $pprice]);
    echo "<script>window.location='manage_services.php';</script>";
}

// 3. สวิตช์ เปิด/ปิด บริการ
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("SELECT is_active FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $status = $stmt->fetchColumn();
    
    $newStatus = ($status == 1) ? 0 : 1;
    $pdo->prepare("UPDATE services SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
    echo "<script>window.location='manage_services.php';</script>";
}

// 4. ลบเกม / ลบแพ็กเกจ
if (isset($_GET['del_game'])) {
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$_GET['del_game']]);
    echo "<script>window.location='manage_services.php';</script>";
}
if (isset($_GET['del_pkg'])) {
    $pdo->prepare("DELETE FROM service_packages WHERE id = ?")->execute([$_GET['del_pkg']]);
    echo "<script>window.location='manage_services.php';</script>";
}

// ดึงข้อมูลทั้งหมดมาแสดง
$services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
?>

<div class="container mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold mb-6 text-white"><i class="fa-solid fa-screwdriver-wrench"></i> จัดการระบบเติมเกม</h1>

    <div class="glass p-6 rounded-xl border border-slate-700 mb-8 bg-slate-800/80">
        <h3 class="text-xl font-bold mb-4 text-theme-main">เพิ่มเกม/บริการใหม่</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div class="space-y-1">
                <label class="text-xs text-gray-400">ชื่อเกม</label>
                <input type="text" name="name" placeholder="ชื่อเกม (เช่น Valorant)" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-400">รูปภาพ (URL)</label>
                <input type="text" name="image" placeholder="ลิงก์รูปภาพ..." class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-400">รูปแบบการกรอก</label>
                <select name="input_type" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                    <option value="uid">กรอกแค่ UID/ID</option>
                    <option value="id_pass">กรอก ID + Password</option>
                    <option value="uid_zone">กรอก UID + Zone</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-400">รายละเอียด/คำแนะนำ (แสดงหน้าเติมเงิน)</label>
                <textarea name="description" placeholder="เช่น: วิธีดู UID... / ปิด 2FA ก่อนเติม..." class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white h-[42px] resize-none"></textarea>
            </div>

            <div class="md:col-span-2 mt-2">
                <button type="submit" name="add_game" class="w-full bg-green-600 hover:bg-green-500 text-white rounded font-bold py-2 shadow-lg transition">
                    <i class="fa-solid fa-plus"></i> เพิ่มเข้าระบบ
                </button>
            </div>
        </form>
    </div>

    <div class="grid gap-6">
        <?php foreach($services as $s): ?>
        <div class="glass p-6 rounded-xl border transition-all <?php echo $s->is_active ? 'border-slate-600' : 'border-red-800 bg-red-900/10 grayscale'; ?>">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <div class="flex gap-4">
                    <img src="<?php echo htmlspecialchars($s->image); ?>" class="w-24 h-24 rounded-lg object-cover bg-slate-800">
                    <div>
                        <h3 class="text-2xl font-bold text-white">
                            <?php echo htmlspecialchars($s->name); ?>
                            <?php if(!$s->is_active): ?>
                                <span class="text-red-500 text-sm ml-2 font-bold">(ปิดรับเติมชั่วคราว)</span>
                            <?php endif; ?>
                        </h3>
                        <p class="text-gray-400 text-sm">รูปแบบ: <span class="text-theme-main"><?php echo $s->input_type; ?></span></p>
                        
                        <?php if($s->description): ?>
                            <p class="text-xs text-gray-500 mt-1 max-w-md truncate"><i class="fa-solid fa-info-circle"></i> <?php echo htmlspecialchars($s->description); ?></p>
                        <?php endif; ?>

                        <a href="?del_game=<?php echo $s->id; ?>" onclick="return confirm('ยืนยันลบเกมนี้? ข้อมูลแพ็กเกจจะหายหมด')" class="text-red-400 text-sm hover:underline mt-2 inline-block">
                            <i class="fa-solid fa-trash"></i> ลบเกมนี้
                        </a>
                    </div>
                </div>

                <a href="?toggle=<?php echo $s->id; ?>" class="px-6 py-2 rounded-full font-bold shadow-lg transition transform hover:scale-105 <?php echo $s->is_active ? 'bg-green-600 hover:bg-green-500 text-white' : 'bg-red-600 hover:bg-red-500 text-white'; ?>">
                    <i class="fa-solid <?php echo $s->is_active ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                    <?php echo $s->is_active ? 'เปิดรับอยู่' : 'ปิดปรับปรุง'; ?>
                </a>
            </div>

            <hr class="border-slate-700 my-4 opacity-30">

            <div class="pl-0 md:pl-28">
                <div class="flex flex-wrap gap-2 mb-3">
                    <?php 
                        $pkgs = $pdo->prepare("SELECT * FROM service_packages WHERE service_id = ? ORDER BY price ASC");
                        $pkgs->execute([$s->id]);
                        foreach($pkgs as $p):
                    ?>
                        <div class="bg-slate-800 border border-slate-600 px-3 py-1 rounded text-sm text-gray-300 flex items-center gap-2">
                            <span><?php echo $p->name; ?></span>
                            <span class="text-theme-main font-bold">฿<?php echo number_format($p->price); ?></span>
                            <a href="?del_pkg=<?php echo $p->id; ?>" onclick="return confirm('ลบแพ็กเกจนี้?')" class="text-gray-500 hover:text-red-400 px-1 border-l border-slate-600 ml-1">x</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" class="flex gap-2 items-center flex-wrap">
                    <span class="text-sm text-gray-500">เพิ่มแพ็กเกจ:</span>
                    <input type="hidden" name="service_id" value="<?php echo $s->id; ?>">
                    <input type="text" name="pkg_name" placeholder="ชื่อ (เช่น 100 เพชร)" class="bg-slate-900 border border-slate-600 rounded px-2 py-1 text-sm text-white" required>
                    <input type="number" name="pkg_price" placeholder="ราคา" class="bg-slate-900 border border-slate-600 rounded px-2 py-1 text-sm text-white w-24" required>
                    <button type="submit" name="add_pkg" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded text-sm">บันทึก</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>