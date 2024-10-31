<?php
// เปิดใช้งานไฟล์ db.php เพื่อเชื่อมต่อฐานข้อมูล
$can_access = true;
include_once dirname(__FILE__) . "/../database/db.php"; // เชื่อมต่อกับฐานข้อมูล

// รับ token จาก URL
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (!isset($token) || $token == '') {
    die(header("HTTP/1.1 404 Not Found"));
}
session_start();
$_SESSION['verify_access'] = true;
$_SESSION['change_email_dt'] = null;


if ($token) {
    // Query ตรวจสอบ token 
    $stmt = $conn->prepare('SELECT * FROM admin WHERE token = ? LIMIT 1');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();


    if ($user) {
        // ถ้า token ยังไม่หมดอายุ อัปเดตการยืนยันอีเมล
        $updateStmt = $conn->prepare('UPDATE admin SET usable = 1, changeEmailDT = NULL, token = NULL  WHERE token = ?');
        $updateStmt->bind_param('s', $token);
        $updateStmt->execute();


        // เปลี่ยนเส้นทางไปยังหน้าสำเร็จ
        header("Location: /web/verify/verify-success.php");
        exit();
    } else {
        // ถ้า token ไม่ถูกต้องหรือหมดอายุ
        header("Location: /web/verify/verify-failed.php");
        exit();
    }
} else {
    // ถ้าไม่มี token ใน URL
    header("Location: /web/verify/verify-failed.php");
    exit();
}
// http://localhost/web/verify/verify-email.php?token=tokenTest
