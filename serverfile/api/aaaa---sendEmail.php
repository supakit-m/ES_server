<?php
// รวมไฟล์ PHPMailer
include_once dirname(__FILE__) . '/../phpmailer/src/PHPMailer.php';
include_once dirname(__FILE__) . '/../phpmailer/src/SMTP.php';
include_once dirname(__FILE__) . '/../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $altBody = $_POST['altBody'];

    // ตรวจสอบข้อมูลพื้นฐาน
    if (empty($name) || empty($email) || empty($subject) || empty($body) || empty($altBody)) {
        echo 'ข้อมูลไม่ครบถ้วน.';
        exit;
    }

    // สร้างอ็อบเจ็กต์ PHPMailer
    $mail = new PHPMailer(true);

    try {
        // ตั้งค่าเซิร์ฟเวอร์ SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // เปลี่ยนเป็นเซิร์ฟเวอร์ SMTP ของคุณ
        $mail->SMTPAuth = true;
        $mail->Username = 'mesb.web.contact@gmail.com'; // ที่อยู่อีเมลของผู้ส่ง                -----------------------------------
        $mail->Password = 'nzkjuhmcugxhptzi'; // รหัสผ่านอีเมลของผู้ส่ง(gmail app password)   -----------------------------------
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // ตั้งค่าอีเมล
        $mail->setFrom('mesb.web.contact@gmail.com', 'mesbAdmin');
        $mail->addAddress($email, $name); // ที่อยู่อีเมลของผู้รับ
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;

        // ส่งอีเมล
        $mail->send();
        #echo 'อีเมลถูกส่งเรียบร้อยแล้ว!';
    } catch (Exception $e) {
        echo "อีเมลไม่สามารถส่งได้. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo 'ข้อมูลที่ส่งมาไม่ถูกต้อง.';
}
?>
