<?php
//$can_access = true;
if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}
$can_access = $can_access;
$page = "user";
$groupByhas = false;
session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
    exit();
}
include_once dirname(__FILE__) . "/../database/db.php";

// Initialize search parameters
$lastLoginFrom = $_POST['lastLoginFrom'] ?? '';
$lastLoginTo = $_POST['lastLoginTo'] ?? '';
$lastDetectFrom = $_POST['lastDetectFrom'] ?? '';
$lastDetectTo = $_POST['lastDetectTo'] ?? '';
$detectCountFrom = $_POST['detectCountFrom'] ?? '';
$detectCountTo = $_POST['detectCountTo'] ?? '';

// Build SQL query with search parameters
$sql = "SELECT m.name, m.email, DATE(m.lastLoginDT) as lastLoginDate, DATE(m.lastDetectDT) as lastDetectDate, COALESCE(SUM(d.detectAmount), 0) AS detection_count
        FROM member m
        LEFT JOIN dailyreport d ON m.accountID = d.accountID
        WHERE 1=1";
$conditions[] = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $lastLoginFrom = $_POST['lastLoginFrom'];
    $lastLoginTo = $_POST['lastLoginTo'];
    $lastDetectFrom = $_POST['lastDetectFrom'];
    $lastDetectTo = $_POST['lastDetectTo'];
    $detectCountFrom = $_POST['detectCountFrom'];
    $detectCountTo = $_POST['detectCountTo'];

    // Add conditions based on input fields
    if ('' != ($lastLoginFrom)) {
        $conditions[] = "DATE(m.lastLoginDT) >= '" . $conn->real_escape_string($lastLoginFrom) . "'";
    }
    if ('' != ($lastLoginTo)) {
        $conditions[] = "DATE(m.lastLoginDT) <= '" . $conn->real_escape_string($lastLoginTo) . "'";
    }
    if ('' != ($lastDetectFrom)) {
        $conditions[] = "DATE(m.lastDetectDT) >= '" . $conn->real_escape_string($lastDetectFrom) . "'";
    }
    if ('' != ($lastDetectTo)) {
        $conditions[] = "DATE(m.lastDetectDT) <= '" . $conn->real_escape_string($lastDetectTo) . "'";
    }
    // Update SQL query if there are any conditions
    if (count($conditions) > 0) {
        $sql .= "" . implode(" AND ", $conditions);
    }

    $sql .= " GROUP BY m.accountID";
    $groupByhas = true;

    if (!empty($detectCountFrom) || !empty($detectCountTo)) {
        $sql .= " HAVING 1=1";
        if (!empty($detectCountFrom)) {
            $sql .= " AND detection_count >= " . (int)$detectCountFrom;
        }
        if (!empty($detectCountTo)) {
            $sql .= " AND detection_count <= " . (int)$detectCountTo;
        }
    }

}

// Add conditions based on search parameters
#echo "<script>alert($can_acces);</script>";
#if (!empty($lastLoginFrom)) {
#    $conditions[] = "DATE(m.lastLoginDT) >= '" . $conn->real_escape_string($lastLoginFrom) . "'";
#}
#if (!empty($lastLoginTo)) {
#    $conditions[] = "DATE(m.lastLoginDT) <= '" . $conn->real_escape_string($lastLoginTo) . "'";
#}
#if (!empty($lastDetectFrom)) {
#    $conditions[] = "DATE(m.lastDetectDT) >= '" . $conn->real_escape_string($lastDetectFrom) . "'";
#}
#if (!empty($lastDetectTo)) {
#    $conditions[] = "DATE(m.lastDetectDT) <= '" . $conn->real_escape_string($lastDetectTo) . "'";
#}
#
#if (count($conditions) > 0) {
#    $sql .= "" . implode(" AND ", $conditions);
#}

if($groupByhas == false){
    $sql .= " GROUP BY m.accountID";
}


#
// Execute the query and check for errors

$result = $conn->query($sql);


if (!$result) {
    die("Query error: " . $conn->error);
}

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";
?>
<style>
    .userbg {
        background-color: #00c8e2;
        color: #000000;
    }

    .form-group {
        display: flex;
        align-items: center;
    }

    .form-group label {
        flex: 1;
        margin-right: 0.1rem;
    }

    .form-group input {
        flex: 2;
    }
</style>

<div class="mt-0">
    <form method="POST" action="">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header fs-4"><b>วันที่ใช้งานแอปล่าสุด</b></div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="lastLoginFrom" class="form-label fs-5">ตั้งแต่:</label>
                            <input class="form-control" type="date" id="lastLoginFrom" name="lastLoginFrom" value="<?php echo htmlspecialchars($lastLoginFrom); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="lastLoginTo" class="form-label fs-5">ถึง:</label>
                            <input class="form-control" type="date" id="lastLoginTo" name="lastLoginTo" value="<?php echo htmlspecialchars($lastLoginTo); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header fs-4"><b>วันที่ใช้ตรวจจับล่าสุด</b></div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="lastDetectFrom" class="form-label fs-5">ตั้งแต่:</label>
                            <input class="form-control" type="date" id="lastDetectFrom" name="lastDetectFrom" value="<?php echo htmlspecialchars($lastDetectFrom); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="lastDetectTo" class="form-label fs-5">ถึง:</label>
                            <input class="form-control" type="date" id="lastDetectTo" name="lastDetectTo" value="<?php echo htmlspecialchars($lastDetectTo); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header fs-4"><b>จำนวนรายการตรวจจับที่มีในระบบ</b></div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="detectCountFrom" class="form-label fs-5">ตั้งแต่:</label>
                            <input class="form-control" type="number" id="detectCountFrom" name="detectCountFrom" value="<?php echo htmlspecialchars($detectCountFrom); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="detectCountTo" class="form-label fs-5">ถึง:</label>
                            <input class="form-control" type="number" id="detectCountTo" name="detectCountTo" value="<?php echo htmlspecialchars($detectCountTo); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="d-flex justify-content-between gap-2">
                <a href="?page=user" class="btn btn-secondary w-40 fs-5 p-2 px-4"><i class="bi bi-x-circle"></i> ล้าง</a>
                <button class="btn btn-primary w-40 fs-5 p-2 px-4" type="submit"><i class="bi bi-search"></i>&nbsp;ค้นหา</button>
            </div>
        </div>
    </form>

    <div class="mt-4">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table">
                    <tr>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 20%;">ชื่อ-นามสกุล</th>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 20%;">อีเมล</th>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 20%;">วันที่ใช้งานแอปล่าสุด</th>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 20%;">วันที่ใช้ตรวจจับล่าสุด</th>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 20%;">จำนวนรายการ</th>
                    </tr>
                </thead>
                <tbody class="text-center fs-5">
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['lastLoginDate']); ?></td>
                            <td><?php echo htmlspecialchars($user['lastDetectDate']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($user['detection_count'])); ?></td>
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