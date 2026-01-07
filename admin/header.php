<?php 
// admin/header.php
require_once '../db.php';

// ตรวจสอบสิทธิ์แอดมิน
checkAdmin($pdo);

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Panel - <?php echo $web_config->site_name; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Kanit', 'sans-serif'] },
                    colors: {
                        theme: { 
                            main: '#8b5cf6', 
                            hover: '#7c3aed',
                            dark: '#0f172a', 
                            card: '#1e293b' 
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'slide-in': 'slideIn 0.3s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { 
            background-color: #0f172a; 
            color: #f8fafc; 
            background-image: radial-gradient(at 0% 0%, rgba(139, 92, 246, 0.15) 0px, transparent 50%), 
                              radial-gradient(at 100% 100%, rgba(15, 23, 42, 1) 0px, transparent 50%);
            background-attachment: fixed;
            -webkit-tap-highlight-color: transparent; /* ลบสีฟ้าเวลาจิ้มบนมือถือ */
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #8b5cf6; }

        /* Sidebar Glass Effect */
        .glass-sidebar {
            background: rgba(15, 23, 42, 0.95); /* เพิ่มความทึบแสงขึ้นอีกนิดสำหรับมือถือ */
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Nav Item Styles */
        .nav-item {
            position: relative;
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            padding-left: 1.25rem;
        }
        .nav-item.active {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.9) 0%, rgba(124, 58, 237, 0.8) 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: white;
            border-radius: 0 4px 4px 0;
        }

        /* Mobile specific fixes */
        .safe-area-padding {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-sm md:text-base">

    <div id="mobile-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[60] hidden md:hidden transition-opacity duration-300 opacity-0 touch-none"></div>

    <aside id="sidebar" class="glass-sidebar w-[280px] h-full flex flex-col fixed md:relative transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-[70] safe-area-padding shadow-2xl md:shadow-none">
        
        <div class="h-20 flex items-center px-6 border-b border-white/5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white shadow-lg shadow-violet-500/30">
                    <i class="fa-solid fa-shield-cat text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-wide text-white">ADMIN</h1>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">Control Panel</p>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden ml-auto w-8 h-8 rounded-full bg-white/5 text-gray-400 hover:text-white flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <nav class="flex-grow p-4 space-y-1 overflow-y-auto custom-scrollbar">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Main Menu</p>
            
            <a href="dashboard.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie w-6 text-center"></i> 
                <span class="font-medium">ภาพรวมระบบ</span>
            </a>

            <a href="orders.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-receipt w-6 text-center"></i> 
                <span class="font-medium">รายการสั่งซื้อ</span>
            </a>

            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Management</p>

            <a href="products.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo strpos($current_page, 'product') !== false ? 'active' : ''; ?>">
                <i class="fa-solid fa-box-open w-6 text-center"></i> 
                <span class="font-medium">จัดการสินค้า</span>
            </a>

            <a href="categories.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo strpos($current_page, 'categor') !== false ? 'active' : ''; ?>">
                <i class="fa-solid fa-layer-group w-6 text-center"></i> 
                <span class="font-medium">หมวดหมู่</span>
            </a>

            <a href="manage_codes.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo $current_page == 'manage_codes.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-ticket w-6 text-center"></i> 
                <span class="font-medium">สต็อกโค้ด</span>
            </a>
            
            <a href="manage_services.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo $current_page == 'manage_services.php' ? 'active' : ''; ?>">
                <i class="fa-brands fa-servicestack w-6 text-center"></i>
                <span class="font-medium">บริการเติมเกม</span>
            </a>

            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">System</p>

            <a href="users.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users w-6 text-center"></i> 
                <span class="font-medium">สมาชิก</span>
            </a>

            <a href="settings.php" class="nav-item flex items-center gap-3 px-4 py-3.5 text-gray-400 <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-sliders w-6 text-center"></i> 
                <span class="font-medium">ตั้งค่าเว็บไซต์</span>
            </a>
        </nav>

        <div class="p-4 border-t border-white/5 bg-black/20 shrink-0 mb-safe">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center border border-slate-600">
                    <i class="fa-solid fa-user-astronaut text-gray-300"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-white truncate"><?php echo $_SESSION['username'] ?? 'Admin'; ?></p>
                    <p class="text-xs text-green-400 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Online</p>
                </div>
            </div>
            <a href="../index.php" class="block w-full text-center bg-slate-800 hover:bg-slate-700 text-gray-300 py-2 rounded-lg transition text-xs border border-slate-700">
                <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> กลับหน้าร้านค้า
            </a>
        </div>
    </aside>

    <main class="flex-grow flex flex-col h-screen overflow-hidden relative w-full">
        
        <header class="md:hidden h-16 bg-slate-900/90 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-4 z-50 sticky top-0 safe-area-padding">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-theme-main flex items-center justify-center text-white shadow-lg shadow-theme-main/20">
                    <i class="fa-solid fa-shield-cat"></i>
                </div>
                <span class="font-bold text-lg text-white">Admin</span>
            </div>
            <button onclick="toggleSidebar()" class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white active:scale-95 transition">
                <i class="fa-solid fa-bars-staggered text-lg"></i>
            </button>
        </header>

        <div class="flex-grow overflow-y-auto custom-scrollbar relative w-full">
            <div class="fixed top-0 right-0 -z-10 w-[500px] h-[500px] bg-purple-600/10 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="animate-fade-in p-4 md:p-8 pb-24 md:pb-8 w-full max-w-full">
            
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const body = document.body;
        
        if (sidebar.classList.contains('-translate-x-full')) {
            // Open Menu
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            // ล็อคการเลื่อนหน้าจอข้างหลัง (สำคัญสำหรับมือถือ)
            body.style.overflow = 'hidden'; 
            
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        } else {
            // Close Menu
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            body.style.overflow = ''; // ปลดล็อคการเลื่อน
            
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }
</script>