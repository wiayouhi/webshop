<?php include 'admin_auth.php'; ?>
<?php
// admin/users.php
require_once 'header.php';

// Logic: เติมเงิน/หักเงิน/แก้ไข
if (isset($_POST['update_user'])) {
    $uid = $_POST['user_id'];
    $point = $_POST['point'];
    $role = $_POST['role'];
    
    // ถ้ามีการกรอกรหัสผ่านใหม่ ให้เปลี่ยนด้วย
    $pass_sql = "";
    $params = [$point, $role];
    
    if (!empty($_POST['new_password'])) {
        $pass_sql = ", password = ?";
        $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    }
    
    $params[] = $uid; // ปิดท้ายด้วย WHERE id

    $stmt = $pdo->prepare("UPDATE users SET point = ?, role = ? $pass_sql WHERE id = ?");
    
    if ($stmt->execute($params)) {
        echo "<script>Swal.fire('สำเร็จ', 'อัปเดตข้อมูลสมาชิกเรียบร้อย', 'success');</script>";
    }
}

// Logic: ลบสมาชิก
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    echo "<script>window.location='users.php';</script>";
}

// Search
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users WHERE username LIKE ? ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%"]);
$users = $stmt->fetchAll();
?>

<div class="container mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-bold">จัดการสมาชิก</h2>
            <p class="text-gray-400">รายชื่อสมาชิกทั้งหมด <?php echo count($users); ?> คน</p>
        </div>
        
        <form class="flex w-full md:w-auto relative">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาชื่อผู้ใช้..." class="bg-slate-800 border border-slate-600 rounded-l-lg px-4 py-2 text-white focus:outline-none w-full md:w-64">
            <button type="submit" class="bg-theme-main text-white px-4 py-2 rounded-r-lg hover:bg-purple-600 transition">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <div class="glass rounded-xl overflow-hidden border border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-800 text-gray-400 uppercase text-sm">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Username</th>
                        <th class="p-4">Point (เครดิต)</th>
                        <th class="p-4">สถานะ</th>
                        <th class="p-4 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700 text-gray-300">
                    <?php foreach($users as $u): ?>
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="p-4 text-gray-500">#<?php echo $u->id; ?></td>
                        <td class="p-4 flex items-center gap-3">
                            <img src="../<?php echo $u->profile_img; ?>" class="w-8 h-8 rounded-full bg-slate-600">
                            <span class="font-bold text-white"><?php echo $u->username; ?></span>
                        </td>
                        <td class="p-4 text-theme-main font-bold">฿ <?php echo number_format($u->point, 2); ?></td>
                        <td class="p-4">
                            <?php if($u->role == 'admin'): ?>
                                <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded text-xs border border-red-500/30">Admin</span>
                            <?php else: ?>
                                <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded text-xs border border-blue-500/30">Member</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center">
                            <button onclick="editUser(<?php echo htmlspecialchars(json_encode($u)); ?>)" class="bg-yellow-600 hover:bg-yellow-500 text-white px-3 py-1 rounded-lg text-sm mr-2 transition">
                                <i class="fa-solid fa-edit"></i> แก้ไข / เติมเงิน
                            </button>
                            <?php if($u->id != $_SESSION['user_id']): // ห้ามลบตัวเอง ?>
                            <a href="users.php?delete=<?php echo $u->id; ?>" onclick="return confirm('ยืนยันลบ User นี้?')" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded-lg text-sm transition">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="glass max-w-md w-full p-6 rounded-2xl border border-slate-600 relative animate-fade-in-up">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
        
        <h3 class="text-xl font-bold mb-4">จัดการสมาชิก: <span id="modal_username" class="text-theme-main"></span></h3>
        
        <form method="POST">
            <input type="hidden" name="user_id" id="modal_uid">
            
            <div class="mb-4">
                <label class="block text-gray-400 mb-1 text-sm">ยอดเงินคงเหลือ (บาท)</label>
                <input type="number" step="0.01" name="point" id="modal_point" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-lg font-bold text-green-400">
                <p class="text-xs text-gray-500 mt-1">* สามารถแก้ตัวเลขนี้เพื่อ เติมเงิน/หักเงิน ได้เลย</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-400 mb-1 text-sm">เปลี่ยนรหัสผ่าน (ถ้าไม่เปลี่ยนให้เว้นว่าง)</label>
                <input type="text" name="new_password" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" placeholder="ตั้งรหัสผ่านใหม่...">
            </div>

            <div class="mb-6">
                <label class="block text-gray-400 mb-1 text-sm">สิทธิ์การใช้งาน</label>
                <select name="role" id="modal_role" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                    <option value="member">Member (สมาชิกทั่วไป)</option>
                    <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                </select>
            </div>

            <button type="submit" name="update_user" class="w-full bg-theme-main hover:bg-purple-600 text-white py-2 rounded-lg font-bold">บันทึกข้อมูล</button>
        </form>
    </div>
</div>

<script>
    function editUser(user) {
        document.getElementById('modal_uid').value = user.id;
        document.getElementById('modal_username').innerText = user.username;
        document.getElementById('modal_point').value = user.point;
        document.getElementById('modal_role').value = user.role;
        
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>

<?php require_once 'footer.php'; // ปิด HTML ให้ครบ ?>