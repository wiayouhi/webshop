<?php include 'admin_auth.php'; ?>
<?php
// admin/product_form.php
require_once 'header.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
$product = null;
$gallery_images = [];
$unsold_stock = [];

// --- ACTION: ลบรูปภาพแกลเลอรี่ ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_img' && isset($_GET['img_id'])) {
    $img_id = $_GET['img_id'];
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$img_id]);
    
    // Redirect ล้างค่า URL
    echo "<script>window.location='product_form.php?id=$id';</script>";
    exit;
}

// --- ACTION: ลบสต็อกรายชิ้น ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_stock' && isset($_GET['stock_id'])) {
    $stock_id = $_GET['stock_id'];
    $stmt = $pdo->prepare("DELETE FROM stocks WHERE id = ? AND is_sold = 0");
    $stmt->execute([$stock_id]);
    
    echo "<script>window.location='product_form.php?id=$id';</script>";
    exit;
}

// --- QUERY: ดึงข้อมูลสินค้าและสต็อก ---
if ($id) {
    // 1. สินค้า
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    // 2. Gallery
    $stmt_img = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC");
    $stmt_img->execute([$id]);
    $gallery_images = $stmt_img->fetchAll();

    // 3. Stock
    $stmt_stock = $pdo->prepare("SELECT * FROM stocks WHERE product_id = ? AND is_sold = 0 ORDER BY id ASC");
    $stmt_stock->execute([$id]);
    $unsold_stock = $stmt_stock->fetchAll();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// --- LOGIC: บันทึกข้อมูล ---
if (isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $is_gacha = isset($_POST['is_gacha']) ? 1 : 0;
    
    // จัดการรูปปก
    $img_path = trim($_POST['img_url']); 

    try {
        if ($id) {
            // Update สินค้า
            $sql = "UPDATE products SET name=?, category_id=?, price=?, description=?, img=?, is_gacha=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $category_id, $price, $description, $img_path, $is_gacha, $id]);
            $product_id = $id;

            // Update สต็อกเก่า
            if (isset($_POST['edit_stock']) && is_array($_POST['edit_stock'])) {
                $stock_update_stmt = $pdo->prepare("UPDATE stocks SET contents = ? WHERE id = ? AND is_sold = 0");
                foreach ($_POST['edit_stock'] as $sid => $content) {
                    $content = trim($content);
                    if (!empty($content)) {
                        $stock_update_stmt->execute([$content, $sid]);
                    }
                }
            }

        } else {
            // Insert สินค้าใหม่
            $sql = "INSERT INTO products (name, category_id, price, description, img, is_gacha) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $category_id, $price, $description, $img_path, $is_gacha]);
            $product_id = $pdo->lastInsertId();
        }

        // --- ส่วนที่เพิ่มรูป Gallery ---
        if (!empty($_POST['gallery_urls'])) {
            $gallery_sql = "INSERT INTO product_images (product_id, img_path) VALUES (?, ?)";
            $gallery_stmt = $pdo->prepare($gallery_sql);
            
            // แยกบรรทัดและลบช่องว่าง
            $urls = explode("\n", str_replace("\r", "", $_POST['gallery_urls']));
            foreach ($urls as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $gallery_stmt->execute([$product_id, $url]);
                }
            }
        }

        // เพิ่มสต็อกใหม่
        $stock_content = trim($_POST['add_stock']);
        if (!empty($stock_content)) {
            $items = explode("\n", str_replace("\r", "", $stock_content));
            $stock_sql = "INSERT INTO stocks (product_id, contents) VALUES (?, ?)";
            $stock_stmt = $pdo->prepare($stock_sql);
            foreach ($items as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $stock_stmt->execute([$product_id, $item]);
                }
            }
        }

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Saved successfully',
                showConfirmButton: false, timer: 1500
            }).then(() => { window.location='product_form.php?id=$product_id'; });
        </script>";

    } catch (PDOException $e) {
        // แสดง Error ถ้า SQL มีปัญหา (เช่น ลืมสร้างตาราง)
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Database Error',
                text: '" . addslashes($e->getMessage()) . "'
            });
        </script>";
    }
}
?>

<div class="max-w-6xl mx-auto pb-10">
    <div class="flex items-center gap-4 mb-6">
        <a href="products.php" class="bg-slate-800 p-3 rounded-xl hover:bg-slate-700 text-white transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-3xl font-bold"><?php echo $id ? 'Edit Product' : 'Add New Product'; ?></h2>
    </div>

    <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="glass p-6 rounded-2xl border border-slate-700">
                <h3 class="text-xl font-bold mb-4 border-b border-slate-700 pb-2">Product Info</h3>
                
                <div class="mb-4">
                    <label class="block text-gray-400 mb-2">Product Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product->name ?? ''); ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none" required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-400 mb-2">Price</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $product->price ?? ''; ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-gray-400 mb-2">Category</label>
                        <select name="category_id" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none">
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat->id; ?>" <?php echo ($product && $product->category_id == $cat->id) ? 'selected' : ''; ?>>
                                    <?php echo $cat->name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4 bg-slate-800/50 p-4 rounded-xl border border-slate-600">
                    <label class="block text-theme-main font-bold mb-2">Cover Image (URL)</label>
                    <input type="text" name="img_url" value="<?php echo $product->img ?? ''; ?>" placeholder="https://..." class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none" required>
                    <?php if(isset($product->img) && $product->img): ?>
                        <div class="mt-2">
                            <img src="<?php echo $product->img; ?>" class="h-32 rounded-lg border border-slate-600 object-cover">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4 bg-slate-800/50 p-4 rounded-xl border border-slate-600 border-dashed">
                    <label class="block text-theme-main font-bold mb-2">
                        <i class="fa-solid fa-images"></i> Gallery Images
                    </label>
                    
                    <?php if(!empty($gallery_images)): ?>
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <?php foreach($gallery_images as $g_img): ?>
                                <div class="relative group">
                                    <img src="<?php echo $g_img->img_path; ?>" class="w-full h-24 object-cover rounded-lg border border-slate-500">
                                    <a href="product_form.php?id=<?php echo $id; ?>&action=delete_img&img_id=<?php echo $g_img->id; ?>" 
                                       onclick="return confirm('Delete this image?')"
                                       class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg cursor-pointer transition transform hover:scale-110">
                                        <i class="fa-solid fa-times text-xs"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <label class="block text-gray-400 text-sm mb-2">Add Image URLs (One per line)</label>
                    <textarea name="gallery_urls" rows="3" placeholder="https://example.com/pic1.jpg&#10;https://example.com/pic2.jpg" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-2 text-sm text-white font-mono"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-400 mb-2">Description (HTML)</label>
                    <textarea name="description" rows="5" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-theme-main focus:outline-none"><?php echo $product->description ?? ''; ?></textarea>
                </div>
                
                <div class="flex items-center gap-3 bg-slate-800 p-3 rounded-lg border border-slate-700">
                    <input type="checkbox" name="is_gacha" id="is_gacha" class="w-5 h-5 accent-theme-main" <?php echo ($product && $product->is_gacha) ? 'checked' : ''; ?>>
                    <label for="is_gacha" class="cursor-pointer select-none">Is Gacha Product?</label>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            
            <div class="glass p-6 rounded-2xl border border-slate-700">
                <h3 class="text-xl font-bold mb-4 border-b border-slate-700 pb-2 text-green-400">
                    <i class="fa-solid fa-plus-circle"></i> Add New Stock
                </h3>
                <div class="mb-4">
                     <textarea name="add_stock" rows="5" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white font-mono text-sm" placeholder="user:pass"></textarea>
                </div>
            </div>

            <?php if($id): ?>
            <div class="glass p-6 rounded-2xl border border-slate-700 max-h-[500px] overflow-y-auto">
                <h3 class="text-xl font-bold mb-4 border-b border-slate-700 pb-2 text-yellow-400 flex justify-between items-center">
                    <span><i class="fa-solid fa-pen-to-square"></i> Manage Stock</span>
                    <span class="text-xs bg-slate-800 px-2 py-1 rounded text-white"><?php echo count($unsold_stock); ?> items</span>
                </h3>
                
                <?php if(count($unsold_stock) > 0): ?>
                    <div class="space-y-2">
                        <?php foreach($unsold_stock as $stock): ?>
                            <div class="flex gap-2 items-center">
                                <input type="text" 
                                       name="edit_stock[<?php echo $stock->id; ?>]" 
                                       value="<?php echo htmlspecialchars($stock->contents); ?>" 
                                       class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-sm text-gray-300 font-mono focus:text-white focus:border-yellow-500 outline-none">
                                
                                <a href="product_form.php?id=<?php echo $id; ?>&action=delete_stock&stock_id=<?php echo $stock->id; ?>" 
                                   onclick="return confirm('Delete this stock?')"
                                   class="bg-red-500/20 hover:bg-red-500 text-red-500 hover:text-white p-2 rounded transition"
                                   title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-4 text-center">* Edit text and click Save below *</p>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No unsold stock</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <button type="submit" name="save_product" class="w-full bg-theme-main hover:bg-purple-600 text-white font-bold py-4 rounded-xl shadow-lg sticky bottom-4 z-10 text-lg">
                <i class="fa-solid fa-save"></i> Save All Changes
            </button>
        </div>

    </form>
</div>
<?php echo "</div></main></body></html>"; ?>