<?php include 'db.php'; ?>
<?php
require_once 'header.php';

// ดึงหมวดหมู่สินค้า
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();

// ดึงสินค้าแนะนำ (หรือสินค้าทั้งหมดถ้าไม่มีแนะนำ) ดึง 8 ชิ้นล่าสุด
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8")->fetchAll();

// --- ส่วนจัดการข้อมูลแบนเนอร์ (รองรับทั้งแบบลิ้งค์เดียวและแบบ JSON หลายรูป) ---
$banners = [];
if (!empty($web_config->banner_img)) {
    // ลองแปลงจาก JSON
    $decoded = json_decode($web_config->banner_img, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $banners = $decoded; // ถ้าเป็น JSON (หลายรูป)
    } else {
        $banners[] = $web_config->banner_img; // ถ้าเป็นลิ้งค์เดียว (แบบเก่า)
    }
}
?>

<div class="mb-10 relative rounded-2xl overflow-hidden shadow-2xl group h-[300px] md:h-[400px] bg-slate-900">
    
    <?php if(!empty($banners)): ?>
        <div id="banner-slider" class="relative w-full h-full">
            <?php foreach($banners as $index => $img): ?>
                <div class="banner-slide absolute inset-0 transition-opacity duration-1000 ease-in-out <?php echo $index === 0 ? 'opacity-100' : 'opacity-0'; ?>">
                    <img src="<?php echo trim($img); ?>" class="w-full h-full object-cover">
                </div>
            <?php endforeach; ?>
        </div>

        <?php if(count($banners) > 1): ?>
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
            <?php foreach($banners as $index => $img): ?>
                <button onclick="changeSlide(<?php echo $index; ?>)" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white transition banner-dot <?php echo $index === 0 ? 'bg-white' : ''; ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="w-full h-full bg-gradient-to-r from-violet-900 to-slate-900 flex items-center justify-center">
            <h1 class="text-4xl md:text-6xl font-bold text-white opacity-20 select-none">SHOP BANNER</h1>
        </div>
    <?php endif; ?>
    
    <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 via-black/50 to-transparent p-6 z-10 pointer-events-none">
        <h2 class="text-3xl font-bold text-white text-glow drop-shadow-md">ยินดีต้อนรับสู่ <?php echo $web_config->site_name; ?></h2>
        <p class="text-gray-300 mt-2 drop-shadow-sm">แหล่งรวมไอดีเกมและสินค้าดิจิตอลคุณภาพ อันดับ 1</p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let currentSlide = 0;
    const slides = document.querySelectorAll('.banner-slide');
    const dots = document.querySelectorAll('.banner-dot');
    const totalSlides = slides.length;
    let slideInterval;

    function showSlide(index) {
        if (totalSlides <= 1) return;

        slides.forEach(s => {
            s.classList.remove('opacity-100');
            s.classList.add('opacity-0');
        });

        dots.forEach(d => {
            d.classList.remove('bg-white');
            d.classList.add('bg-white/50');
        });

        slides[index].classList.remove('opacity-0');
        slides[index].classList.add('opacity-100');

        if (dots[index]) {
            dots[index].classList.remove('bg-white/50');
            dots[index].classList.add('bg-white');
        }

        currentSlide = index;
    }

    function nextSlide() {
        let next = (currentSlide + 1) % totalSlides;
        showSlide(next);
    }

    window.changeSlide = function (index) {
        showSlide(index);
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }

    if (totalSlides > 1) {
        slideInterval = setInterval(nextSlide, 5000);
    }

});
</script>


<div class="mb-12">
    <h3 class="text-2xl font-bold mb-6 border-l-4 border-theme-main pl-4 flex items-center gap-2">
        <i class="fa-solid fa-layer-group"></i> หมวดหมู่สินค้า
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <a href="shop.php?cat=all" class="glass p-4 rounded-xl text-center hover:bg-theme-main hover:border-theme-main transition group cursor-pointer">
            <div class="w-14 h-14 mx-auto bg-slate-700/50 rounded-full flex items-center justify-center mb-3 group-hover:bg-white/20">
                <i class="fa-solid fa-border-all text-2xl text-gray-300 group-hover:text-white"></i>
            </div>
            <span class="font-medium text-gray-300 group-hover:text-white">ทั้งหมด</span>
        </a>
        <?php foreach($cats as $cat): ?>
        <a href="shop.php?cat=<?php echo $cat->id; ?>" class="glass p-4 rounded-xl text-center hover:bg-theme-main hover:border-theme-main transition group cursor-pointer">
            <img src="<?php echo $cat->img; ?>" class="w-14 h-14 mx-auto rounded-full object-cover mb-3 bg-slate-800">
            <span class="font-medium text-gray-300 group-hover:text-white"><?php echo $cat->name; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div>
    <div class="flex justify-between items-end mb-6">
        <h3 class="text-2xl font-bold border-l-4 border-theme-main pl-4 flex items-center gap-2">
            <i class="fa-solid fa-fire text-orange-500"></i> สินค้ามาใหม่
        </h3>
        <a href="shop.php" class="text-slate-400 hover:text-white text-sm">ดูทั้งหมด <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <?php if(count($products) > 0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach($products as $p): 
            // เช็คสต็อก
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE product_id = ? AND is_sold = 0");
            $stmt->execute([$p->id]);
            $stock = $stmt->fetchColumn();
        ?>
        <div class="glass rounded-xl overflow-hidden hover:shadow-2xl hover:shadow-purple-500/20 transition duration-300 flex flex-col h-full relative group border border-slate-700 hover:border-theme-main/50">
            
            <?php if($p->is_gacha): ?>
                <div class="absolute top-2 right-2 bg-yellow-500 text-black text-xs font-bold px-2 py-1 rounded shadow-lg z-10 animate-pulse">
                    <i class="fa-solid fa-star"></i> สุ่มรางวัล
                </div>
            <?php endif; ?>

            <div class="relative overflow-hidden h-48">
                <img src="<?php echo $p->img; ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                <?php if($stock == 0): ?>
                    <div class="absolute inset-0 bg-black/70 flex items-center justify-center">
                        <span class="text-red-500 font-bold border-2 border-red-500 px-4 py-1 rounded rotate-12 text-xl">SOLD OUT</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-4 flex flex-col flex-grow">
                <h4 class="font-bold text-lg mb-1 line-clamp-1" title="<?php echo $p->name; ?>"><?php echo $p->name; ?></h4>
                
                <div class="flex justify-between items-center text-sm text-gray-400 mb-3">
                    <span><i class="fa-solid fa-box-open"></i> พร้อมส่ง: <span class="<?php echo $stock > 0 ? 'text-green-400' : 'text-red-400'; ?>"><?php echo $stock; ?></span> ชิ้น</span>
                </div>

                <div class="mt-auto pt-3 border-t border-slate-700 flex justify-between items-center">
                    <span class="text-xl font-bold text-theme-main">฿ <?php echo number_format($p->price, 0); ?></span>
                    
                    <a href="product_detail.php?id=<?php echo $p->id; ?>" class="bg-slate-700 hover:bg-theme-main text-white px-4 py-2 rounded-lg transition text-sm">
                        <i class="fa-solid fa-cart-shopping"></i> ซื้อ/ดู
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="text-center py-20 bg-slate-800/50 rounded-xl border border-dashed border-slate-600">
            <i class="fa-solid fa-box-open text-6xl text-slate-600 mb-4"></i>
            <p class="text-slate-400">ยังไม่มีสินค้าในขณะนี้</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>