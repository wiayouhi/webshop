<?php include 'db.php'; ?>
<?php
checkLogin(); // บังคับล็อกอินก่อน
require_once 'header.php';
?>

<div class="max-w-3xl mx-auto py-12 px-4">
    
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-white mb-2 drop-shadow-lg">เติมเงินเข้าระบบ</h1>
        <p class="text-gray-400">ระบบอัตโนมัติ 24 ชั่วโมง ผ่าน TrueMoney Wallet (ซองของขวัญ)</p>
    </div>

    <div class="glass p-8 rounded-2xl border border-slate-700 relative shadow-2xl">
        
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <img src="https://images.seeklogo.com/logo-png/36/1/truemoney-wallet-logo-png_seeklogo-367826.png" class="w-16">
            </div>
            <h2 class="text-2xl font-bold text-white">เติมเงินด้วยซองของขวัญ</h2>
            
            <div class="mt-4 bg-orange-500/10 border border-orange-500/20 rounded-lg p-3 inline-block">
                <p class="text-orange-400 text-sm">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    วิธีสร้าง: เลือก <b>"แบ่งจำนวนเงินเท่ากัน"</b> > กรอกคนรับ <b>"1 คน"</b>
                </p>
            </div>
        </div>

        <div class="max-w-md mx-auto space-y-4">
            <div>
                <label class="block text-gray-300 mb-2 text-sm font-medium">วางลิงก์ซองของขวัญที่นี่</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                        <i class="fa-solid fa-link"></i>
                    </div>
                    <input type="text" id="angpao_link" 
                           class="w-full bg-slate-900 border border-slate-600 rounded-xl py-3.5 pl-10 pr-4 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:outline-none text-white transition-all placeholder-gray-600" 
                           placeholder="https://gift.truemoney.com/campaign/...">
                </div>
            </div>
            
            <button onclick="submitTopup()" class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-300 transform hover:-translate-y-1 active:scale-95">
                <i class="fa-regular fa-paper-plane mr-2"></i> ยืนยันการเติมเงิน
            </button>
        </div>

    </div>
</div>

<script>
    function submitTopup() {
        // ดึงค่าลิงก์จากช่อง input
        let link = document.getElementById('angpao_link').value;
        
        // เช็คว่าว่างไหม
        if(!link) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูล',
                text: 'กรุณาวางลิงก์ซองของขวัญก่อนกดยืนยันครับ',
                background: '#1e293b', color: '#fff',
                confirmButtonColor: '#f97316'
            });
            return;
        }

        // แสดง Loading
        Swal.fire({
            title: 'กำลังตรวจสอบ...',
            text: 'กรุณารอสักครู่ ระบบกำลังเช็คยอดเงิน',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() },
            background: '#1e293b', color: '#fff'
        });

        // เตรียมข้อมูลส่งไปหลังบ้าน
        const formData = new FormData();
        formData.append('link', link);

        // ยิงไปที่ไฟล์ api/topup_angpao.php (ไฟล์เดิมของคุณ)
        fetch('api/topup_angpao.php', { 
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                // สำเร็จ
                Swal.fire({
                    icon: 'success',
                    title: 'เติมเงินสำเร็จ!',
                    text: `ได้รับเงินจำนวน ${data.amount} บาท`,
                    background: '#1e293b', color: '#fff',
                    confirmButtonColor: '#22c55e'
                }).then(() => {
                    window.location.reload(); // รีโหลดหน้าเพื่ออัปเดตยอดเงิน
                });
            } else {
                // ไม่สำเร็จ (เช่น ลิงก์ผิด, ลิงก์ใช้ไปแล้ว)
                Swal.fire({
                    icon: 'error',
                    title: 'เติมเงินไม่สำเร็จ',
                    text: data.message,
                    background: '#1e293b', color: '#fff',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        });
    }
</script>

<?php require_once 'footer.php'; ?>