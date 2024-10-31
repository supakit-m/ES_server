<?php
if (!isset($can_access) || !$can_access) {
    http_response_code(404);
    exit;
}

session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
}

include_once dirname(__FILE__) . '/../database/db.php';

// Default SQL query to fetch all data
$sql = "SELECT adminID, email, username, usable FROM admin WHERE usable IS NOT NULL AND 1=1";
$conditions[] = "";

// Default values for form fields
$email = "";
$username = "";
$status = "total";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $status = $_POST['status'];

    // Add conditions based on input fields
    if (!empty($email)) {
        $conditions[] = "email LIKE '%" . $conn->real_escape_string($email) . "%'";
    }
    if (!empty($username)) {
        $conditions[] = "username LIKE '%" . $conn->real_escape_string($username) . "%'";
    }
    if ($status !== "total") {
        $usable = ($status == "active") ? 1 : 0;
        $conditions[] = "usable = $usable";
    }

    // Update SQL query if there are any conditions
    if (count($conditions) > 0) {
        $sql .= "" . implode(" AND ", $conditions);
    }
}

$result = $conn->query($sql);

include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";

?>
<style>
    .adminbg {
        background-color: #00c8e2;
        color: #000000;
    }

    .table td .form-check {
        display: flex;
        justify-content: center;
        /* จัดสวิตช์ให้อยู่ตรงกลางแนวนอน */
        align-items: center;
        /* จัดสวิตช์ให้อยู่ตรงกลางแนวตั้ง */
        height: 100%;
        /* ใช้ความสูงของเซลล์ทั้งหมด */
    }
</style>

<script>
    $(document).ready(function() {
        $(".form-check-input").change(function() {
            var adminID = $(this).data("adminid");
            var usable = $(this).is(":checked") ? 1 : 0;


            $.ajax({
                url: "/web/api/update_status.php",
                type: 'POST',
                data: {
                    adminID: adminID,
                    usable: usable
                },
                success: function(response) {
                    console.log(response);
                }
            });
        });
    });
</script>


<div>
    <form method="POST" action="">
        <div class="row mb-4">
            <div class="col-md-4">
                <label for="email" class="form-label fs-4"><b>อีเมล</b></label>
                <input type="email" name="email" id="email" class="form-control fs-5" placeholder="aaa@mail.com" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="col-md-4">
                <label for="username" class="form-label fs-4"><b>ชื่อผู้ใช้</b></label>
                <input type="text" name="username" id="username" class="form-control fs-5" placeholder="admin" value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fs-4"><b>สถานะ</b></label>
                <div class="form-check">
                    <input class="form-check-input fs-5" type="radio" id="total" name="status" value="total" <?php echo $status == "total" ? "checked" : ""; ?>>
                    <label class="form-check-label fs-5" for="total">ทั้งหมด</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input fs-5" type="radio" id="active" name="status" value="active" <?php echo $status == "active" ? "checked" : ""; ?>>
                    <label class="form-check-label fs-5" for="active">ใช้งานได้</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input fs-5" type="radio" id="inactive" name="status" value="inactive" <?php echo $status == "inactive" ? "checked" : ""; ?>>
                    <label class="form-check-label fs-5" for="inactive">ใช้งานไม่ได้</label>
                </div>
            </div>
            <div class="col-md-2 text-center align-self-end">
                <button type="submit" class="btn btn-primary mb-3 w-100 fs-5"><i class="bi bi-search"></i>&nbsp;ค้นหา</button>
                <button type="button" class="btn btn-success w-100 fs-5" onclick="window.location.href='?page=add_admin';"><i class="bi bi-person-fill-add"></i>&nbsp;เพิ่มผู้ดูแลระบบ</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table">
                <tr>
                    <th class="text-center bg-dark-subtle fs-4" style="width: 35%;">อีเมล</th>
                    <th class="text-center bg-dark-subtle fs-4" style="width: 35%;">ชื่อผู้ใช้</th>
                    <th class="text-center bg-dark-subtle fs-4" style="width: 30%;">สถานะการใช้งาน</th>
                </tr>
            </thead>
            <tbody class="fs-5">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_checked = $row['usable'] ? "checked" : "";
                        echo "<tr>
                                <td class='text-center'>{$row['email']}</td>
                                <td class='text-center'>{$row['username']}</td>
                                <td class='text-center'>
                                    <div class='form-check form-switch'>
                                        <input class='form-check-input fs-3 border-dark form-check-input-status' type='checkbox' data-adminid='{$row['adminID']}' $status_checked>
                                    </div>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No results found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(".form-check-input-status").change(function() {
            var adminID = $(this).data("adminid");
            var usable = $(this).is(":checked") ? 1 : 0;
            var message = usable ? 'เปิดใช้งานผู้ดูแลระบบเรียบร้อย' : 'ปิดใช้งานผู้ดูแลระบบเรียบร้อย';
            var iconType = usable ? 'success' : 'success'; // แยก icon สำหรับเปิดและปิด

            $.ajax({
                url: "/web/api/update_status.php",
                type: 'POST',
                data: {
                    adminID: adminID,
                    usable: usable
                },
                success: function(response) {
                    Swal.fire({
                        icon: iconType,
                        title: message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to update status',
                        text: 'An error occurred while updating status.',
                    });
                    console.error("Error: " + error);
                }
            });
        });
    });
</script>

<?php

include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";

?>