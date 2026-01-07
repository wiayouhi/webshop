<?php include '../db.php'; ?>
<?php
require_once 'api_auth.php';
// api/topup_slip.php
// เวอร์ชัน: Full Security (ป้องกัน Bypass/Spam/Fake Slip)

header('Content-Type: application/json; charset=utf-8');
require_once '../db.php'; 

// 1. ตรวจสอบล็อกอิน
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

// ---------------------------------------------------------------------
// 🛡️ SECURITY LEVEL 1: ตรวจสอบไฟล์อัปโหลด (ป้องกัน Shell/Script)
// ---------------------------------------------------------------------
if (!isset($_FILES['slip_image']) || $_FILES['slip_image']['error'] != 0) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาอัปโหลดไฟล์รูปภาพสลิป']);
    exit;
}

$file_tmp = $_FILES['slip_image']['tmp_name'];
$file_type = $_FILES['slip_image']['type'];
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

// เช็ค Mime Type
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['status' => 'error', 'message' => 'อนุญาตเฉพาะไฟล์รูปภาพ (JPG, PNG) เท่านั้น']);
    exit;
}

// เช็คว่าเป็นรูปภาพจริงๆ หรือไม่ (ป้องกันการเปลี่ยนนามสกุลไฟล์ php มาเนียน)
if (!getimagesize($file_tmp)) {
    echo json_encode(['status' => 'error', 'message' => 'ไฟล์ที่อัปโหลดไม่ใช่รูปภาพที่ถูกต้อง']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ---------------------------------------------------------------------
// 2. ดึง Config และ API Key
// ---------------------------------------------------------------------
$stmt = $pdo->prepare("SELECT payment_bank_acc, slip_api_token FROM settings LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$api_token = $config->slip_api_token ?? '';
// เตรียมเลขบัญชีเราไว้เทียบ (ตัดขีด ตัดวรรคออก เพื่อความแม่นยำ)
$my_acc_no = str_replace(['-', ' '], '', $config->payment_bank_acc ?? ''); 

if (empty($api_token)) {
    echo json_encode(['status' => 'error', 'message' => 'ระบบยังไม่ได้ตั้งค่า API Token (ติดต่อแอดมิน)']);
    exit;
}

// ---------------------------------------------------------------------
// 3. ส่งรูปไปตรวจสอบกับ API (SlipOK / EasySlip)
// ---------------------------------------------------------------------
// ** อย่าลืมแก้ URL ตรงนี้ให้ตรงกับเจ้าที่ใช้ **
$api_url = "https://api.slipok.com/api/line/verification"; 

$cfile = new CURLFile($file_tmp, $file_type, $_FILES['slip_image']['name']);
$data = ['files' => $cfile]; 

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "x-authorization: " . $api_token // ถ้าใช้ EasySlip อาจต้องเปลี่ยนเป็น Authorization: Bearer ...
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

// ---------------------------------------------------------------------
// 4. วิเคราะห์ผลลัพธ์จาก API
// ---------------------------------------------------------------------
if ($http_code == 200 && isset($result['success']) && $result['success'] === true) {
    
    $slip = $result['data'];
    $amount = floatval($slip['amount']);
    $trans_ref = $slip['transRef']; // เลขอ้างอิงธุรกรรม (สำคัญมากใช้กันซ้ำ)
    
    // -----------------------------------------------------------------
    // 🛡️ SECURITY LEVEL 2: ตรวจสอบบัญชีปลายทาง (ป้องกันเอาสลิปคนอื่นมาใช้)
    // -----------------------------------------------------------------
    if (!empty($my_acc_no)) {
        // ดึงเลขบัญชีผู้รับจากสลิป (Structure นี้อิงตาม SlipOK)
        // ถ้าใช้เจ้าอื่นลอง var_dump($slip) ดูโครงสร้างก่อนครับ
        $receiver_acc = str_replace(['-', ' '], '', $slip['receiver']['account']['bank']['account'] ?? '');
        
        // เช็คว่าเลขบัญชีในสลิป มีส่วนที่ตรงกับเลขบัญชีเราไหม
        if (strpos($receiver_acc, $my_acc_no) === false && strpos($my_acc_no, $receiver_acc) === false) {
            // กรณีไม่ตรง ลองเช็ค Proxy (เผื่อเป็น PromptPay เบอร์โทร)
            $proxy_acc = str_replace(['-', ' '], '', $slip['receiver']['proxy']['account'] ?? '');
            
            if (strpos($proxy_acc, $my_acc_no) === false) {
                // ถ้าไม่ตรงทั้งเลขบัญชี และ เบอร์ PromptPay -> ดีดออก
                echo json_encode(['status' => 'error', 'message' => 'สลิปนี้ไม่ได้โอนเข้าบัญชีของทางเว็บ (ตรวจสอบบัญชีปลายทาง)']);
                exit; 
            }
        }
    }

    // -----------------------------------------------------------------
    // 🛡️ SECURITY LEVEL 3: ป้องกันสลิปซ้ำ & ยิงรัว (Race Condition)
    // -----------------------------------------------------------------
    
    try {
        $pdo->beginTransaction();

        // ขั้นตอนที่ 1: บันทึก Log ก่อน (ถ้า reference_code ซ้ำ Database จะ Error ทันที เพราะเราทำ Unique Index ไว้)
        // หมายเหตุ: ต้องรัน SQL: ALTER TABLE topups ADD UNIQUE INDEX idx_ref_code (reference_code); ก่อนนะ
        $stmt = $pdo->prepare("INSERT INTO topups (user_id, amount, method, reference_code, status) VALUES (?, ?, 'qr_payment', ?, 'success')");
        $stmt->execute([$user_id, $amount, $trans_ref]);

        // ขั้นตอนที่ 2: เพิ่ม Point ให้ User
        $stmt = $pdo->prepare("UPDATE users SET point = point + ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);

        // ขั้นตอนที่ 3: อัปเดต Session
        if(isset($_SESSION['point'])) {
            $_SESSION['point'] += $amount;
        }

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'amount' => $amount,
            'message' => 'เติมเงินสำเร็จ'
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        
        // เช็ค Error Code: 23000 คือ Duplicate Entry (ข้อมูลซ้ำ)
        if ($e->getCode() == '23000') {
            echo json_encode(['status' => 'error', 'message' => 'สลิปนี้ถูกใช้งานไปแล้ว (ห้ามทำรายการซ้ำ)']);
        } else {
            // Error อื่นๆ เช่น Database ล่ม
            error_log($e->getMessage()); // เก็บ Log ไว้ดูเองหลังบ้าน
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล (Database Error)']);
        }
    }

} else {
    // -----------------------------------------------------------------
    // กรณี API ตอบกลับว่าไม่ผ่าน
    // -----------------------------------------------------------------
    $msg = $result['message'] ?? 'ไม่สามารถตรวจสอบสลิปได้ (รูปไม่ชัด หรือ สลิปปลอม)';
    
    // เช็คกรณีโควต้าหมด
    if ($http_code == 400 || $http_code == 402) {
        $msg = "ระบบเช็คสลิปขัดข้องชั่วคราว (Quota Limit Exceeded)";
    }

    echo json_encode(['status' => 'error', 'message' => $msg]);
}
?>