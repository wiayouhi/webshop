<?php include 'db.php'; ?>
<?php
require_once 'header.php';

if (isset($_SESSION['user_id'])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

// ส่วน Logic การล็อกอิน
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // เช็คว่ามี user และ รหัสผ่านถูกต้อง (verify hash)
    if ($user && password_verify($password, $user->password)) {
        // สร้าง Session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        $_SESSION['point'] = $user->point;
        $_SESSION['profile_img'] = $user->profile_img;

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'ยินดีต้อนรับ',
                text: 'เข้าสู่ระบบสำเร็จ!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location='index.php';
            });
        </script>";
    } else {
        $error_msg = "ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<div class="flex items-center justify-center min-h-[60vh]">
    <div class="glass w-full max-w-md p-8 rounded-2xl shadow-2xl relative overflow-hidden border border-slate-700">
        
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-theme-main rounded-full blur-[80px] opacity-20"></div>

        <h2 class="text-3xl font-bold text-center mb-6 text-glow">เข้าสู่ระบบ</h2>

        <?php if(isset($error_msg)): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded-lg mb-4 text-center text-sm animate-pulse">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-400 mb-2 text-sm">ชื่อผู้ใช้งาน</label>
                <div class="relative">
                    <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-500"></i>
                    <input type="text" name="username" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main transition text-white" placeholder="Username" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-400 mb-2 text-sm">รหัสผ่าน</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-500"></i>
                    <input type="password" name="password" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main transition text-white" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" name="login" class="w-full bg-theme-main hover:bg-purple-600 text-white font-bold py-3 rounded-lg shadow-lg shadow-purple-500/30 transition transform hover:-translate-y-1">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-400">
            ยังไม่มีบัญชี? <a href="/register" class="text-theme-main hover:text-white transition underline">สมัครสมาชิกใหม่</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>