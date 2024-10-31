<?php

if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}

session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
    exit;
}

include_once dirname(__FILE__) . "/../database/db.php";

// Retrieve current email
$current_email = $_SESSION['email'];
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $name = $_SESSION["username"];
}

include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";

?>

<div class="container mt-2">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="p-4">
                <div class="mb-4"><h2>เปลี่ยนที่อยู่อีเมลใหม่</h2></div>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="/web/api/sendVerifyEmail.php" onsubmit="return validateForm()">
                    <div class="mb-4 mt-4">
                        <label for="current_email" class="form-label fs-4">อีเมลปัจจุบัน :</label>
                        <input type="email" id="current_email" class="form-control fs-5" value="<?php echo htmlspecialchars($current_email); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="new_email" class="form-label fs-4">อีเมลใหม่ :</label>
                        <input type="email" id="new_email" name="new_email" class="form-control fs-5" placeholder="aaa@mail.com" required>
                    </div>
                    <input type='hidden' name='adminID' id='adminID' value="<?php echo htmlspecialchars($admin_id); ?>">
                    <input type='hidden' name='name' id='name' value="<?php echo htmlspecialchars($name); ?>">
                    <div class="d-flex justify-content-between gap-2">
                        <a href="?page=change_email" class="btn btn-secondary fs-5"><i class="bi bi-x-circle"></i> ล้าง</a>
                        <button type="submit" class="btn btn-primary fs-5">ยืนยันการเปลี่ยนที่อยู่อีเมล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>   
</div>


<script>
function validateForm() {
    const newEmail = document.getElementById('new_email').value;
    //const confirmEmail = document.getElementById('confirm_email').value;

    if (newEmail === "") {// || confirmEmail === ""
        alert("กรุณากรอกอีเมลใหม่");
        return false;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(newEmail)) {
        alert("รูปแบบอีเมลใหม่ไม่ถูกต้อง");
        return false;
    }
    return true; // form is valid
}
</script>

<?php

include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";

?>
