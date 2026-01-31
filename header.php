<?php
// header.php
if(!isset($pdo)) { require_once 'db.php'; }

// --- 1. จัดการ Data & Config พื้นฐาน ---
$bg_list = json_decode($web_config->background_list ?? '[]', true);
if (!$bg_list && !empty($web_config->background_img)) {
    $bg_list = [$web_config->background_img];
}
$emojis = isset($web_config->floating_emojis) ? array_filter(explode(',', $web_config->floating_emojis)) : [];

// สีหลักเดิมของเว็บ
$original_site_color = ($web_config->site_color == "red") ? "#ef4444" : (($web_config->site_color == "blue") ? "#3b82f6" : "#8b5cf6");
$site_main_color = $original_site_color;

// --- 2. ระบบตรวจสอบเทศกาล ---
$d = date('d');
$m = date('m');

$season = [
    'type' => 'normal',
    'color' => $original_site_color,
    'icon' => 'fa-gamepad',
    'title' => 'WELCOME',
    'sub' => 'Welcome to ' . $web_config->site_name,
    'effect' => 'normal' 
];

// Logic ตรวจสอบเทศกาล
if ($m == 12 && $d >= 20 || $m == 1 && $d <= 5) { // 🎄 ปีใหม่
    $season = [
        'type' => 'newyear',
        'color' => '#fbbf24',
        'icon' => 'fa-champagne-glasses',
        'title' => 'HAPPY NEW YEAR',
        'sub' => 'Wishing you happiness!',
        'effect' => ($d == 31 || $d == 1) ? 'fireworks' : 'snow'
    ];
} elseif ($m == 2 && $d >= 10 && $d <= 14) { // 🌹 วาเลนไทน์
    $season = [
        'type' => 'valentine',
        'color' => '#f43f5e',
        'icon' => 'fa-heart',
        'title' => 'HAPPY VALENTINE',
        'sub' => 'Love is in the air...',
        'effect' => 'hearts'
    ];
} elseif ($m == 4 && $d >= 10 && $d <= 16) { // 💦 สงกรานต์
    $season = [
        'type' => 'songkran',
        'color' => '#0ea5e9',
        'icon' => 'fa-water',
        'title' => 'HAPPY SONGKRAN',
        'sub' => 'Splash functionality loading...',
        'effect' => 'normal'
    ];
} elseif ($m == 10 && $d >= 25) { // 👻 ฮาโลวีน
    $season = [
        'type' => 'halloween',
        'color' => '#f97316',
        'icon' => 'fa-ghost',
        'title' => 'TRICK OR TREAT',
        'sub' => 'Spooky loading...',
        'effect' => 'spooky'
    ];
} elseif ($m == 11) { // 🌕 ลอยกระทง
    $season = [
        'type' => 'loykrathong',
        'color' => '#facc15',
        'icon' => 'fa-dharmachakra', 
        'title' => 'LOY KRATHONG',
        'sub' => 'Full moon loading...',
        'effect' => 'normal'
    ];
}

if ($season['type'] !== 'normal') {
    $site_main_color = $season['color'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $web_config->site_name; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Kanit', 'sans-serif'] },
                    colors: {
                        theme: {
                            main: '<?php echo $site_main_color; ?>',
                            hover: '<?php echo $site_main_color; ?>CC',
                            dark: '#0f172a',
                            glass: 'rgba(30, 41, 59, 0.7)',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'fade-in-up': 'fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'spin-fast': 'spin 0.8s linear infinite',
                        'entrance-left': 'slideInLeft 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards',
                        'pop-in': 'popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideInLeft: {
                            '0%': { opacity: '0', transform: 'translateX(-50px)' },
                            '100%': { opacity: '1', transform: 'translateX(0)' },
                        },
                        popIn: {
                            '0%': { opacity: '0', transform: 'scale(0.5)' },
                            '100%': { opacity: '1', transform: 'scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <script>
        // Check Landing Overlay Session
        if (sessionStorage.getItem('enteredSite')) {
            document.write('<style>#landing-overlay { display: none !important; }</style>');
        }
    </script>

    <style>
        /* Base Setup */
        body { color: #f8fafc; font-family: 'Kanit', sans-serif; overflow-x: hidden; background-color: #0f172a; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--theme-main); }

        /* Dynamic Background */
        #dynamic-bg { position: fixed; inset: 0; z-index: -20; overflow: hidden; background: #0f172a; }
        .bg-slide { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transition: opacity 2s ease-in-out, transform 10s ease; transform: scale(1.1); }
        .bg-slide.active { opacity: 1; transform: scale(1); }
        #video-bg { position: fixed; top: 50%; left: 50%; min-width: 100%; min-height: 100%; transform: translate(-50%, -50%); z-index: -20; object-fit: cover; opacity: 0.8; }
        .bg-overlay { position: fixed; inset: 0; background: linear-gradient(to bottom, rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.95), #0f172a); z-index: -10; pointer-events: none; }

        /* Canvas Effect (Layer หลังสุด) */
        #season-canvas { position: fixed; inset: 0; z-index: -5; pointer-events: none; }

        /* --- New Landing Overlay Styles (Modified) --- */
        #landing-overlay {
            position: fixed; inset: 0; z-index: 9998;
            /* ปรับพื้นหลังเป็น Glassmorphism (ทึบแสงแต่โปร่งใสเล็กน้อย + เบลอ) */
            background: rgba(15, 23, 42, 0.75); /* ปรับความโปร่งใสที่นี่ (0.75) */
            backdrop-filter: blur(20px); /* ความเบลอของฉากหลัง */
            -webkit-backdrop-filter: blur(20px);
            display: flex; align-items: center; justify-content: center;
            transition: opacity 0.6s ease-in-out, visibility 0.6s;
        }
        #landing-overlay.hidden-overlay { opacity: 0; visibility: hidden; pointer-events: none; }
        
        /* Grid Background Pattern (Modified: เอา Mask วงกลมออก) */
        .bg-grid-animated {
            background-size: 50px 50px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            /* ลบ mask-image ออกเพื่อให้ Grid เต็มหน้าจอ ไม่ใช่แค่วงกลม */
        }

        /* Card Menu Hover Effect */
        .menu-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }
        .menu-card:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(var(--theme-main), 0.5);
            transform: translateY(-5px);
        }

        /* Navigation Styles */
        .glass-nav { background: rgba(15, 23, 42, 0.05); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.05); transition: all 0.4s; }
        .glass-nav.scrolled { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 40px -10px rgba(0,0,0,0.5); }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); }

        .nav-link { position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 50%; width: 0; height: 2px; background: <?php echo $site_main_color; ?>; transition: all 0.3s ease; transform: translateX(-50%); box-shadow: 0 0 10px <?php echo $site_main_color; ?>; }
        .nav-link:hover::after, .nav-link.active::after { width: 80%; }
        
        #mobile-menu-drawer { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); transform: translateX(100%); }
        #mobile-menu-drawer.open { transform: translateX(0); }

        /* Emoji Floating */
        .floating-emoji { position: fixed; z-index: -5; animation: floatUp linear forwards; pointer-events: none; opacity: 0; filter: blur(1px); }
        @keyframes floatUp { 0% { transform: translateY(110vh) rotate(0deg) scale(0.8); opacity: 0; } 10% { opacity: 0.6; } 100% { transform: translateY(-10vh) rotate(360deg) scale(1.2); opacity: 0; } }
        
        /* Effect for Refresh Text */
        .text-flash { animation: flashText 0.5s ease; color: #4ade80 !important; }
        @keyframes flashText { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <div id="landing-overlay" class="bg-grid-animated">
        <div class="container mx-auto px-6 max-w-6xl relative z-10">
            <div class="flex flex-col lg:flex-row items-center justify-center gap-12 lg:gap-24">
                
                <div class="text-center lg:text-right opacity-0 animate-entrance-left" style="animation-delay: 0.1s;">
                    <div class="relative inline-block">
                        <h1 class="text-6xl lg:text-8xl font-black uppercase tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-white via-indigo-200 to-indigo-400 drop-shadow-[0_0_25px_rgba(99,102,241,0.5)]">
                            <?php echo $web_config->site_name; ?>
                        </h1>
                        <h1 class="text-6xl lg:text-8xl font-black uppercase tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-white/10 to-transparent absolute top-full left-0 transform scale-y-[-0.3] origin-top blur-sm select-none">
                            <?php echo $web_config->site_name; ?>
                        </h1>
                    </div>
                    <p class="mt-4 text-indigo-300 text-lg lg:text-xl font-light tracking-widest uppercase">
                        Welcome to our official store
                    </p>
                </div>

                <div class="hidden lg:block w-px h-64 bg-gradient-to-b from-transparent via-indigo-500/50 to-transparent"></div>

                <div class="flex-1 w-full max-w-md flex flex-col gap-6">
                    
                    <button onclick="enterSite()" class="opacity-0 animate-pop-in group relative w-full py-4 bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl font-bold text-xl text-white shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-[1.02] transition-all duration-300 overflow-hidden" style="animation-delay: 0.3s;">
                        <span class="relative z-10 flex items-center justify-center gap-3">
                            เข้าสู่เว็บไซต์ <i class="fa-solid fa-arrow-right-long group-hover:translate-x-1 transition-transform"></i>
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                    </button>

                    <div class="text-center text-gray-500 text-sm opacity-0 animate-pop-in" style="animation-delay: 0.4s;">- หรือเลือกเมนูทางลัด -</div>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="shop.php" class="menu-card p-3 rounded-lg flex items-center gap-3 opacity-0 animate-pop-in" style="animation-delay: 0.5s;">
                            <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400"><i class="fa-solid fa-cart-shopping"></i></div>
                            <div class="text-left"><div class="text-sm font-bold text-gray-200">สินค้า</div><div class="text-xs text-gray-500">Products</div></div>
                        </a>
                        <a href="topup.php" class="menu-card p-3 rounded-lg flex items-center gap-3 opacity-0 animate-pop-in" style="animation-delay: 0.6s;">
                            <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-400"><i class="fa-solid fa-wallet"></i></div>
                            <div class="text-left"><div class="text-sm font-bold text-gray-200">เติมเงิน</div><div class="text-xs text-gray-500">Topup</div></div>
                        </a>
                        <a href="redeem.php" class="menu-card p-3 rounded-lg flex items-center gap-3 opacity-0 animate-pop-in" style="animation-delay: 0.7s;">
                            <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400"><i class="fa-solid fa-gift"></i></div>
                            <div class="text-left"><div class="text-sm font-bold text-gray-200">โค้ด</div><div class="text-xs text-gray-500">Redeem</div></div>
                        </a>
                        <a href="contact.php" class="menu-card p-3 rounded-lg flex items-center gap-3 opacity-0 animate-pop-in" style="animation-delay: 0.8s;">
                            <div class="w-10 h-10 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-400"><i class="fa-solid fa-comments"></i></div>
                            <div class="text-left"><div class="text-sm font-bold text-gray-200">ติดต่อ</div><div class="text-xs text-gray-500">Contact</div></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($web_config->background_type == 'video' && !empty($bg_list)): ?>
        <video autoplay muted loop playsinline id="video-bg"><source src="<?php echo $bg_list[0]; ?>" type="video/mp4"></video>
    <?php else: ?>
        <div id="dynamic-bg">
            <?php foreach($bg_list as $index => $img): ?>
                <div class="bg-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo $img; ?>');"></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="bg-overlay"></div>

    <canvas id="season-canvas"></canvas>

    <nav id="main-nav" class="glass-nav fixed top-0 w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center h-20">
                
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="relative w-11 h-11">
                        <div class="absolute inset-0 bg-theme-main blur-lg opacity-40 group-hover:opacity-60 transition duration-500 rounded-full animate-pulse-slow"></div>
                        <?php if($web_config->site_logo): ?>
                            <img src="<?php echo $web_config->site_logo; ?>" class="relative w-full h-full rounded-full object-cover border-2 border-white/10 group-hover:border-theme-main transition duration-300">
                        <?php else: ?>
                            <div class="w-full h-full rounded-full bg-slate-800 flex items-center justify-center border border-white/10"><i class="fa-solid fa-gamepad text-theme-main"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-white tracking-wide group-hover:text-theme-main transition duration-300"><?php echo $web_config->site_name; ?></span>
                        <span class="text-[10px] text-gray-400 font-light tracking-wider uppercase">Best Gaming Service</span>
                    </div>
                </a>

                <div class="hidden lg:flex items-center gap-1 bg-white/5 p-1 rounded-full border border-white/5 backdrop-blur-sm">
                    <?php 
                    $menu_items = [
                        ['url' => 'index.php', 'icon' => 'fa-house', 'label' => 'หน้าหลัก'],
                        ['url' => 'shop.php', 'icon' => 'fa-bag-shopping', 'label' => 'ร้านค้า'],
                        ['url' => 'services.php', 'icon' => 'fa-gamepad', 'label' => 'เติมเกม'],
                        ['url' => 'redeem.php', 'icon' => 'fa-ticket', 'label' => 'แลกโค้ด'],
                        ['url' => 'topup.php', 'icon' => 'fa-wallet', 'label' => 'เติมเงิน'],
                        ['url' => 'contact.php', 'icon' => 'fa-headset', 'label' => 'ช่วยเหลือ'],
                    ];
                    foreach($menu_items as $item): 
                        $isActive = basename($_SERVER['PHP_SELF']) == $item['url'];
                    ?>
                        <a href="<?php echo $item['url']; ?>" 
                           class="nav-link px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 flex items-center gap-2 
                           <?php echo $isActive ? 'text-white' : 'text-gray-400 hover:text-white'; ?> 
                           <?php echo $isActive ? 'active' : ''; ?>">
                           <i class="fa-solid <?php echo $item['icon']; ?> <?php echo $isActive ? 'text-theme-main' : ''; ?>"></i> 
                            <?php echo $item['label']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="flex items-center gap-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="hidden lg:flex flex-col items-end mr-2 cursor-pointer group/balance" onclick="updateBalance()" title="คลิกเพื่ออัปเดตยอดเงิน">
                            <span class="text-[10px] text-gray-400 font-light uppercase tracking-wider flex items-center gap-1">
                                Balance 
                                <i id="refresh-icon" class="fa-solid fa-rotate-right text-[10px] opacity-0 group-hover/balance:opacity-100 transition-all duration-300 text-theme-main"></i>
                            </span>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-coins text-yellow-400 text-xs"></i>
                                <span class="font-bold text-white text-lg leading-none">฿<span id="user-balance"><?php echo number_format($_SESSION['point'], 2); ?></span></span>
                            </div>
                        </div>
                        <div class="relative group z-50">
                            <button class="relative w-10 h-10 rounded-full p-0.5 border-2 border-transparent hover:border-theme-main transition-all duration-300">
                                <img src="<?php echo $_SESSION['profile_img'] ? $_SESSION['profile_img'] : 'https://ui-avatars.com/api/?name='.$_SESSION['username']; ?>" class="w-full h-full rounded-full object-cover bg-slate-800">
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
                            </button>

                            <div class="absolute right-0 mt-3 w-64 glass-card rounded-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right scale-95 group-hover:scale-100 overflow-hidden shadow-2xl shadow-black/50">
                                <div class="p-4 bg-gradient-to-br from-slate-800/80 to-slate-900/80 border-b border-white/5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-theme-main/20 flex items-center justify-center text-theme-main font-bold text-lg">
                                            <?php echo mb_substr($_SESSION['username'], 0, 1); ?>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-white font-bold truncate text-sm"><?php echo $_SESSION['username']; ?></p>
                                            <p class="text-[10px] text-theme-main uppercase tracking-wide bg-theme-main/10 inline-block px-1.5 rounded">Member</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-2 space-y-1">
                                    <a href="profile.php" class="block px-3 py-2.5 rounded-xl hover:bg-white/10 text-sm text-gray-300 hover:text-white transition flex items-center gap-3">
                                        <i class="fa-regular fa-id-card w-5 opacity-70"></i> ข้อมูลส่วนตัว
                                    </a>
                                    <a href="history.php" class="block px-3 py-2.5 rounded-xl hover:bg-white/10 text-sm text-gray-300 hover:text-white transition flex items-center gap-3">
                                        <i class="fa-solid fa-clock-rotate-left w-5 opacity-70"></i> ประวัติการสั่งซื้อ
                                    </a>
                                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                    <a href="admin/dashboard.php" class="block px-3 py-2.5 rounded-xl hover:bg-theme-main/20 text-sm text-theme-main transition flex items-center gap-3">
                                        <i class="fa-solid fa-gauge-high w-5"></i> จัดการระบบ
                                    </a>
                                    <?php endif; ?>
                                    <div class="h-px bg-white/10 my-1 mx-2"></div>
                                    <a href="logout.php" class="block px-3 py-2.5 rounded-xl hover:bg-red-500/20 text-sm text-red-400 hover:text-red-300 transition flex items-center gap-3 group/logout">
                                        <i class="fa-solid fa-power-off w-5 group-hover/logout:rotate-90 transition-transform"></i> ออกจากระบบ
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="hidden lg:flex items-center gap-3">
                            <a href="login.php" class="text-sm font-medium text-gray-300 hover:text-white transition px-4 py-2 hover:bg-white/5 rounded-full">เข้าสู่ระบบ</a>
                            <a href="register.php" class="text-sm font-bold bg-theme-main text-white px-6 py-2.5 rounded-full shadow-[0_0_15px_rgba(var(--theme-main),0.4)] hover:shadow-[0_0_25px_rgba(var(--theme-main),0.6)] hover:-translate-y-0.5 transition-all duration-300">
                                สมัครสมาชิก
                            </a>
                        </div>
                    <?php endif; ?>

                    <button onclick="toggleMobileMenu()" class="lg:hidden w-10 h-10 flex flex-col justify-center items-center gap-1.5 group">
                        <span class="w-6 h-0.5 bg-white rounded-full transition-all group-hover:w-8 group-hover:bg-theme-main"></span>
                        <span class="w-6 h-0.5 bg-white rounded-full transition-all group-hover:w-4 group-hover:bg-theme-main"></span>
                        <span class="w-6 h-0.5 bg-white rounded-full transition-all group-hover:w-6 group-hover:bg-theme-main"></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div id="mobile-menu-overlay" onclick="toggleMobileMenu()" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[60] opacity-0 pointer-events-none transition-opacity duration-300"></div>
    <div id="mobile-menu-drawer" class="fixed top-0 right-0 h-full w-[85%] max-w-[320px] bg-[#0f172a] z-[70] flex flex-col border-l border-white/10 shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5">
            <h2 class="text-xl font-bold text-white">Menu</h2>
            <button onclick="toggleMobileMenu()" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-gray-400 hover:text-white hover:bg-red-500 hover:rotate-90 transition-all duration-300">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-1">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <a href="login.php" class="py-3 text-center rounded-xl bg-slate-800 text-gray-300 border border-white/5">เข้าสู่ระบบ</a>
                    <a href="register.php" class="py-3 text-center rounded-xl bg-theme-main text-white shadow-lg shadow-theme-main/20">สมัครสมาชิก</a>
                </div>
            <?php else: ?>
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 rounded-2xl border border-white/5 mb-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition"><i class="fa-solid fa-wallet text-6xl text-theme-main"></i></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <img src="<?php echo $_SESSION['profile_img'] ? $_SESSION['profile_img'] : 'https://ui-avatars.com/api/?name='.$_SESSION['username']; ?>" class="w-12 h-12 rounded-full border-2 border-theme-main">
                        <div>
                            <div class="text-white font-bold"><?php echo $_SESSION['username']; ?></div>
                            <div class="text-theme-main text-sm font-semibold flex items-center gap-2" onclick="updateBalance()">
                                ฿ <span id="mobile-balance"><?php echo number_format($_SESSION['point'], 2); ?></span> <i class="fa-solid fa-rotate-right text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="space-y-1">
                <?php foreach($menu_items as $mItem): $isMActive = basename($_SERVER['PHP_SELF']) == $mItem['url']; ?>
                <a href="<?php echo $mItem['url']; ?>" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?php echo $isMActive ? 'bg-theme-main text-white shadow-lg shadow-theme-main/20' : 'text-gray-400 hover:bg-white/5 hover:text-white hover:pl-6'; ?>">
                    <i class="fa-solid <?php echo $mItem['icon']; ?> w-6 text-center"></i>
                    <span class="font-medium"><?php echo $mItem['label']; ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="h-px bg-white/5 my-4"></div>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin/dashboard.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-theme-main hover:bg-theme-main/10 transition-all hover:pl-6">
                        <i class="fa-solid fa-gauge w-6 text-center"></i> จัดการระบบ
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-red-400 hover:bg-red-500/10 transition-all hover:pl-6">
                    <i class="fa-solid fa-right-from-bracket w-6 text-center"></i> ออกจากระบบ
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!empty($web_config->marquee_text)): ?>
    <div class="pt-24 pb-2 container mx-auto px-4 animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="glass-card rounded-full py-2 px-4 flex items-center gap-4 overflow-hidden relative">
            <div class="flex-shrink-0 bg-theme-main/20 text-theme-main px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-theme-main opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-theme-main"></span>
                </span>
                News
            </div>
            <marquee scrollamount="4" class="text-sm font-light text-gray-300">
                <?php echo $web_config->marquee_text; ?>
            </marquee>
        </div>
    </div>
    <?php else: ?>
    <div class="pt-24"></div>
    <?php endif; ?>

    <main class="flex-grow container mx-auto px-4 py-6 relative z-10 animate-fade-in-up" style="animation-delay: 0.2s;">

    <script>
        // --- Logic: Enter Site ---
        function enterSite() {
            const overlay = document.getElementById('landing-overlay');
            overlay.classList.add('hidden-overlay');
            sessionStorage.setItem('enteredSite', 'true');
        }

        // --- Nav Scroll ---
        const navbar = document.getElementById('main-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) { navbar.classList.add('scrolled'); } 
            else { navbar.classList.remove('scrolled'); }
        });

        function toggleMobileMenu() {
            const overlay = document.getElementById('mobile-menu-overlay');
            const drawer = document.getElementById('mobile-menu-drawer');
            if (drawer.classList.contains('open')) {
                drawer.classList.remove('open'); overlay.classList.remove('opacity-100', 'pointer-events-auto'); overlay.classList.add('opacity-0', 'pointer-events-none'); document.body.style.overflow = '';
            } else {
                drawer.classList.add('open'); overlay.classList.remove('opacity-0', 'pointer-events-none'); overlay.classList.add('opacity-100', 'pointer-events-auto'); document.body.style.overflow = 'hidden';
            }
        }

        // --- AJAX Update Balance ---
        async function updateBalance() {
            const icon = document.getElementById('refresh-icon');
            const balanceText = document.getElementById('user-balance');
            const mobileBalance = document.getElementById('mobile-balance');

            if(icon) icon.classList.add('animate-spin-fast');

            try {
                const response = await fetch('api/check_balance.php'); 
                if (!response.ok) throw new Error("API Error");

                const data = await response.json();
                if (data.status === 'success') {
                    if(balanceText) {
                        balanceText.innerText = data.point;
                        balanceText.classList.add('text-flash');
                        setTimeout(() => balanceText.classList.remove('text-flash'), 500);
                    }
                    if(mobileBalance) mobileBalance.innerText = data.point;
                } else {
                    window.location.reload();
                }
            } catch (error) {
                console.warn('Auto refresh failed, reloading...');
                window.location.reload();
            } finally {
                setTimeout(() => { if(icon) icon.classList.remove('animate-spin-fast'); }, 800);
            }
        }

        // --- BG Slider ---
        <?php if ($web_config->background_type == 'image' && count($bg_list) > 1): ?>
        const slides = document.querySelectorAll('.bg-slide');
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 6000); 
        <?php endif; ?>

        // --- Seasonal Canvas ---
        const season = '<?php echo $season['effect']; ?>';
        const canvas = document.getElementById('season-canvas');
        const ctx = canvas.getContext('2d');
        let width = window.innerWidth;
        let height = window.innerHeight;
        canvas.width = width; canvas.height = height;

        window.addEventListener('resize', () => { width = window.innerWidth; height = window.innerHeight; canvas.width = width; canvas.height = height; });

        class Particle {
            constructor(type) { this.type = type; this.reset(); }
            reset() {
                this.x = Math.random() * width; this.y = Math.random() * height - height;
                this.speed = Math.random() * 2 + 1; this.size = Math.random() * 3 + 1; this.opacity = Math.random() * 0.5 + 0.3;
                if (this.type === 'snow') { this.vx = Math.random() * 1 - 0.5; }
                else if (this.type === 'hearts') { this.y = height + Math.random() * 100; this.speed = Math.random() * 1.5 + 0.5; this.size = Math.random() * 15 + 10; this.vx = Math.random() * 0.6 - 0.3; }
                else if (this.type === 'spooky') { this.speed = Math.random() * 0.5 + 0.2; this.size = Math.random() * 200 + 100; this.vx = Math.random() * 0.5 - 0.25; this.opacity = 0.05; }
            }
            update() {
                if (this.type === 'snow') { this.y += this.speed; this.x += this.vx; if (this.y > height) this.reset(); }
                else if (this.type === 'hearts') { this.y -= this.speed; this.x += Math.sin(this.y * 0.01) * 0.5; if (this.y < -50) this.reset(); }
                else if (this.type === 'spooky') { this.x += this.vx; if (this.x > width + 200 || this.x < -200) this.reset(); }
            }
            draw() {
                ctx.globalAlpha = this.opacity;
                if (this.type === 'snow') { ctx.fillStyle = '#FFF'; ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill(); }
                else if (this.type === 'hearts') { ctx.font = this.size + 'px serif'; ctx.fillText('❤️', this.x, this.y); }
                else if (this.type === 'spooky') { ctx.fillStyle = '#AAAAAA'; ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill(); }
            }
        }

        let fireworks = [];
        class Firework { 
            constructor() { this.x = Math.random() * width; this.y = height; this.targetY = Math.random() * (height / 2); this.speed = 10; this.particles = []; this.exploded = false; this.color = `hsl(${Math.random() * 360}, 100%, 50%)`; }
            update() { if (!this.exploded) { this.y -= this.speed; if (this.y <= this.targetY) this.explode(); } else { for (let i = this.particles.length - 1; i >= 0; i--) { this.particles[i].update(); if (this.particles[i].alpha <= 0) this.particles.splice(i, 1); } } }
            draw() { if (!this.exploded) { ctx.fillStyle = this.color; ctx.beginPath(); ctx.arc(this.x, this.y, 3, 0, Math.PI*2); ctx.fill(); } else { this.particles.forEach(p => p.draw()); } }
            explode() { this.exploded = true; for (let i = 0; i < 50; i++) this.particles.push(new FireworkParticle(this.x, this.y, this.color)); }
        }
        class FireworkParticle {
            constructor(x, y, color) { this.x = x; this.y = y; this.color = color; const angle = Math.random() * Math.PI * 2; const speed = Math.random() * 4; this.vx = Math.cos(angle) * speed; this.vy = Math.sin(angle) * speed; this.alpha = 1; this.decay = Math.random() * 0.02 + 0.01; }
            update() { this.x += this.vx; this.y += this.vy; this.vy += 0.05; this.alpha -= this.decay; }
            draw() { ctx.globalAlpha = this.alpha; ctx.fillStyle = this.color; ctx.beginPath(); ctx.arc(this.x, this.y, 2, 0, Math.PI*2); ctx.fill(); }
        }

        const particles = [];
        if (season === 'snow') { for(let i=0; i<100; i++) particles.push(new Particle('snow')); }
        else if (season === 'hearts') { for(let i=0; i<30; i++) particles.push(new Particle('hearts')); }
        else if (season === 'spooky') { for(let i=0; i<20; i++) particles.push(new Particle('spooky')); }

        function animate() {
            ctx.clearRect(0, 0, width, height);
            particles.forEach(p => { p.update(); p.draw(); });
            if (season === 'fireworks') {
                if (Math.random() < 0.03) fireworks.push(new Firework());
                for (let i = fireworks.length - 1; i >= 0; i--) { fireworks[i].update(); fireworks[i].draw(); if (fireworks[i].exploded && fireworks[i].particles.length === 0) fireworks.splice(i, 1); }
            }
            requestAnimationFrame(animate);
        }
        if (season !== 'normal') animate();

        // --- Emoji Fallback ---
        <?php if ($season['effect'] == 'normal' && !empty($emojis)): ?>
        const emojiList = <?php echo json_encode(array_values($emojis)); ?>;
        function spawnEmoji() {
            if (emojiList.length === 0) return;
            const emoji = document.createElement('div');
            emoji.innerText = emojiList[Math.floor(Math.random() * emojiList.length)];
            emoji.classList.add('floating-emoji');
            emoji.style.left = (Math.random() * 90 + 5) + 'vw';
            emoji.style.fontSize = (Math.random() * 24 + 20) + 'px';
            const duration = Math.random() * 5 + 6; 
            emoji.style.animationDuration = duration + 's';
            document.body.appendChild(emoji);
            setTimeout(() => { emoji.remove(); }, duration * 1000);
        }
        setInterval(spawnEmoji, 1200); 
        <?php endif; ?>
    </script>
</body>
</html>