<?php include 'db.php'; ?>
<?php
require_once 'header.php';

// 1. รับค่าค้นหาและหมวดหมู่
$search = $_GET['search'] ?? '';
$cat_id = $_GET['cat'] ?? '';

// 2. สร้าง Query แบบ Dynamic
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if ($cat_id) {
    $sql .= " AND category_id = ?";
    $params[] = $cat_id;
}

$sql .= " ORDER BY id DESC"; // สินค้าใหม่มาก่อน
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// 3. ดึงหมวดหมู่ทั้งหมดมาทำเมนู
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="container mx-auto py-10 px-4">
    
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-10">
        <div>
            <h1 class="text-4xl font-bold text-glow">ร้านค้าทั้งหมด</h1>
            <p class="text-gray-400">เลือกซื้อสินค้าดิจิทัลคุณภาพเยี่ยม</p>
        </div>

        <form class="w-full md:w-1/3 relative">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาสินค้า..." class="w-full bg-slate-900 border border-slate-700 rounded-full py-3 px-6 pl-12 text-white focus:border-theme-main focus:outline-none shadow-lg">
            <button type="submit" class="absolute left-4 top-3 text-gray-400 hover:text-white">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <div class="flex flex-wrap gap-3 mb-8 overflow-x-auto pb-2 scrollbar-hide">
        <a href="shop.php" class="px-6 py-2 rounded-full border transition whitespace-nowrap <?php echo $cat_id == '' ? 'bg-theme-main border-theme-main text-white' : 'bg-slate-900 border-slate-700 text-gray-400 hover:border-theme-main hover:text-white'; ?>">
            ทั้งหมด
        </a>
        <?php foreach($categories as $c): ?>
            <a href="shop.php?cat=<?php echo $c->id; ?>" class="px-6 py-2 rounded-full border transition whitespace-nowrap <?php echo $cat_id == $c->id ? 'bg-theme-main border-theme-main text-white' : 'bg-slate-900 border-slate-700 text-gray-400 hover:border-theme-main hover:text-white'; ?>">
                <?php echo $c->name; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if(count($products) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($products as $p): 
                // เช็คสต็อกเล็กน้อยเพื่อแสดงป้าย
                $stock = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE product_id = ? AND is_sold = 0");
                $stock->execute([$p->id]);
                $stock_count = $stock->fetchColumn();
            ?>
            <div class="glass rounded-2xl overflow-hidden hover:transform hover:scale-[1.02] transition duration-300 border border-slate-700 group relative flex flex-col h-full">
                
                <?php if($stock_count == 0): ?>
                    <div class="absolute inset-0 bg-black/70 z-10 flex items-center justify-center">
                        <span class="text-red-500 font-bold border-2 border-red-500 px-4 py-1 rounded -rotate-12">SOLD OUT</span>
                    </div>
                <?php elseif($p->is_gacha): ?>
                    <div class="absolute top-2 right-2 z-10 bg-yellow-500 text-black text-xs font-bold px-2 py-1 rounded shadow animate-pulse">
                        <i class="fa-solid fa-star"></i> สุ่ม
                    </div>
                <?php endif; ?>

                <div class="h-48 overflow-hidden relative">
                    <img src="<?php echo $p->img; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                </div>
                
                <div class="p-5 flex flex-col flex-grow">
                    <h3 class="font-bold text-lg mb-1 truncate"><?php echo $p->name; ?></h3>
                    <p class="text-xs text-gray-400 mb-4 line-clamp-2"><?php echo strip_tags($p->description); ?></p>
                    
                    <div class="mt-auto flex items-center justify-between">
                        <div class="text-theme-main font-bold text-xl">฿ <?php echo number_format($p->price, 0); ?></div>
                        <a href="product_detail.php?id=<?php echo $p->id; ?>" class="bg-slate-800 hover:bg-theme-main text-white px-4 py-2 rounded-lg transition text-sm">
                            <?php echo $stock_count > 0 ? 'สั่งซื้อ' : 'สินค้าหมด'; ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-20 text-gray-500">
            <i class="fa-solid fa-box-open text-6xl mb-4 opacity-50"></i>
            <p>ไม่พบสินค้าที่คุณค้นหา</p>
            <a href="shop.php" class="text-theme-main underline mt-2 inline-block">ดูสินค้าทั้งหมด</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>