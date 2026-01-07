<?php include 'db.php'; ?>
<?php
require_once 'header.php';
checkLogin();

// --- Configuration ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// --- SQL Preparation ---
$sql = "SELECT * FROM orders WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($filter_status != 'all') {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

// Count Total
$countStmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $sql));
$countStmt->execute($params);
$total_items = $countStmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch Data
$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// --- Helper Function: Get Status Badge ---
function getStatusBadge($status) {
    $config = [
        'success' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20', 'icon' => 'fa-check-circle', 'label' => 'เสร็จสิ้น'],
        'cancelled' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/20', 'icon' => 'fa-circle-xmark', 'label' => 'ยกเลิก'],
        'pending' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'icon' => 'fa-clock', 'label' => 'รอตรวจสอบ']
    ];
    return $config[$status] ?? $config['pending'];
}
?>

<div class="container mx-auto py-8 px-4 md:px-6 min-h-screen">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                <span class="bg-gradient-to-tr from-theme-main to-purple-600 p-2 rounded-xl shadow-lg shadow-purple-500/20">
                    <i class="fa-solid fa-clock-rotate-left text-xl text-white"></i>
                </span>
                ประวัติการสั่งซื้อ
            </h1>
            <p class="text-slate-400 text-sm pl-1">รายการทั้งหมด <span class="text-theme-main font-bold"><?php echo number_format($total_items); ?></span> รายการ</p>
        </div>

        <form method="GET" class="w-full md:w-auto">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <select name="status" onchange="this.form.submit()" class="w-full md:w-48 bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl focus:ring-theme-main focus:border-theme-main block pl-10 p-2.5 cursor-pointer hover:bg-slate-750 transition">
                    <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                    <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>รอตรวจสอบ</option>
                    <option value="success" <?php echo $filter_status == 'success' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                    <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                </select>
            </div>
        </form>
    </div>

    <?php if(count($orders) > 0): ?>
        
        <div class="grid grid-cols-1 gap-4 md:hidden mb-6">
            <?php foreach($orders as $order): $st = getStatusBadge($order->status); ?>
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4 shadow-lg">
                <div class="flex justify-between items-start mb-3 pb-3 border-b border-slate-700/50">
                    <div>
                        <span class="text-xs text-slate-500 uppercase font-bold">Order ID</span>
                        <div class="font-mono text-white text-lg font-bold">#<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="<?php echo "{$st['bg']} {$st['text']} {$st['border']}"; ?> px-2.5 py-1 rounded-lg text-xs font-bold border flex items-center gap-1.5">
                        <i class="fa-solid <?php echo $st['icon']; ?>"></i> <?php echo $st['label']; ?>
                    </div>
                </div>

                <div class="mb-3 space-y-1">
                    <div class="text-white font-bold text-lg leading-tight"><?php echo htmlspecialchars($order->product_name); ?></div>
                    <div class="text-slate-400 text-sm flex items-center gap-2">
                        <i class="fa-regular fa-calendar-check text-slate-600"></i>
                        <?php echo date('d M Y, H:i', strtotime($order->purchased_at)); ?> น.
                    </div>
                    <?php if($order->note): ?>
                        <div class="text-xs text-slate-400 bg-slate-900/50 inline-block px-2 py-1 rounded mt-1 border border-slate-700/50">
                            Note: <?php echo htmlspecialchars($order->note); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-3 pt-2">
                     <?php if($order->data_received && $order->data_received != '-'): ?>
                    <div class="bg-slate-900 rounded-lg p-3 border border-slate-700/80 relative group">
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">ข้อมูลสินค้า:</div>
                        <div class="font-mono text-sm text-green-400 break-all pr-8" id="m-code-<?php echo $order->id; ?>">
                            <?php echo htmlspecialchars($order->data_received); ?>
                        </div>
                        <button onclick="copyToClipboard('m-code-<?php echo $order->id; ?>')" class="absolute top-2 right-2 p-1.5 text-slate-500 hover:text-white bg-slate-800 hover:bg-theme-main rounded transition">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="flex justify-between items-end mt-1">
                        <div class="text-slate-500 text-xs">ราคารวม</div>
                        <div class="text-theme-main text-2xl font-bold">฿<?php echo number_format($order->price, 2); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="hidden md:block bg-slate-800/40 backdrop-blur rounded-2xl overflow-hidden border border-slate-700 shadow-xl mb-6">
            <table class="w-full text-left">
                <thead class="bg-slate-900/50 text-slate-400 uppercase text-xs font-bold tracking-wider border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-5">Order Detail</th>
                        <th class="px-6 py-5">สินค้า / ข้อมูลที่ได้รับ</th>
                        <th class="px-6 py-5 text-right">ราคา</th>
                        <th class="px-6 py-5 text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    <?php foreach($orders as $order): $st = getStatusBadge($order->status); ?>
                    <tr class="hover:bg-slate-700/20 transition duration-200 group">
                        <td class="px-6 py-5 align-top w-48">
                            <div class="font-mono text-white font-bold text-lg group-hover:text-theme-main transition">#<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <i class="fa-regular fa-clock"></i>
                                <?php echo date('d/m/y H:i', strtotime($order->purchased_at)); ?>
                            </div>
                        </td>
                        
                        <td class="px-6 py-5 align-top">
                            <div class="font-bold text-white text-base mb-2"><?php echo htmlspecialchars($order->product_name); ?></div>
                            
                            <?php if($order->note): ?>
                            <div class="text-xs text-slate-400 mb-2 flex items-center gap-1">
                                <i class="fa-solid fa-note-sticky text-slate-600"></i> <?php echo htmlspecialchars($order->note); ?>
                            </div>
                            <?php endif; ?>

                            <?php if($order->data_received && $order->data_received != '-'): ?>
                            <div class="relative max-w-md">
                                <div class="bg-slate-900/80 border border-slate-700 rounded-lg py-2 px-3 font-mono text-sm text-green-400 break-all shadow-inner pl-9">
                                    <div class="absolute left-3 top-2.5 text-slate-600 select-none"><i class="fa-solid fa-key"></i></div>
                                    <span id="d-code-<?php echo $order->id; ?>"><?php echo htmlspecialchars($order->data_received); ?></span>
                                </div>
                                <button onclick="copyToClipboard('d-code-<?php echo $order->id; ?>')" class="absolute right-1 top-1 bottom-1 px-3 text-slate-500 hover:text-white hover:bg-slate-700 rounded transition" title="Copy">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>

                        <td class="px-6 py-5 align-top text-right">
                            <span class="text-white font-bold text-lg">฿<?php echo number_format($order->price, 2); ?></span>
                        </td>

                        <td class="px-6 py-5 align-top text-center w-40">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border <?php echo "{$st['bg']} {$st['text']} {$st['border']}"; ?>">
                                <i class="fa-solid <?php echo $st['icon']; ?>"></i> <?php echo $st['label']; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="flex flex-col items-center justify-center py-20 bg-slate-800/30 rounded-2xl border border-slate-700 border-dashed">
            <div class="bg-slate-800 p-4 rounded-full mb-4 shadow-lg">
                <i class="fa-solid fa-box-open text-4xl text-slate-600"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-1">ไม่พบข้อมูลคำสั่งซื้อ</h3>
            <p class="text-slate-400 text-sm">ยังไม่มีรายการสั่งซื้อในช่วงเวลานี้ หรือตามเงื่อนไขที่เลือก</p>
            <?php if($filter_status != 'all'): ?>
                <a href="?status=all" class="mt-4 text-theme-main hover:underline text-sm font-bold">ล้างตัวกรอง</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($total_pages > 1): ?>
    <div class="flex justify-center mt-8">
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm bg-slate-800/50 backdrop-blur p-1 border border-slate-700">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>" 
                   class="relative inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md transition-all duration-200 <?php echo ($i == $page) ? 'z-10 bg-theme-main text-white shadow-lg shadow-purple-500/30 scale-105' : 'text-slate-400 hover:bg-slate-700 hover:text-white'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
    <?php endif; ?>

</div>

<script>
// ฟังก์ชัน Copy ที่ปรับปรุงแล้ว รองรับ SweetAlert2
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).innerText;
    
    // หาปุ่มที่กดเพื่อทำ Effect
    const btn = document.querySelector(`button[onclick="copyToClipboard('${elementId}')"]`);
    const icon = btn.querySelector('i');
    
    navigator.clipboard.writeText(text).then(() => {
        // เปลี่ยน Icon เป็น Check
        if(icon) {
            const originalClass = icon.className;
            icon.className = "fa-solid fa-check text-green-400";
            setTimeout(() => { icon.className = originalClass; }, 2000);
        }

        // แสดง Swal notification (ถ้ามี Library)
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                background: '#1e293b', // slate-800
                color: '#fff',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'success',
                title: 'คัดลอกข้อมูลแล้ว'
            });
        }
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>

<?php require_once 'footer.php'; ?>
