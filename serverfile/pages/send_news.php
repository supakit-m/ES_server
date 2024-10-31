<?php


session_start();

if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
    exit();
}

include_once dirname(__FILE__) . "/../database/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    #header('Content-Type: application/json; charset=UTF-8');

    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $message = trim($_POST['message']);

    // Validate input data
    $startDateValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate);
    $endDateValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate);

    if ($startDateValid && $endDateValid && !empty($message)) {
        if (new DateTime($startDate) > new DateTime($endDate)) {
            $response = ['status' => 'error', 'message' => 'Start Date must be before End Date'];
            echo json_encode($response);
            exit();
        }

        $sql = "INSERT INTO `notification` (`startDate`, `endDate`, `message`) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        $stmt->bind_param('sss', $startDate, $endDate, $message);

        if ($stmt->execute()) {
            sendNewNoti($conn);
            $response = ['status' => 'success'];
            echo "<script>alert('Success.');</script>";
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to insert data'];
            echo "<script>alert('Failed to insert data. Please try again.');</script>";
        }

        $stmt->close();
    }
    $conn->close();
}


function sendNewNoti($conn)
{
    // สร้างคำสั่ง SQL สำหรับการอัปเดต
    $sql = "UPDATE `member` SET `newNotification` = 1 WHERE 1=1"; // ใช้ 1 แทน TRUE
    $stmt = $conn->prepare($sql);

    // Execute คำสั่ง
    if ($stmt->execute()) {
        echo "<script>alert('New Notification sent successfully.');</script>";
    } else {
        echo "<script>alert('Failed to send New Notification.');</script>";
    }

    // ปิด statement
    $stmt->close();
}

include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";
?>

<style>
    /* CSS เดิมคงไว้ */
    .senbg {
        background-color: #00c8e2;
        color: #000000;
    }

    .news-form {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 20px;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .form-group {
        flex: 1;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
    }

    .form-group label {
        margin-bottom: 0;
    }
</style>

<script>
    function validateAndConfirm() {
        var sdate = document.getElementById("startDate").value;
        var edate = document.getElementById("endDate").value;

        if (sdate > edate) {
            alert("โปรดเลือกช่วงวันที่ให้ถูกต้อง (วันที่เริ่มต้น ต้องน้อยกว่าหรือเท่ากับ วันที่สิ้นสุด)");
            return false;
        }

        return confirm("คุณต้องการส่งข่าวสารในช่วงวันที่ " + sdate + " ถึง " + edate + " ใช่หรือไม่?");
    }
</script>

<div class="news-form container bg-transparent border border-0">
    <h1 class="text-center mb-1 fs-1"><b>ข่าวสาร</b></h1>
    <form action="" method="post" onsubmit="return validateAndConfirm()">
        <div class="form-group mb-3">
            <label class="fs-4 mb-3" for="message">ข้อความ:</label>
            <textarea class="form-control fs-4" id="message" name="message" rows="5" maxlength="200" placeholder="กรุณาใส่ข่าวสาร ที่นี่..." required></textarea>
            <p class="text-danger fs-5 mt-1">*ใส่ข่าวสารได้ไม่เกิน 200 ตัวอักษร*</p>
        </div>
        <div class="form-row mb-3">
            <div class="form-group">
                <label class="fs-4" for="startDate">ตั้งแต่:</label>
                <input type="date" class="form-control fs-5" id="startDate" name="startDate" placeholder="Select start date" required>
            </div>
            <div class="form-group">
                <label class="fs-4" for="endDate">ถึง:</label>
                <input type="date" class="form-control fs-5" id="endDate" name="endDate" placeholder="Select end date" required>
            </div>
        </div>
        <div class="d-flex justify-content-between gap-2">
            <a href="?page=send_news" class="btn btn-secondary fs-5 p-2 px-4"><i class="bi bi-x-circle"></i> ล้าง</a>
            <button type="submit" class="btn btn-primary fs-5 p-2 px-4">ส่งข่าวสาร</button>
        </div>
    </form>
</div>


<?php

include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";

?>