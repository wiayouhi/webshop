<?php include 'db.php'; ?>
<?php
require_once 'header.php';

// รับค่า ID สินค้า
if (!isset($_GET['id'])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container mx-auto py-20 text-center text-red-500'>ไม่พบสินค้านี้</div>";
    require_once 'footer.php';
    exit;
}

// เช็คสต็อกสินค้า
$stmt = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE product_id = ? AND is_sold = 0");
$stmt->execute([$id]);
$stock = $stmt->fetchColumn();

// --- ดึงรูปภาพ Gallery เพิ่มเติม ---
$stmt_img = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt_img->execute([$id]);
$gallery = $stmt_img->fetchAll();
?>

<div class="max-w-6xl mx-auto py-10">
    <div class="glass rounded-2xl p-6 md:p-10 shadow-2xl border border-slate-700">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            
            <div>
                <div class="relative group mb-4">
                    <div class="overflow-hidden rounded-xl border-2 border-slate-700 shadow-lg relative aspect-square bg-slate-900">
                        <img id="mainImage" src="<?php echo $product->img; ?>" class="w-full h-full object-contain hover:scale-105 transition duration-500">
                        
                        <?php if($product->is_gacha): ?>
                            <div class="absolute top-4 right-4 bg-yellow-500 text-black font-bold px-3 py-1 rounded shadow animate-pulse z-10">
                                <i class="fa-solid fa-star"></i> สินค้าสุ่ม
                            </div>
                        <?php endif; ?>

                        <?php if($stock == 0): ?>
                            <div class="absolute inset-0 bg-black/80 flex items-center justify-center z-20 pointer-events-none">
                                <span class="text-red-500 font-bold border-4 border-red-500 px-6 py-2 rounded-xl -rotate-12 text-3xl">SOLD OUT</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-5 gap-2 select-none">
                    <div class="cursor-pointer border-2 border-transparent hover:border-theme-main rounded-lg overflow-hidden transition opacity-70 hover:opacity-100"
                         onclick="changeImage('<?php echo $product->img; ?>')">
                        <img src="<?php echo $product->img; ?>" class="w-full h-full object-cover aspect-square">
                    </div>
                    
                    <?php foreach($gallery as $img): ?>
                        <div class="cursor-pointer border-2 border-transparent hover:border-theme-main rounded-lg overflow-hidden transition opacity-70 hover:opacity-100"
                             onclick="changeImage('<?php echo $img->img_path; ?>')">
                            <img src="<?php echo $img->img_path; ?>" class="w-full h-full object-cover aspect-square">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex flex-col">
                <h1 class="text-3xl md:text-4xl font-bold mb-4 text-glow"><?php echo $product->name; ?></h1>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="bg-slate-800 px-4 py-2 rounded-lg border border-slate-600">
                        <span class="text-gray-400 text-sm">ราคา</span>
                        <div class="text-2xl font-bold text-theme-main">฿ <?php echo number_format($product->price, 2); ?></div>
                    </div>
                    <div class="bg-slate-800 px-4 py-2 rounded-lg border border-slate-600">
                        <span class="text-gray-400 text-sm">สินค้าคงเหลือ</span>
                        <div class="text-2xl font-bold <?php echo $stock > 0 ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php echo $stock; ?> <span class="text-sm text-gray-500">ชิ้น</span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-700 mb-6 flex-grow prose prose-invert max-w-none">
                    <h3 class="text-lg font-bold text-white mb-2"><i class="fa-solid fa-circle-info text-theme-main"></i> รายละเอียดสินค้า</h3>
                    <div class="text-gray-300 text-sm leading-relaxed">
                        <?php echo $product->description; ?>
                    </div>
                </div>

                <?php if($stock > 0): ?>
                    <div class="bg-slate-800 p-4 rounded-xl border border-slate-700">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <div class="flex items-center gap-4">
                                <div class="w-1/3">
                                    <label class="text-sm text-gray-400 mb-1 block">จำนวนที่จะซื้อ</label>
                                    <div class="flex items-center bg-slate-900 rounded-lg border border-slate-600">
                                        <button onclick="updateQty(-1)" class="px-3 py-2 hover:bg-slate-700 text-gray-400 hover:text-white transition">-</button>
                                        <input type="number" id="qty" value="1" min="1" max="<?php echo $stock; ?>" class="w-full bg-transparent text-center text-white focus:outline-none" onchange="checkMax(this, <?php echo $stock; ?>)">
                                        <button onclick="updateQty(1)" class="px-3 py-2 hover:bg-slate-700 text-gray-400 hover:text-white transition">+</button>
                                    </div>
                                </div>
                                <button onclick="buyItem(<?php echo $product->id; ?>)" class="w-2/3 bg-theme-main hover:bg-purple-600 text-white font-bold py-3 rounded-lg shadow-lg shadow-purple-500/30 transition transform hover:-translate-y-1 flex justify-center items-center gap-2 text-lg mt-5">
                                    <i class="fa-solid fa-cart-shopping"></i> 
                                    <?php echo $product->is_gacha ? 'สุ่มรางวัลเลย' : 'สั่งซื้อทันที'; ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="block text-center w-full bg-slate-600 hover:bg-slate-500 text-white font-bold py-3 rounded-lg transition">
                                <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบเพื่อสั่งซื้อ
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <button disabled class="w-full bg-slate-700 text-gray-500 font-bold py-3 rounded-lg cursor-not-allowed">
                        สินค้าหมดชั่วคราว
                    </button>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
    // ฟังก์ชันเปลี่ยนรูปภาพหลัก
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
    }

    function updateQty(change) {
        let qtyInput = document.getElementById('qty');
        let currentQty = parseInt(qtyInput.value);
        let maxQty = parseInt(qtyInput.getAttribute('max'));
        
        let newQty = currentQty + change;
        if (newQty >= 1 && newQty <= maxQty) {
            qtyInput.value = newQty;
        }
    }

    function checkMax(input, max) {
        if (input.value > max) input.value = max;
        if (input.value < 1) input.value = 1;
    }

    function buyItem(productId) {
        let qty = document.getElementById('qty').value;
        
        Swal.fire({
            title: 'ยืนยันการสั่งซื้อ?',
            text: `คุณต้องการซื้อสินค้านี้จำนวน ${qty} ชิ้น หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8b5cf6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, สั่งซื้อเลย',
            cancelButtonText: 'ยกเลิก',
            background: '#1e293b',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังทำรายการ...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() },
                    background: '#1e293b', color: '#fff'
                });

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('qty', qty);

                fetch('api/buy.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สั่งซื้อสำเร็จ!',
                            html: data.message,
                            background: '#1e293b', color: '#fff'
                        }).then(() => {
                            window.location = 'history.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: data.message,
                            background: '#1e293b', color: '#fff'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        background: '#1e293b', color: '#fff'
                    });
                });
            }
        });
    }
</script>

<?php require_once 'footer.php'; ?>