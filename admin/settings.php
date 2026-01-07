<?php include 'admin_auth.php'; ?>
<?php
// admin/settings.php

require_once '../db.php';

// 2. Logic การบันทึกข้อมูล
if (isset($_POST['save_settings'])) {
    
    // --- CSRF Protection ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("❌ Error: Invalid CSRF Token");
    }

    // รับค่าข้อมูลทั่วไป
    $site_name = $_POST['site_name'];
    $site_logo = $_POST['site_logo'];
    $wallet_phone = $_POST['truewallet_phone']; // ใช้สำหรับแสดงผล หรือรับเงิน (ตามการใช้งาน)
    $marquee = $_POST['marquee_text'];
    
    // รับค่าโซเชียล
    $facebook = $_POST['facebook_url'];
    $line = $_POST['line_url'];
    $discord = $_POST['discord_url'];
    $discord_widget_id = $_POST['discord_widget_id']; 
    $youtube = $_POST['youtube_url'];
    $tiktok = $_POST['tiktok_url'];
    $instagram = $_POST['instagram_url'];

    // จัดการแบนเนอร์
    $banner_urls = isset($_POST['banner_urls']) ? $_POST['banner_urls'] : [];
    $banners_clean = array_values(array_filter(array_map('trim', $banner_urls)));
    $banners_json = json_encode($banners_clean, JSON_UNESCAPED_UNICODE);

    // จัดการพื้นหลัง
    $bg_type = $_POST['background_type'];
    $bg_urls = isset($_POST['background_urls']) ? $_POST['background_urls'] : [];
    $bg_clean = array_values(array_filter(array_map('trim', $bg_urls)));
    $bg_list_json = json_encode($bg_clean, JSON_UNESCAPED_UNICODE);
    $emojis = $_POST['floating_emojis'];

    // ระบบการเงิน (เหลือแค่ TrueWallet)
    $pay_tm_phone = $_POST['payment_tm_phone'];
    $pay_tm_api = $_POST['payment_tm_api_url'];

    // SQL Update (ตัดส่วน Bank/Slip ออก)
    $sql = "UPDATE settings SET 
            site_name=?, 
            site_logo=?, 
            truewallet_phone=?, 
            marquee_text=?, 
            facebook_url=?, 
            line_url=?, 
            discord_url=?,
            discord_widget_id=?, 
            youtube_url=?, 
            tiktok_url=?, 
            instagram_url=?, 
            banner_img=?,
            background_type=?, 
            background_list=?, 
            floating_emojis=?,
            payment_tm_phone=?,     
            payment_tm_api_url=?   
            WHERE id=1";

    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([
        $site_name, $site_logo, $wallet_phone, $marquee, 
        $facebook, $line, $discord, $discord_widget_id, $youtube, $tiktok, $instagram, 
        $banners_json,
        $bg_type, $bg_list_json, $emojis,
        $pay_tm_phone, $pay_tm_api
    ])) {
        // Refresh Config
        $stmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
        $stmt->execute();
        $web_config = $stmt->fetch();

        echo "<script>
            setTimeout(function() {
                Swal.fire({
                    title: 'บันทึกสำเร็จ!',
                    text: 'อัปเดตข้อมูลเว็บไซต์เรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    background: '#1e293b',
                    color: '#fff'
                }).then(() => window.location='settings.php');
            }, 100);
        </script>";
    }
}

// 3. เรียก header.php
require_once 'header.php';

// Load Config
$config = $web_config; 
if (!isset($config)) {
    $stmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch();
}

$current_banners_json = $config->banner_img ?: '[]';
if (json_decode($current_banners_json) === null) $current_banners_json = json_encode([$config->banner_img]);

$current_bg_json = $config->background_list ?: '[]';
if (json_decode($current_bg_json) === null) $current_bg_json = json_encode([]); 
?>

<style>
    .glass-card {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        border-color: rgba(var(--theme-main), 0.5);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
    }
    .input-dark {
        background: #0f172a;
        border: 1px solid #334155;
        transition: border-color 0.2s;
    }
    .input-dark:focus {
        border-color: var(--theme-main);
        outline: none;
    }
    .animate-up { opacity: 0; transform: translateY(20px); animation: fadeUp 0.6s ease forwards; }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
</style>

<div class="max-w-7xl mx-auto py-8 px-4">
    
    <div class="flex items-center justify-between mb-8 animate-up">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">ตั้งค่าเว็บไซต์</h2>
            <p class="text-gray-400 text-sm mt-1">จัดการข้อมูลพื้นฐาน และระบบเติมเงิน</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-theme-main/20 flex items-center justify-center text-theme-main text-xl animate-pulse">
            <i class="fa-solid fa-gear"></i>
        </div>
    </div>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="glass-card p-6 rounded-2xl animate-up delay-100">
                    <h3 class="text-lg font-bold text-white mb-4 border-l-4 border-theme-main pl-3">ข้อมูลพื้นฐาน</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-400 text-xs mb-1">ชื่อเว็บไซต์</label>
                            <input type="text" name="site_name" value="<?php echo htmlspecialchars($config->site_name); ?>" class="input-dark w-full rounded-lg p-3 text-white">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs mb-1">ข้อความวิ่ง (Marquee)</label>
                            <input type="text" name="marquee_text" value="<?php echo htmlspecialchars($config->marquee_text); ?>" class="input-dark w-full rounded-lg p-3 text-white">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="block text-gray-400 text-xs mb-1">ลิงก์โลโก้เว็บ (URL)</label>
                        <div class="flex gap-4 items-start">
                            <div class="flex-1">
                                <input type="text" id="logo-input" name="site_logo" value="<?php echo htmlspecialchars($config->site_logo ?? ''); ?>" placeholder="https://..." class="input-dark w-full rounded-lg p-3 text-white" oninput="updateLogoPreview()">
                            </div>
                            <div class="w-12 h-12 bg-slate-800 rounded-full border border-slate-600 overflow-hidden flex-shrink-0 relative">
                                <img id="logo-preview" src="<?php echo !empty($config->site_logo) ? $config->site_logo : 'https://placehold.co/100x100?text=No+Logo'; ?>" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-6 rounded-2xl animate-up delay-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5"><i class="fa-solid fa-coins text-8xl"></i></div>
                    <h3 class="text-lg font-bold text-orange-400 mb-4 border-l-4 border-orange-500 pl-3">ตั้งค่ารับเงิน (TrueWallet ซอง)</h3>
                    
                    <div class="grid grid-cols-1 gap-6 relative z-10">
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-dashed border-slate-700">
                            <h4 class="text-orange-400 font-semibold mb-3 text-sm"><i class="fa-solid fa-gift"></i> TrueWallet Angpao API</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-400 block mb-1">เบอร์ Wallet ที่จะรับเงิน</label>
                                    <input type="text" name="payment_tm_phone" value="<?php echo htmlspecialchars($config->payment_tm_phone ?? ''); ?>" class="input-dark w-full rounded p-2 text-white text-sm" placeholder="0xxxxxxxxx">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-400 block mb-1">API URL (สำหรับเช็คซอง)</label>
                                    <input type="text" name="payment_tm_api_url" value="<?php echo htmlspecialchars($config->payment_tm_api_url ?? ''); ?>" class="input-dark w-full rounded p-2 text-white text-sm" placeholder="https://api.example.com/check_angpao">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-6 rounded-2xl animate-up delay-300">
                     <h3 class="text-lg font-bold text-white mb-4 border-l-4 border-pink-500 pl-3">ช่องทางติดต่อ</h3>
                     <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <?php 
                        $socials = [
                            ['facebook', 'Facebook', $config->facebook_url, 'text-blue-500'],
                            ['line', 'Line', $config->line_url, 'text-green-500'],
                            ['discord', 'Discord (Link)', $config->discord_url, 'text-indigo-400'],
                            ['youtube', 'YouTube', $config->youtube_url, 'text-red-500'],
                            ['tiktok', 'TikTok', $config->tiktok_url, 'text-pink-500'],
                            ['instagram', 'Instagram', $config->instagram_url, 'text-purple-500'],
                        ];
                        foreach($socials as $s): ?>
                        <div class="relative">
                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-brands fa-<?php echo $s[0]; ?> <?php echo $s[3]; ?>"></i>
                             </div>
                             <input type="text" name="<?php echo $s[0]; ?>_url" value="<?php echo htmlspecialchars($s[2] ?? ''); ?>" placeholder="<?php echo $s[1]; ?>" class="input-dark w-full rounded pl-9 p-2 text-sm text-white">
                        </div>
                        <?php endforeach; ?>
                     </div>

                     <div class="mt-4 pt-4 border-t border-white/5">
                        <label class="block text-gray-400 text-xs mb-2">
                            <i class="fa-brands fa-discord text-indigo-400 mr-1"></i> Discord Widget (Server ID)
                        </label>
                        <div class="flex gap-2">
                             <input type="text" name="discord_widget_id" value="<?php echo htmlspecialchars($config->discord_widget_id ?? ''); ?>" placeholder="เช่น 123456789012345678" class="input-dark w-full rounded-lg p-2.5 text-white font-mono text-sm">
                        </div>
                     </div>
                </div>

            </div>
            
            <div class="space-y-6">
                
                <button type="submit" name="save_settings" class="w-full bg-gradient-to-r from-theme-main to-purple-600 hover:from-purple-500 hover:to-theme-main text-white font-bold py-4 rounded-xl shadow-lg shadow-theme-main/30 transition-all duration-300 transform hover:-translate-y-1 active:scale-95 animate-up">
                    <i class="fa-solid fa-save mr-2"></i> บันทึกการเปลี่ยนแปลง
                </button>

                <div class="glass-card p-6 rounded-2xl animate-up delay-200">
                    <h3 class="text-lg font-bold text-white mb-4"><i class="fa-solid fa-palette text-gray-400 mr-2"></i> ธีมพื้นหลัง</h3>
                    
                    <div class="mb-4">
                        <select name="background_type" class="input-dark w-full rounded-lg p-2.5 text-white mb-2">
                            <option value="image" <?php echo ($config->background_type == 'image') ? 'selected' : ''; ?>>📷 รูปภาพสไลด์โชว์</option>
                            <option value="video" <?php echo ($config->background_type == 'video') ? 'selected' : ''; ?>>🎥 วิดีโอ (YouTube/MP4)</option>
                        </select>
                        
                        <div id="bg-list" class="space-y-2 mt-2 max-h-[200px] overflow-y-auto pr-1 custom-scrollbar"></div>
                        
                        <button type="button" onclick="addBgField()" class="mt-2 w-full py-2 bg-slate-800 hover:bg-slate-700 text-xs text-gray-300 rounded border border-dashed border-slate-600 transition">
                            <i class="fa-solid fa-plus"></i> เพิ่มลิงก์พื้นหลัง
                        </button>
                    </div>

                    <div class="pt-4 border-t border-white/5">
                        <label class="block text-gray-400 text-xs mb-2">Floating Emojis</label>
                        <input type="text" name="floating_emojis" value="<?php echo htmlspecialchars($config->floating_emojis ?? ''); ?>" class="input-dark w-full rounded p-2 text-white text-center" placeholder="❤️,🔥,✨">
                    </div>
                </div>

                <div class="glass-card p-6 rounded-2xl animate-up delay-300">
                    <h3 class="text-lg font-bold text-white mb-4"><i class="fa-solid fa-images text-gray-400 mr-2"></i> สไลด์แบนเนอร์</h3>
                    <div id="banner-list" class="space-y-2 max-h-[250px] overflow-y-auto pr-1 custom-scrollbar"></div>
                    <button type="button" onclick="addBannerField()" class="mt-3 w-full py-2 bg-slate-800 hover:bg-slate-700 text-xs text-gray-300 rounded border border-dashed border-slate-600 transition">
                        <i class="fa-solid fa-plus"></i> เพิ่มรูปร้านค้า
                    </button>
                </div>

            </div>
        </div>
        
        <input type="hidden" name="truewallet_phone" value="<?php echo htmlspecialchars($config->truewallet_phone); ?>">
    </form>
</div>

<script>
    // 1. Logo Preview Logic
    function updateLogoPreview() {
        const url = document.getElementById('logo-input').value;
        const preview = document.getElementById('logo-preview');
        if(url) {
            preview.src = url;
            preview.onerror = function() { this.src = 'https://placehold.co/100x100?text=Error'; };
        } else {
            preview.src = 'https://placehold.co/100x100?text=No+Logo';
        }
    }

    // 2. Banner Logic
    const bannerList = document.getElementById('banner-list');
    const oldBanners = <?php echo $current_banners_json; ?>;
    
    function addBannerField(value = '') {
        const div = document.createElement('div');
        div.className = 'flex gap-2 animate-up';
        div.style.animationDuration = '0.3s';
        div.innerHTML = `
            <div class="relative flex-1">
                <input type="text" name="banner_urls[]" value="${value}" placeholder="URL รูปภาพ..." class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-xs focus:border-theme-main focus:outline-none pl-2">
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-2.5 rounded transition border border-red-500/20"><i class="fa-solid fa-trash text-xs"></i></button>
        `;
        bannerList.appendChild(div);
    }
    if (oldBanners && oldBanners.length > 0) oldBanners.forEach(url => addBannerField(url));
    else addBannerField();

    // 3. Background Logic
    const bgList = document.getElementById('bg-list');
    const oldBgs = <?php echo $current_bg_json; ?>;
    
    function addBgField(value = '') {
        const div = document.createElement('div');
        div.className = 'flex gap-2 animate-up';
        div.style.animationDuration = '0.3s';
        div.innerHTML = `
            <input type="text" name="background_urls[]" value="${value}" placeholder="URL พื้นหลัง..." class="flex-1 bg-slate-900 border border-slate-600 rounded p-2 text-white text-xs focus:border-theme-main focus:outline-none">
            <button type="button" onclick="this.parentElement.remove()" class="bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-2.5 rounded transition border border-red-500/20"><i class="fa-solid fa-trash text-xs"></i></button>
        `;
        bgList.appendChild(div);
    }
    if (oldBgs && oldBgs.length > 0) oldBgs.forEach(url => addBgField(url));
    else addBgField();
</script>

<?php echo "</div></main></body></html>"; ?>