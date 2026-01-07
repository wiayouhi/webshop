<?php include 'db.php'; ?>
<?php
require_once 'header.php';

// ดึงข้อมูลเกมและแพ็กเกจจาก Database
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY id ASC");
$servicesData = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // ดึงแพ็กเกจของเกมนี้
    $pkgStmt = $pdo->prepare("SELECT id, name, price FROM service_packages WHERE service_id = ? ORDER BY price ASC");
    $pkgStmt->execute([$row['id']]);
    $packages = $pkgStmt->fetchAll(PDO::FETCH_ASSOC);

    // เก็บใส่ Array เตรียมส่งให้ JS
    $servicesData[$row['id']] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'img' => $row['image'],
        'type' => $row['input_type'],
        'desc' => $row['description'], // <--- (ใหม่) ดึง Description มาด้วย
        'packages' => $packages
    ];
}
?>

<div class="container mx-auto py-10 px-4">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-glow mb-2">บริการเติมเกม</h1>
        <p class="text-gray-400">เติมไว ปลอดภัย 100% ด้วยระบบอัตโนมัติ (โดยทีมงาน)</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach ($servicesData as $gameId => $game): ?>
        <div onclick="openServiceModal(<?php echo $gameId; ?>)" class="glass p-4 rounded-xl border border-slate-700 hover:border-theme-main cursor-pointer hover:transform hover:-translate-y-2 transition group">
            <img src="<?php echo htmlspecialchars($game['img']); ?>" class="w-full h-40 object-cover rounded-lg mb-4 group-hover:opacity-80 transition">
            <h3 class="font-bold text-lg text-center text-white"><?php echo htmlspecialchars($game['name']); ?></h3>
            <p class="text-center text-xs text-theme-main mt-1">คลิกเพื่อเลือกแพ็กเกจ</p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="serviceModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="glass max-w-md w-full p-6 rounded-2xl border border-slate-600 relative animate-fade-in-up">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
        
        <h3 id="modalTitle" class="text-xl font-bold mb-4 text-theme-main"></h3>
        <img id="modalImg" src="" class="w-20 h-20 rounded-lg mx-auto mb-4 object-cover">

        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-2">เลือกแพ็กเกจ</label>
            <select id="packageSelect" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
            </select>
        </div>

        <div id="inputArea" class="mb-6 space-y-3">
        </div>

        <button onclick="submitService()" class="w-full bg-theme-main hover:bg-purple-600 text-white py-3 rounded-lg font-bold shadow-lg shadow-purple-500/30">
            ยืนยันการสั่งซื้อ
        </button>
    </div>
</div>

<script>
    const gamesData = <?php echo json_encode($servicesData); ?>;
    let selectedGameId = null;

    function openServiceModal(id) {
        selectedGameId = id;
        const game = gamesData[id];
        
        document.getElementById('modalTitle').innerText = 'เติมเกม: ' + game.name;
        document.getElementById('modalImg').src = game.img;
        
        // --- (ใหม่) สร้าง HTML สำหรับ Description ---
        let descHtml = '';
        if (game.desc && game.desc !== '') {
            descHtml = `
                <div class="bg-blue-900/30 border border-blue-500/50 p-3 rounded-lg mb-4 text-sm text-blue-200">
                    <i class="fa-solid fa-circle-info mr-1"></i> ${game.desc}
                </div>
            `;
        }

        // Dropdown แพ็กเกจ
        let select = document.getElementById('packageSelect');
        select.innerHTML = '';
        game.packages.forEach((pkg, index) => {
            select.innerHTML += `<option value="${index}">${pkg.name} - ฿${pkg.price}</option>`;
        });

        // Input Area + Description
        let inputArea = document.getElementById('inputArea');
        inputArea.innerHTML = descHtml; // ใส่คำอธิบายก่อนช่องกรอก
        
        if (game.type === 'uid' || game.type === 'uid_zone') {
            inputArea.innerHTML += `
                <div>
                    <label class="text-xs text-gray-400">UID / OpenID</label>
                    <input type="text" id="input1" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" placeholder="กรอกเลข UID...">
                </div>`;
            
            if(game.type === 'uid_zone'){
                 inputArea.innerHTML += `
                <div class="mt-2">
                    <label class="text-xs text-gray-400">Zone / Server</label>
                    <input type="text" id="input2" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" placeholder="เช่น Asia, TH...">
                </div>`;
            }

        } else if (game.type === 'id_pass') {
            inputArea.innerHTML += `
                <div>
                    <label class="text-xs text-gray-400">Username / ID</label>
                    <input type="text" id="input1" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" placeholder="ไอดีล็อกอิน...">
                </div>
                <div class="mt-2">
                    <label class="text-xs text-gray-400">Password</label>
                    <input type="text" id="input2" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" placeholder="รหัสผ่าน...">
                </div>
                <p class="text-xs text-red-400 mt-1">*กรุณาเปลี่ยนรหัสผ่านชั่วคราวเพื่อความปลอดภัย</p>`;
        }

        document.getElementById('serviceModal').classList.remove('hidden');
        document.getElementById('serviceModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('serviceModal').classList.add('hidden');
        document.getElementById('serviceModal').classList.remove('flex');
    }

    // --- (ใหม่) ฟังก์ชัน Submit แบบมีรายละเอียดครบ ---
    function submitService() {
        if (!selectedGameId) return;
        const game = gamesData[selectedGameId];
        const pkgIndex = document.getElementById('packageSelect').value;
        const pkg = game.packages[pkgIndex]; 
        
        let note = '';
        let userDetailsHtml = ''; 

        // ตรวจสอบข้อมูลตามประเภท
        if (game.type === 'uid') {
            const uid = document.getElementById('input1').value;
            if(!uid) return Swal.fire('แจ้งเตือน', 'กรุณากรอก UID', 'warning');
            note = `UID: ${uid}`;
            userDetailsHtml = `<span class="text-yellow-400">${uid}</span>`;

        } else if (game.type === 'uid_zone') {
            const uid = document.getElementById('input1').value;
            const zone = document.getElementById('input2').value;
            if(!uid || !zone) return Swal.fire('แจ้งเตือน', 'กรุณากรอก UID และ Zone', 'warning');
            note = `UID: ${uid} | Zone: ${zone}`;
            userDetailsHtml = `UID: <span class="text-yellow-400">${uid}</span> <br> Zone: <span class="text-yellow-400">${zone}</span>`;

        } else if (game.type === 'id_pass') {
            const id = document.getElementById('input1').value;
            const pass = document.getElementById('input2').value;
            if(!id || !pass) return Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลให้ครบ', 'warning');
            note = `ID: ${id} | Pass: ${pass}`;
            userDetailsHtml = `
                <div class="text-left text-sm mt-2 space-y-1">
                    <div>ID: <span class="text-yellow-400">${id}</span></div>
                    <div>Pass: <span class="text-yellow-400">${pass}</span></div>
                </div>`;
        }

        // แสดง Alert ยืนยัน
        Swal.fire({
            title: '<h3 class="text-2xl font-bold text-theme-main">ตรวจสอบข้อมูล</h3>',
            html: `
                <div class="bg-slate-800 p-4 rounded-lg border border-slate-600 text-gray-300 text-sm">
                    <div class="flex justify-between mb-2 border-b border-slate-600 pb-2">
                        <span>บริการ:</span>
                        <span class="text-white font-bold">${game.name}</span>
                    </div>
                    <div class="flex justify-between mb-2 border-b border-slate-600 pb-2">
                        <span>แพ็กเกจ:</span>
                        <span class="text-purple-400 font-bold">${pkg.name}</span>
                    </div>
                    <div class="flex justify-between mb-4">
                        <span>ราคา:</span>
                        <span class="text-green-400 font-bold text-lg">฿${pkg.price}</span>
                    </div>
                    
                    <div class="bg-slate-900 p-3 rounded text-center">
                        <p class="text-xs text-gray-500 mb-1">ข้อมูลที่จะใช้เติม</p>
                        <div class="font-mono text-base break-words">
                            ${userDetailsHtml}
                        </div>
                    </div>
                    <p class="text-xs text-red-400 mt-3 text-center">*กรุณาตรวจสอบข้อมูล หากผิดพลาดจะไม่สามารถแก้ไขได้</p>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> ยืนยันชำระเงิน',
            confirmButtonColor: '#10b981', 
            cancelButtonText: 'แก้ไข',
            cancelButtonColor: '#64748b', 
            background: '#1e293b', 
            color: '#fff',
            customClass: { popup: 'rounded-2xl border border-slate-700' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังทำรายการ...',
                    text: 'กรุณารอสักครู่',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() },
                    background: '#1e293b', color: '#fff'
                });
                
                let formData = new FormData();
                formData.append('package_id', pkg.id);
                formData.append('note', note);

                fetch('api/buy_service.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire({
                            icon: 'success', 
                            title: 'สั่งซื้อสำเร็จ!',
                            text: 'ระบบได้รับรายการแล้ว กรุณารอแอดมินดำเนินการ',
                            background: '#1e293b', color: '#fff',
                            confirmButtonColor: '#8b5cf6'
                        }).then(() => location.href='history.php');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: data.message,
                            background: '#1e293b', color: '#fff'
                        });
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                });
            }
        });
    }
</script>

<?php require_once 'footer.php'; ?>