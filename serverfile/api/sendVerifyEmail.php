<?php
// รวมไฟล์ PHPMailer
include_once dirname(__FILE__) . '/../phpmailer/src/PHPMailer.php';
include_once dirname(__FILE__) . '/../phpmailer/src/SMTP.php';
include_once dirname(__FILE__) . '/../phpmailer/src/Exception.php';
session_start();
$can_access = true;
include_once dirname(__FILE__) . "/../database/db.php";
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $adminID = $_POST['adminID'];
    $new_email = $_POST['new_email'];
    //$confirm_email = $_POST['confirm_email'];
    // Check if the new email already exists
    $checkEmailQuery = "SELECT * FROM admin WHERE email = ? AND adminID != ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("si", $new_email, $adminID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('This email is already in use.'); window.location.href = '/index.php';</script>";
    } else {
        //Set Token
        $token = createToken($new_email);
        //db set token
        // Update email
        //changeEmailDT = NOW()
        $sql = "UPDATE admin SET email = ?, changeEmailDT = NOW(), token = ? WHERE adminID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $new_email, $token, $adminID);



        if ($stmt->execute()) {
            // ตรวจสอบข้อมูลพื้นฐาน
            if (empty($name) || empty($new_email)) {
                echo 'ข้อมูลไม่ครบถ้วน.';
                exit;
            }
            //Create url Verify
            $urlApiVerify = "https://www.mesb.in.th/web/verify/verify-email.php?token=";
            $urlVerify = $urlApiVerify . $token; // URL ที่จะใช้สำหรับการยืนยัน

            sentMail($name, $new_email, $urlVerify);
        } else {
            $error_message = "An error occurred: " . $stmt->error;
            echo "<script>alert('$error_message'); window.location.href = '/index.php';</script>";
        }

        $stmt->close();
    }
}
function createToken($email)
{
    // แฮชข้อมูลโดยใช้ SHA256
    $hashedData = hash('sha256', $email);
    // ตัดผลลัพธ์ให้เหลือ 20 ตัวอักษร
    $token = substr($hashedData, 0, 20);
    return $token;
}

function sentMail($name, $email, $urlVerify)
{
    $_SESSION['email'] = $email;
    $_SESSION['change_email_dt'] = "have";
    if (empty($name) || empty($email) || empty($urlVerify)) {
        echo 'ข้อมูลไม่ครบถ้วน.';
        exit;
    }

    $subject = "ES Admin - Verify Your New Email Address";
    $body = "
        <html>
        <head>
            <title>ES Admin - Verify Your New Email Address</title>
        </head>
        <body>
            <p>Dear $name,</p>
            <p>You have requested to change the email address associated with your ES Admin account.</p> 
            <p>Please click the link below to verify your new email address:</p>
            <p><strong><a href='$urlVerify'>Verify New Email</a></strong></p>
            <p>If you did not request this change, please ignore this email.</p>
            <br>
            <p>Best regards,</p>
            <p>The ES Admin Team</p>
        </body>
        </html>
    ";
    $altBody = "Dear $name,\n\nYou have requested to change the email address associated with your ES Admin account. Please visit the following link to verify your new email address: $urlVerify\n\nIf you did not request this change, please ignore this email.\n\nBest regards,\nThe ES Admin Team";

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
        echo "<script>alert('อีเมลถูกส่งเรียบร้อยแล้ว! กรุณายืนยันอีเมลใหม่ที่อีเมลที่ถูกส่งไป'); window.location.href = '/index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('อีเมลไม่สามารถส่งได้. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}
