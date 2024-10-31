<?php

if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}

include_once dirname(__FILE__) . '/../phpmailer/src/PHPMailer.php';
include_once dirname(__FILE__) . '/../phpmailer/src/SMTP.php';
include_once dirname(__FILE__) . '/../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
}

// Include header and navigation menu
include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include db.php for database connection
    include_once dirname(__FILE__) . "/../database/db.php";;

    // Retrieve form data
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs (you should add more specific validation as needed)
    if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        echo "Please fill out all fields.";
        exit;
    }

    if ($password != $confirm_password) {
        echo "Passwords don't match";
        exit;
    }
    $checkEmailQuery = "SELECT * FROM admin WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('This email is already in use.');</script>";
    } else {
        // Hash password for security
        $hashed_password = hash('sha256', $password);
        $token = createToken($email);

        // Prepare and execute SQL query to insert new admin
        $sql = "INSERT INTO admin (email, username, password, token) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $username, $hashed_password, $token);

        if ($stmt->execute()) {
            $urlApiVerify = "https://www.mesb.in.th/web/verify/verify-email.php?token=";
            $urlVerify = $urlApiVerify . $token; // URL ที่จะใช้สำหรับการยืนยัน
            sentMail($username, $email, $urlVerify);
            echo "<script>alert('Add Admin Account Success');</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        // Close statement and database connection
        $stmt->close();
        $conn->close();
    }
}

function sentMail($name, $email, $urlVerify)
{
    $_SESSION['email'] = $email;
    //$_SESSION['change_email_dt'] = "have";
    if (empty($name) || empty($email) || empty($urlVerify)) {
        echo 'ข้อมูลไม่ครบถ้วน.';
    }

    $subject = "ES Admin - Verify Your Email Address";
    $body = "
    <html>
    <head>
        <title>ES Admin - Verify Your Email Address</title>
    </head>
    <body>
        <p>Dear $name,</p>
        <p>You have created an account with ES Admin.</p> 
        <p>Please click the link below to verify your email address and activate your account:</p>
        <p><strong><a href='$urlVerify'>Verify Email</a></strong></p>
        <p>If you did not create an account with ES Admin, please ignore this email.</p>
        <br>
        <p>Best regards,</p>
        <p>The ES Admin Team</p>
    </body>
    </html>
";
    $altBody = "Dear $name,\n\nYou have created an account with ES Admin. Please visit the following link to verify your email address and activate your account: $urlVerify\n\nIf you did not create an account with ES Admin, please ignore this email.\n\nBest regards,\nThe ES Admin Team";

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
        $mail->setFrom('mesb.web.contact@gmail.com', 'ES admin');
        $mail->addAddress($email, $name); // ที่อยู่อีเมลของผู้รับ
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;

        // ส่งอีเมล
        $mail->send();
        echo "<script>alert('อีเมลถูกส่งเรียบร้อยแล้ว!');</script>";
    } catch (Exception $e) {
        echo "<script>alert('อีเมลไม่สามารถส่งได้. Mailer Error: {$mail->ErrorInfo}');</script>";
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
?>

<style>
    .position-relative {
        position: relative;
    }

    #togglePasswordIcon1,
    #togglePasswordIcon2 {
        cursor: pointer;
        position: absolute;
        top: 76%;
        right: 10px;
        transform: translateY(-50%);
    }
</style>

<div class="container mt-2 w-50 ">
    <h2 class="mb-4">กรอกข้อมูลบัญชีผู้ดูแลระบบที่ต้องการเพิ่ม</h2>
    <form method="POST" action="" onsubmit="return validateForm();">
        <div class="mb-3">
            <label for="email" class="form-label fs-4">อีเมล</label>
            <input type="email" class="form-control fs-5" id="email" name="email" placeholder="aaa@mail.com" required>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label fs-4">ชื่อผู้ใช้</label>
            <input type="text" class="form-control fs-5" id="username" name="username" placeholder="admin" required>
        </div>
        <div class="mb-3 position-relative">
            <label for="password" class="form-label fs-4">รหัสผ่าน</label>
            <input type="password" class="form-control fs-5" id="password" name="password" placeholder="Password1234" minlength="8" required>
            <i class="bi bi-eye-slash position-absolute end-0 translate-middle-y me-3" id="togglePasswordIcon1" style="cursor: pointer;"></i>
        </div>
        <div class="mb-4 position-relative">
            <label for="confirm_password" class="form-label fs-4">ยืนยันรหัสผ่าน</label>
            <input type="password" class="form-control fs-5" id="confirm_password" name="confirm_password" placeholder="Password1234" minlength="8" required>
            <i class="bi bi-eye-slash position-absolute end-0 translate-middle-y me-3" id="togglePasswordIcon2" style="cursor: pointer;"></i>
        </div>
        <div class="d-flex justify-content-between gap-2">
            <a href="?page=add_admin" class="btn btn-secondary fs-5"><i class="bi bi-x-circle"></i> ล้าง</a>
            <button type="submit" class="btn btn-primary fs-5">เพิ่มบัญชีผู้ดูแลระบบ</button>
        </div>
        <p class="mt-2 text-muted fs-5 text-center">*เจ้าของบัญชีต้องยืนยันอีเมลก่อน บัญชีจึงจะสามารถใช้งานได้*</p>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var password_input = document.getElementById('password');
        var toggle1_password_icon = document.getElementById('togglePasswordIcon1');
        var confirm_password_input = document.getElementById('confirm_password');
        var toggle2_password_icon = document.getElementById('togglePasswordIcon2');

        toggle1_password_icon.addEventListener('click', function() {
            // Toggle the password visibility
            const type = password_input.getAttribute('type') === 'password' ? 'text' : 'password';
            password_input.setAttribute('type', type);

            // Toggle the icon
            toggle1_password_icon.classList.toggle('bi-eye');
            toggle1_password_icon.classList.toggle('bi-eye-slash');
        });
        toggle2_password_icon.addEventListener('click', function() {
            // Toggle the password visibility
            const type = confirm_password_input.getAttribute('type') === 'password' ? 'text' : 'password';
            confirm_password_input.setAttribute('type', type);

            // Toggle the icon
            toggle2_password_icon.classList.toggle('bi-eye');
            toggle2_password_icon.classList.toggle('bi-eye-slash');
        });
    });

    function validateForm() {
        const Password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Check if new passwords match
        if (Password !== confirmPassword) {
            alert('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
            return false; // Prevent form submission
        }

        // Check if new password meets requirements
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
        if (!passwordPattern.test(Password)) {
            alert('รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวอักษรภาษาอังกฤษพิมพ์ใหญ่ พิมพ์เล็ก และตัวเลข อย่างน้อยหนึ่งตัว โดยห้ามมีเครื่องหมายพิเศษ');
            return false; // Prevent form submission
        }


        return true; // Allow form submission if all checks pass
    }
</script>

<?php
// Include footer and navigation menu end
include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";
?>