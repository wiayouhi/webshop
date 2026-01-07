<?php include 'db.php'; ?>
<?php
require_once 'header.php';
checkLogin(); // บังคับล็อกอิน

$user_id = $_SESSION['user_id'];
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$user_id]);
$current_user = $user->fetch();

// Logic: อัปเดตข้อมูล
if (isset($_POST['update_profile'])) {
    
    // 1. จัดการรูปภาพ (ถ้ามีการอัปโหลด)
    $img_path = $current_user->profile_img;
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_name = "user_" . $user_id . "_" . uniqid() . "." . $file_ext;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_dir . $new_name)) {
            $img_path = "uploads/" . $new_name;
        }
    }

    // 2. จัดการรหัสผ่าน
    $pass_sql = "";
    $params = [$img_path];

    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $pass_sql = ", password = ?";
            $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        } else {
            echo "<script>Swal.fire('Error', 'รหัสผ่านยืนยันไม่ตรงกัน', 'error');</script>";
        }
    }

    // 3. อัปเดตลง Database
    $params[] = $user_id; // สำหรับ WHERE
    $sql = "UPDATE users SET profile_img = ? $pass_sql WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        // อัปเดต Session รูปภาพด้วย
        $_SESSION['profile_img'] = $img_path;
        
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: 'อัปเดตข้อมูลโปรไฟล์เรียบร้อย',
                timer: 1500,
                showConfirmButton: false
            }).then(() => window.location='profile.php');
        </script>";
    }
}
?>

<div class="container mx-auto py-10 px-4">
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="md:col-span-1">
            <div class="glass p-8 rounded-2xl border border-slate-700 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-purple-600 to-blue-600 opacity-50"></div>
                
                <div class="relative z-10">
                    <img src="<?php echo $current_user->profile_img; ?>" class="w-32 h-32 rounded-full border-4 border-slate-800 mx-auto mb-4 object-cover shadow-xl bg-slate-700">
                    <h2 class="text-2xl font-bold text-white"><?php echo $current_user->username; ?></h2>
                    
                    <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-600">
                        <p class="text-xs text-gray-400 uppercase">ยอดเงินคงเหลือ</p>
                        <div class="text-2xl font-bold text-theme-main">
                            ฿ <?php echo number_format($current_user->point, 2); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <a href="history.php" class="block w-full bg-slate-800 hover:bg-slate-700 text-gray-300 py-3 rounded-xl transition border border-slate-700">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i> ดูประวัติการสั่งซื้อ
                </a>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="glass p-8 rounded-2xl border border-slate-700">
                <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-theme-main"></i> แก้ไขข้อมูลส่วนตัว
                </h3>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-6">
                        <label class="block text-gray-400 mb-2">เปลี่ยนรูปโปรไฟล์</label>
                        <div class="flex items-center gap-4">
                            <div class="relative w-full">
                                <input type="file" name="avatar" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-theme-main file:text-white hover:file:bg-purple-600 cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-700 my-6">

                    <h4 class="text-lg font-bold mb-4 text-gray-300">เปลี่ยนรหัสผ่าน <span class="text-xs font-normal text-gray-500">(ไม่ต้องกรอกหากไม่ต้องการเปลี่ยน)</span></h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-400 mb-2 text-sm">รหัสผ่านใหม่</label>
                            <input type="password" name="new_password" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2 text-sm">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="confirm_password" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="w-full bg-theme-main hover:bg-purple-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-purple-500/30 transition transform hover:-translate-y-1 mt-4">
                        <i class="fa-solid fa-save"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once 'footer.php'; ?>