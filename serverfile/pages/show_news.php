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

include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";

$sql = "SELECT startDate, endDate, message FROM notification WHERE 1";

$newsList = $conn->query($sql);


?>
<style>
    .newsbg {
        background-color: #00c8e2;
        color: #000000;
    }

    table thead th {
        background-color: #343a40;
        color: #ffffff;
    }

    table tbody td {
        padding: 15px;
        vertical-align: middle;
        word-wrap: break-word;
        /* เพื่อให้ข้อความที่ยาวเกินขึ้นบรรทัดใหม่ */
        white-space: normal;
        /* ปรับให้ข้อความไม่อยู่บรรทัดเดียว */
    }

    .table-bordered td,
    .table-bordered th {
        border: 2px solid #dee2e6;
    }
</style>

<div class="container mt-4">
    <h1 class="text-center mb-4"><b>รายการข่าวสาร</b></h1>
    <div class="table-responsive">
        <table class="table table-bordered table-hover " style="table-layout: fixed;">
            <thead>
                <tr>
                    <th class="text-center bg-dark-subtle fs-4" style="width: 23%;">วันที่เริ่มต้นแสดงข่าวสาร</th>
                    <th class="text-center bg-dark-subtle fs-4" style="width: 23%;">วันที่สิ้นสุดแสดงข่าวสาร</th>
                    <th class="text-center bg-dark-subtle fs-4" style="width: 54%;">เนื้อหาข่าวสาร</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($newsList as $news) : ?>
                    <tr>
                        <td class="text-center fs-5"><?php echo htmlspecialchars($news['startDate']); ?></td>
                        <td class="text-center fs-5"><?php echo htmlspecialchars($news['endDate']); ?></td>
                        <td class="fs-5"><?php echo nl2br(htmlspecialchars($news['message'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";
?>