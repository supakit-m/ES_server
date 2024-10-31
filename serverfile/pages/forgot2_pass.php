<?php

if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}


$can_access = true;
include_once dirname(__FILE__) . "/../database/db.php";
session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
}




include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";
?>
<head>
    <!-- เพิ่มลิงก์ไปยัง SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<div class="container mt-2">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="p-4">
                <h2 class="mb-5">ขอรหัสผ่านใหม่</h2>
                <div class="card-body">
                    <form id="forgetPass_form" method="POST" action="/web/api/sendNewPassword.php">
                        <div class="form-group mb-4">
                            <label class="fs-4 mb-3" for="email">อีเมลผู้ดูแลระบบ :</label>
                            <input type="email" class="form-control fs-5 p-2" name="email" id="email" placeholder="aaa@mail.com">
                        </div>
                        <div class="d-grid">
                            <button type="submit" id="button_login" class="btn btn-primary mb-2 fs-5">ขอรหัสผ่านใหม่</button>
                            <a href="?page=change_pass" class="btn btn-secondary btn-back fs-5">กลับไปหน้าเปลี่ยนรหัสผ่านใหม่</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";
?>
