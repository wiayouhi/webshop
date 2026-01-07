<?php require_once 'header.php'; ?>

<div class="container mx-auto py-16 px-4">
    <div class="max-w-md mx-auto glass p-8 rounded-2xl border border-slate-700 shadow-2xl relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-transparent via-theme-main to-transparent opacity-50"></div>
        
        <h1 class="text-3xl font-bold text-center mb-2 text-glow">
            <i class="fa-solid fa-gift text-theme-main"></i> แลกรับรางวัล
        </h1>
        <p class="text-gray-400 text-center mb-8 text-sm">กรอกโค้ดกิจกรรมเพื่อรับเครดิตฟรี</p>

        <form id="redeemForm" class="space-y-6">
            <div>
                <label class="block text-gray-400 mb-2 text-sm">รหัสโค้ด (Gift Code)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-gray-500"><i class="fa-solid fa-ticket"></i></span>
                    <input type="text" id="code" class="w-full bg-slate-900/80 border border-slate-600 rounded-xl py-3 pl-10 pr-4 text-white focus:border-theme-main focus:ring-1 focus:ring-theme-main focus:outline-none transition placeholder-gray-600 font-mono text-center tracking-widest uppercase" placeholder="กรอกโค้ดที่นี่">
                </div>
            </div>

            <button type="submit" class="w-full bg-theme-main hover:bg-purple-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-purple-500/20 transition transform hover:-translate-y-1">
                <i class="fa-solid fa-check-circle mr-2"></i> ยืนยันการแลกโค้ด
            </button>
        </form>
        
    </div>
</div>

<script>
document.getElementById('redeemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let code = document.getElementById('code').value;
    
    Swal.fire({
        title: 'กำลังตรวจสอบ...',
        didOpen: () => Swal.showLoading(),
        background: '#1e293b', color: '#fff',
        allowOutsideClick: false
    });

    const formData = new FormData();
    formData.append('code', code);

    fetch('api/redeem.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                background: '#1e293b', color: '#fff'
            }).then(() => {
                location.reload(); // รีโหลดเพื่ออัปเดตยอดเงิน
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ผิดพลาด',
                text: data.message,
                background: '#1e293b', color: '#fff'
            });
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>