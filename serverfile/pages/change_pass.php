<?php


if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}


session_start();


if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
}


// เชื่อมต่อฐานข้อมูล
include_once dirname(__FILE__) . "/../database/db.php";


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];


    // ตรวจสอบว่า admin_id มีค่าใน session หรือไม่
    if (!isset($_SESSION['admin_id'])) {
        echo "<script>alert('admin_id = null กรุณากรอกใหม่อีกครั้ง'); window.location.href = '?page=change_pass';</script>";
        exit;
    }




    // Get the admin's current password from the database
    $admin_id = $_SESSION['admin_id'];
    $query = "SELECT password FROM admin WHERE adminID = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo "<script>alert('เกิดข้อผิดพลาดขณะเตรียมคำสั่งฐานข้อมูล');</script>";
        exit;
    }


    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();


    // Verify the current password
    // Verify the current password
    $current_password_hashed = hash('sha256', $current_password);
    if ($current_password_hashed !== $db_password) {
        echo "<script>alert('รหัสผ่านปัจจุบันไม่ถูกต้อง');</script>";
    } else if ($new_password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');</script>";
    } else {
        // Hash the new password with SHA-256
        $new_password_hashed = hash('sha256', $new_password);


        // Update the password in the database
        $update_query = "UPDATE admin SET password = ? WHERE adminID = ?";
        $stmt = $conn->prepare($update_query);
        if ($stmt === false) {
            echo "<script>alert('เกิดข้อผิดพลาดขณะเตรียมคำสั่งฐานข้อมูล');</script>";
            exit;
        }


        $stmt->bind_param("si", $new_password_hashed, $admin_id);
        if ($stmt->execute()) {
            echo "<script>alert('เปลี่ยนรหัสผ่านสำเร็จ'); window.location.href = '?page=profile';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดขณะเปลี่ยนรหัสผ่าน');</script>";
        }
        $stmt->close();
    }
}


include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";


?>


<div class="container mt-2">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="p-4">
                <h2 class="mb-4">เปลี่ยนรหัสผ่าน</h2>
                <form method="POST" action="" onsubmit="return validateForm();">
                    <div class="mb-3 position-relative">
                        <label for="currentPassword" class="form-label fs-4">รหัสผ่านปัจจุบัน</label>
                        <div class="input-group">
                            <input type="password" class="form-control fs-5" id="currentPassword" name="current_password" placeholder="Password1234" required>
                            <span class="input-group-text" onclick="togglePassword('currentPassword', this)">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        <p class="text-start mt-1 mb-1 fs-5"><a class="link-opacity-50-hover" href="?page=forgot2_pass">ลืมรหัสผ่าน</a></p>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="newPassword" class="form-label fs-4">รหัสผ่านใหม่</label>
                        <div class="input-group">
                            <input type="password" class="form-control fs-5" id="newPassword" name="new_password" placeholder="Password1234" required>
                            <span class="input-group-text" onclick="togglePassword('newPassword', this)">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="confirmPassword" class="form-label fs-4">ยืนยันรหัสผ่านใหม่</label>
                        <div class="input-group">
                            <input type="password" class="form-control fs-5" id="confirmPassword" name="confirm_password" placeholder="Password1234" required>
                            <span class="input-group-text" onclick="togglePassword('confirmPassword', this)">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                    <!-- <button type="submit" class="btn btn-primary mt-1 fs-5">เปลี่ยนรหัสผ่าน</button>
                    <a href="?page=change_pass" class="btn btn-secondary mt-1 fs-5">ยกเลิก</a> -->
                    <div class="d-flex justify-content-between gap-2">
                        <a href="?page=change_pass" class="btn btn-secondary fs-5"><i class="bi bi-x-circle"></i> ล้าง</a>
                        <button type="submit" class="btn btn-primary fs-5">เปลี่ยนรหัสผ่าน</button>
                    </div>
                </form>
                <p class="mt-2">*รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวอักษรภาษาอังกฤษพิมพ์ใหญ่ พิมพ์เล็ก และตัวเลข อย่างน้อยหนึ่งตัว โดยห้ามมีเครื่องหมายพิเศษ*</p>
            </div>
        </div>
    </div>
</div>


<script>
    function togglePassword(inputId, element) {
        const input = document.getElementById(inputId);
        const icon = element.querySelector('i');


        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            input.type = "password";
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    }


    // Function to validate passwords
    function validateForm() {
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;


        // Check if new passwords match
        if (newPassword !== confirmPassword) {
            alert('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
            return false; // Prevent form submission
        }


        // Check if current password is provided
        if (currentPassword === "") {
            alert('กรุณากรอกรหัสผ่านปัจจุบัน');
            return false; // Prevent form submission
        }


        // Check if new password meets requirements
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
        if (!passwordPattern.test(newPassword)) {
            alert('รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวอักษรภาษาอังกฤษพิมพ์ใหญ่ พิมพ์เล็ก และตัวเลข อย่างน้อยหนึ่งตัว โดยห้ามมีเครื่องหมายพิเศษ');
            return false; // Prevent form submission
        }


        return true; // Allow form submission if all checks pass
    }
</script>


<?php
include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";
?>

