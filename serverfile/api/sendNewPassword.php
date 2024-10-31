<?php
// รวมไฟล์ PHPMailer
include_once dirname(__FILE__) . '/../phpmailer/src/PHPMailer.php';
include_once dirname(__FILE__) . '/../phpmailer/src/SMTP.php';
include_once dirname(__FILE__) . '/../phpmailer/src/Exception.php';

$can_access = true;
include_once dirname(__FILE__) . "/../database/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $email = $_POST['email'];

    // เช็คอีเมลในฐานข้อมูล
    $query = "SELECT username, email FROM admin WHERE email = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo "<script>alert('เกิดข้อผิดพลาดขณะเตรียมคำสั่งฐานข้อมูล');</script>";
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // ดึงค่ามาใส่ $name และ $email
    $stmt->bind_result($name, $email);
    $stmt->fetch();
    $stmt->close();

    // หากเจออีเมลในฐานข้อมูล
    if ($email) {
        // สร้างรหัสผ่านใหม่ 8 ตัวอักษร
        $newPassword = generatePassword(8);

        // แปลงรหัสผ่านเป็น SHA256
        $hashedPassword = hash('sha256', $newPassword);

        // อัปเดตรหัสผ่านในฐานข้อมูล
        $updateQuery = "UPDATE admin SET password = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateQuery);
        if ($updateStmt === false) {
            echo "<script>alert('เกิดข้อผิดพลาดขณะเตรียมการอัปเดตรหัสผ่าน');</script>";
            exit;
        }
        $updateStmt->bind_param("ss", $hashedPassword, $email);
        $updateStmt->execute();
        $updateStmt->close();

        // เรียกฟังก์ชันส่งอีเมลพร้อมรหัสผ่านใหม่
        sentMail($name, $email, $newPassword);
    } else {
        // หากไม่พบอีเมลในฐานข้อมูล
        echo "<script>alert('ไม่พบอีเมลนี้ในระบบ');</script>";
    }
} else {
    echo 'ข้อมูลที่ส่งมาไม่ถูกต้อง.';
}

function generatePassword($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function sentMail($name, $email, $newPassword)
{

    if (empty($name) || empty($email) || empty($newPassword)) {
        echo 'ข้อมูลไม่ครบถ้วน.';
        exit;
    }


    $subject = "ES Admin - Your New Password";
    $body = "
    <html>
    <head>
        <title>ES Admin - Your New Password</title>
    </head>
    <body>
        <p>Dear $name,</p>
        <p>We have received a request to reset your password for your account at ES Admin.</p>
        <p>Your new password is: <strong>$newPassword</strong></p>
        <p>Please log in using your new password and remember to change it after your first login.</p>
        <p>If you did not request a password reset, please ignore this email.</p>
        <br>
        <p>Best regards,</p>
        <p>The ES Admin Team</p>
    </body>
    </html>
    ";
    $altBody = "Dear $name,\n\nWe have received a request to reset your password for your account at ES Admin.\nYour new password is: $newPassword\nPlease log in using your new password and remember to change it after your first login.\nIf you did not request a password reset, please ignore this email.\n\nBest regards,\nThe ES Admin Team";

    // ตรวจสอบข้อมูลพื้นฐาน


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
        $mail->setFrom('mesb.web.contact@gmail.com', 'ES Admin');
        $mail->addAddress($email, $name); // ที่อยู่อีเมลของผู้รับ
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;

        // ส่งอีเมล
        $mail->send();
        echo "<script>alert('อีเมลถูกส่งเรียบร้อยแล้ว!'); window.location.href = '/index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('อีเมลไม่สามารถส่งได้. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}
