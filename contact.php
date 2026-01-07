<?php include 'db.php'; ?>
<?php
require_once 'header.php';
?>

<div class="container mx-auto py-12 px-4 min-h-[80vh] flex flex-col items-center">
    
    <div class="text-center mb-12 fade-in-up max-w-2xl">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">
            ติดต่อเรา / ช่วยเหลือ
        </h1>
        <div class="h-1 w-20 bg-theme-main mx-auto rounded-full mb-4"></div>
        <p class="text-slate-400 text-lg">มีปัญหาในการใช้งาน หรือต้องการสอบถามข้อมูลเพิ่มเติม ติดต่อเราได้ทุกช่องทาง</p>
    </div>

    <div class="flex flex-wrap justify-center gap-6 mb-16 w-full">
        
        <?php 
        $channels = [
            [
                'name' => 'Facebook Page',
                'desc' => 'ติดตามข่าวสาร โปรโมชั่น',
                'url' => $web_config->facebook_url,
                'icon' => 'fa-facebook',
                'color' => 'text-blue-500',
                'bg_hover' => 'hover:bg-blue-600/20 hover:border-blue-500/50'
            ],
            [
                'name' => 'Line Official',
                'desc' => 'แจ้งปัญหา เติมเงินไม่เข้า',
                'url' => $web_config->line_url,
                'icon' => 'fa-line',
                'color' => 'text-green-500',
                'bg_hover' => 'hover:bg-green-600/20 hover:border-green-500/50'
            ],
            [
                'name' => 'Discord',
                'desc' => 'พูดคุยกับชุมชน',
                'url' => $web_config->discord_url,
                'icon' => 'fa-discord',
                'color' => 'text-indigo-500',
                'bg_hover' => 'hover:bg-indigo-600/20 hover:border-indigo-500/50'
            ],
            [
                'name' => 'YouTube',
                'desc' => 'รีวิว วิธีใช้งาน',
                'url' => $web_config->youtube_url,
                'icon' => 'fa-youtube',
                'color' => 'text-red-500',
                'bg_hover' => 'hover:bg-red-600/20 hover:border-red-500/50'
            ],
            [
                'name' => 'TikTok',
                'desc' => 'คลิปสั้น ไฮไลท์',
                'url' => $web_config->tiktok_url,
                'icon' => 'fa-tiktok',
                'color' => 'text-pink-500',
                'bg_hover' => 'hover:bg-pink-600/20 hover:border-pink-500/50'
            ],
            [
                'name' => 'Instagram',
                'desc' => 'รูปภาพสินค้า',
                'url' => $web_config->instagram_url,
                'icon' => 'fa-instagram',
                'color' => 'text-purple-500',
                'bg_hover' => 'hover:bg-purple-600/20 hover:border-purple-500/50'
            ]
        ];

        $has_contact = false;
        foreach($channels as $ch): 
            if(!empty($ch['url'])): 
                $has_contact = true;
        ?>
            <a href="<?php echo $ch['url']; ?>" target="_blank" class="w-full md:w-[350px] glass p-6 rounded-2xl border border-slate-700 transition-all duration-300 transform hover:-translate-y-2 group <?php echo $ch['bg_hover']; ?> flex items-center gap-4">
                <div class="shrink-0 w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center text-2xl shadow-lg <?php echo $ch['color']; ?> group-hover:scale-110 transition">
                    <i class="fa-brands <?php echo $ch['icon']; ?>"></i>
                </div>
                <div class="flex-grow">
                    <h3 class="text-lg font-bold text-white group-hover:text-theme-main transition"><?php echo $ch['name']; ?></h3>
                    <p class="text-xs text-slate-400"><?php echo $ch['desc']; ?></p>
                </div>
                <div class="opacity-0 group-hover:opacity-100 transition transform -translate-x-2 group-hover:translate-x-0">
                    <i class="fa-solid fa-chevron-right text-slate-500"></i>
                </div>
            </a>
        <?php 
            endif; 
        endforeach; 

        if(!$has_contact):
        ?>
            <div class="w-full text-center py-10 bg-slate-800/50 rounded-xl border border-dashed border-slate-600">
                <p class="text-slate-400">ยังไม่ได้เพิ่มช่องทางการติดต่อ</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="w-full max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 flex items-center justify-center gap-3 text-white">
            <i class="fa-solid fa-circle-question text-theme-main"></i> คำถามที่พบบ่อย (FAQ)
        </h2>

        <div class="space-y-4">
            <div class="glass p-6 rounded-xl border border-slate-700 hover:border-slate-500 transition">
                <h3 class="font-bold text-lg text-white mb-2 flex items-start gap-2">
                    <i class="fa-solid fa-check-circle text-green-500 mt-1"></i> เติมเงินแล้วยอดไม่เข้า ทำอย่างไร?
                </h3>
                <p class="text-slate-400 pl-7 text-sm">
                    ระบบเติมเงิน TrueWallet ซองของขวัญทำงานอัตโนมัติภายใน 1-3 นาที หากเกินเวลากรุณาติดต่อแอดมินพร้อมแนบลิงก์ซองครับ
                </p>
            </div>

            <div class="glass p-6 rounded-xl border border-slate-700 hover:border-slate-500 transition">
                <h3 class="font-bold text-lg text-white mb-2 flex items-start gap-2">
                    <i class="fa-solid fa-box text-blue-500 mt-1"></i> สินค้าหมด จะมาเติมตอนไหน?
                </h3>
                <p class="text-slate-400 pl-7 text-sm">
                    เราเติมสต็อกสินค้าทุกวันช่วงเย็น ติดตามการประกาศได้ที่หน้าเพจ Facebook
                </p>
            </div>

            <div class="glass p-6 rounded-xl border border-slate-700 hover:border-slate-500 transition">
                <h3 class="font-bold text-lg text-white mb-2 flex items-start gap-2">
                    <i class="fa-solid fa-lock text-red-500 mt-1"></i> เปลี่ยนรหัสผ่านตรงไหน?
                </h3>
                <p class="text-slate-400 pl-7 text-sm">
                    ไปที่เมนู "โปรไฟล์" (คลิกที่ชื่อตัวเองมุมขวาบน) > เลือก "แก้ไขข้อมูลส่วนตัว"
                </p>
            </div>
        </div>

        <div class="mt-10 text-center">
            <div class="glass py-3 px-6 rounded-full inline-flex items-center gap-3 border border-theme-main/30">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-slate-300 text-sm">ระบบทำงานอัตโนมัติ: <span class="text-white font-bold">24 ชั่วโมง</span></span>
            </div>
        </div>
    </div>

</div>

<style>
    .glass {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
    }
    .fade-in-up {
        animation: fadeInUp 0.8s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php require_once 'footer.php'; ?>