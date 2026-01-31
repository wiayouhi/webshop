<?php include 'db.php'; ?>
<?php
require_once 'header.php';

if (isset($_SESSION['user_id'])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

// ส่วน Logic การสมัครสมาชิก
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_msg = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif ($password !== $confirm_password) {
        $error_msg = "รหัสผ่านยืนยันไม่ตรงกัน";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error_msg = "ชื่อผู้ใช้นี้มีคนใช้แล้ว";
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, point) VALUES (?, ?, 'member', 0)");
            if ($stmt->execute([$username, $hash_password])) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'สมัครสมาชิกสำเร็จ',
                        text: 'กรุณาเข้าสู่ระบบเพื่อใช้งาน',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location='login.php';
                    });
                </script>";
            } else {
                $error_msg = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
            }
        }
    }
}
?>

<div class="flex items-center justify-center min-h-[60vh]">
    <div class="glass w-full max-w-md p-8 rounded-2xl shadow-2xl relative overflow-hidden border border-slate-700">
        
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-theme-main rounded-full blur-[80px] opacity-20"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500 rounded-full blur-[80px] opacity-20"></div>

        <h2 class="text-3xl font-bold text-center mb-6 text-glow">สมัครสมาชิก</h2>

        <?php if(isset($error_msg)): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded-lg mb-4 text-center text-sm">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-400 mb-2 text-sm">ชื่อผู้ใช้งาน (Username)</label>
                <div class="relative">
                    <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-500"></i>
                    <input type="text" name="username" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main transition text-white" placeholder="กรอกชื่อผู้ใช้งาน..." required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-400 mb-2 text-sm">รหัสผ่าน (Password)</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-500"></i>
                    <input type="password" name="password" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main transition text-white" placeholder="ตั้งรหัสผ่าน..." required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-400 mb-2 text-sm">ยืนยันรหัสผ่าน (Confirm Password)</label>
                <div class="relative">
                    <i class="fa-solid fa-check-circle absolute left-4 top-3.5 text-gray-500"></i>
                    <input type="password" name="confirm_password" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main transition text-white" placeholder="กรอกรหัสผ่านอีกครั้ง..." required>
                </div>
            </div>

            <button type="submit" name="register" class="w-full bg-theme-main hover:bg-purple-600 text-white font-bold py-3 rounded-lg shadow-lg shadow-purple-500/30 transition transform hover:-translate-y-1">
                สมัครสมาชิก
            </button>
        </form>

        <div class="flex items-center justify-between my-5">
            <hr class="w-full border-slate-600">
            <span class="px-3 text-gray-400 text-sm">หรือ</span>
            <hr class="w-full border-slate-600">
        </div>

        <a href="api/discord_login.php" class="block w-full bg-[#5865F2] hover:bg-[#4752C4] text-white font-bold py-3 rounded-lg shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1 text-center">
            <i class="fa-brands fa-discord mr-2"></i> สมัครสมาชิกด้วย Discord
        </a>

        <div class="mt-6 text-center text-sm text-gray-400">
            มีบัญชีอยู่แล้ว? <a href="/login" class="text-theme-main hover:text-white transition underline">เข้าสู่ระบบที่นี่</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>