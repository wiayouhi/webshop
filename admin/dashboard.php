<?php include 'admin_auth.php'; ?>

<?php
require_once 'header.php';


// --- 1. จัดการ Logic วันที่และโหมด (Time & Mode Handler) ---
$mode = $_GET['mode'] ?? 'week'; // ค่าเริ่มต้น: week
$date_input = $_GET['date'] ?? date('Y-m-d'); // วันที่อ้างอิง

$timestamp = strtotime($date_input);
$chart_labels = [];
$chart_data = [];
$title_chart = "";

// คำนวณช่วงเวลาและเตรียม SQL
switch ($mode) {
    case 'day':
        $title_chart = "ยอดขายรายชั่วโมง (วันที่ " . date('d/m/Y', $timestamp) . ")";
        for ($i = 0; $i < 24; $i++) {
            $chart_labels[] = sprintf("%02d:00", $i);
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE DATE(purchased_at) = ? AND HOUR(purchased_at) = ?");
            $stmt->execute([$date_input, $i]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;

    case 'week':
        $start_date = date('Y-m-d', strtotime('-6 days', $timestamp));
        $end_date = date('Y-m-d', $timestamp);
        $period_text = date('d/m', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
        $title_chart = "ยอดขาย 7 วัน ($period_text)";

        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days", $timestamp));
            $chart_labels[] = date('D d', strtotime($d));
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE DATE(purchased_at) = ?");
            $stmt->execute([$d]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;

    case 'month':
        $title_chart = "ยอดขายรายวัน ประจำเดือน " . date('m/Y', $timestamp);
        $month = date('m', $timestamp);
        $year = date('Y', $timestamp);
        $days_in_month = date('t', $timestamp);

        for ($d = 1; $d <= $days_in_month; $d++) {
            $chart_labels[] = $d;
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE YEAR(purchased_at) = ? AND MONTH(purchased_at) = ? AND DAY(purchased_at) = ?");
            $stmt->execute([$year, $month, $d]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;

    case 'year':
        $title_chart = "ยอดขายรายเดือน ประจำปี " . date('Y', $timestamp);
        $year = date('Y', $timestamp);
        $months_short = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        for ($m = 1; $m <= 12; $m++) {
            $chart_labels[] = $months_short[$m-1];
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE YEAR(purchased_at) = ? AND MONTH(purchased_at) = ?");
            $stmt->execute([$year, $m]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;
}

// 2. ข้อมูล Stats รวม (All-Time)
$stats = [
    'income' => $pdo->query("SELECT SUM(price) FROM orders")->fetchColumn() ?: 0,
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'sold_items' => $pdo->query("SELECT COUNT(*) FROM stocks WHERE is_sold = 1")->fetchColumn(),
];
?>

<div class="mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-4">
        <div>
            <h2 class="text-3xl font-bold mb-2">Dashboard</h2>
            <p class="text-gray-400">ระบบจัดการและดูสถิติร้านค้า</p>
        </div>
        
        <div class="bg-slate-800 p-2 rounded-xl flex flex-col sm:flex-row gap-3 items-center shadow-lg border border-slate-700">
            
            <div class="flex bg-slate-900/50 rounded-lg p-1">
                <?php foreach(['day'=>'วัน', 'week'=>'สัปดาห์', 'month'=>'เดือน', 'year'=>'ปี'] as $m => $label): ?>
                <a href="?mode=<?php echo $m; ?>&date=<?php echo $date_input; ?>" 
                   class="px-3 py-1.5 text-sm rounded-md transition whitespace-nowrap <?php echo $mode==$m ? 'bg-theme-main text-white shadow-md' : 'text-gray-400 hover:text-white'; ?>">
                   <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="h-6 w-px bg-slate-600 hidden sm:block"></div>

            <form action="" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                
                <?php 
                    $prev_link = "";
                    if($mode == 'day') $prev_link = date('Y-m-d', strtotime('-1 day', $timestamp));
                    elseif($mode == 'week') $prev_link = date('Y-m-d', strtotime('-1 week', $timestamp));
                    elseif($mode == 'month') $prev_link = date('Y-m-d', strtotime('-1 month', $timestamp));
                    elseif($mode == 'year') $prev_link = date('Y-m-d', strtotime('-1 year', $timestamp));
                ?>
                <a href="?mode=<?php echo $mode; ?>&date=<?php echo $prev_link; ?>" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-700 hover:bg-slate-600 text-white transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>

                <div class="relative group">
                    <?php if($mode == 'year'): ?>
                        <select name="date" onchange="this.form.submit()" class="bg-slate-900 text-white border border-slate-600 rounded-lg px-3 py-1.5 text-center focus:outline-none focus:border-theme-main appearance-none cursor-pointer hover:bg-slate-800 transition min-w-[100px]">
                            <?php 
                            $curr_year = date('Y');
                            for($y = $curr_year; $y >= $curr_year - 5; $y--): // ย้อนหลัง 5 ปี
                            ?>
                                <option value="<?php echo $y; ?>-01-01" <?php echo date('Y', $timestamp) == $y ? 'selected' : ''; ?>>
                                    ปี <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                    <?php elseif($mode == 'month'): ?>
                        <input type="month" name="date" 
                               value="<?php echo date('Y-m', $timestamp); ?>" 
                               onchange="this.form.submit()"
                               class="bg-slate-900 text-white border border-slate-600 rounded-lg px-3 py-1.5 text-center focus:outline-none focus:border-theme-main cursor-pointer hover:bg-slate-800 transition">

                    <?php else: ?>
                        <input type="date" name="date" 
                               value="<?php echo date('Y-m-d', $timestamp); ?>" 
                               onchange="this.form.submit()"
                               class="bg-slate-900 text-white border border-slate-600 rounded-lg px-3 py-1.5 text-center focus:outline-none focus:border-theme-main cursor-pointer hover:bg-slate-800 transition">
                    <?php endif; ?>
                </div>

                <?php 
                    $next_link = "";
                    if($mode == 'day') $next_link = date('Y-m-d', strtotime('+1 day', $timestamp));
                    elseif($mode == 'week') $next_link = date('Y-m-d', strtotime('+1 week', $timestamp));
                    elseif($mode == 'month') $next_link = date('Y-m-d', strtotime('+1 month', $timestamp));
                    elseif($mode == 'year') $next_link = date('Y-m-d', strtotime('+1 year', $timestamp));
                ?>
                <a href="?mode=<?php echo $mode; ?>&date=<?php echo $next_link; ?>" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-700 hover:bg-slate-600 text-white transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="glass p-6 rounded-2xl border-l-4 border-green-500 flex items-center justify-between hover:translate-y-[-5px] transition duration-300">
        <div>
            <p class="text-gray-400 text-sm">รายได้รวมทั้งหมด</p>
            <h3 class="text-2xl font-bold text-white">฿ <?php echo number_format($stats['income'], 2); ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-500 text-xl">
            <i class="fa-solid fa-sack-dollar"></i>
        </div>
    </div>
    <div class="glass p-6 rounded-2xl border-l-4 border-blue-500 flex items-center justify-between hover:translate-y-[-5px] transition duration-300">
        <div>
            <p class="text-gray-400 text-sm">สมาชิก</p>
            <h3 class="text-2xl font-bold text-white"><?php echo number_format($stats['users']); ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500 text-xl">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
    <div class="glass p-6 rounded-2xl border-l-4 border-purple-500 flex items-center justify-between hover:translate-y-[-5px] transition duration-300">
        <div>
            <p class="text-gray-400 text-sm">ออเดอร์</p>
            <h3 class="text-2xl font-bold text-white"><?php echo number_format($stats['orders']); ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-500 text-xl">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
    </div>
    <div class="glass p-6 rounded-2xl border-l-4 border-orange-500 flex items-center justify-between hover:translate-y-[-5px] transition duration-300">
        <div>
            <p class="text-gray-400 text-sm">ขายแล้ว</p>
            <h3 class="text-2xl font-bold text-white"><?php echo number_format($stats['sold_items']); ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-500 text-xl">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2 glass p-6 rounded-2xl border border-slate-700 flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-theme-main"></i> <?php echo $title_chart; ?>
            </h3>
        </div>
        <div class="relative w-full h-80">
            <canvas id="mainChart"></canvas>
        </div>
    </div>

    <div class="glass p-6 rounded-2xl border border-slate-700 flex flex-col h-[450px]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-500"></i> ล่าสุด
            </h3>
            <span class="text-xs bg-slate-800 text-gray-400 px-2 py-1 rounded-md">50 รายการ</span>
        </div>
        
        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <div class="flex flex-col gap-3">
                <?php
                $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 50")->fetchAll();
                if(count($orders) > 0):
                    foreach($orders as $o):
                ?>
                <div class="flex items-center justify-between p-3 bg-slate-800/40 rounded-xl hover:bg-slate-800 transition border-l-2 border-transparent hover:border-theme-main group">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="bg-slate-700/50 p-2 rounded-lg text-gray-400 group-hover:text-white transition shrink-0">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white truncate group-hover:text-theme-main transition"><?php echo htmlspecialchars($o->product_name); ?></p>
                            <p class="text-xs text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($o->purchased_at)); ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="block text-sm font-bold text-green-400">+฿<?php echo number_format($o->price); ?></span>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <div class="h-40 flex items-center justify-center text-gray-500">ไม่มีข้อมูลการสั่งซื้อ</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>

<script>
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    // Gradient Effect
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.5)'); // Theme color fade
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'ยอดขาย',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#8b5cf6', // Theme Main Color
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#1e293b',
                pointBorderColor: '#8b5cf6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#fff',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return ' รายได้: ฿' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#64748b' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)', borderDash: [5, 5] },
                    ticks: { color: '#64748b', callback: function(value){ return '฿' + value; } },
                    border: { display: false }
                }
            }
        }
    });
</script>

<?php 
echo "</div></main></body></html>"; 
?>