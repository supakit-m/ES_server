<?php

if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}

session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    header("Location: ?page=login");
    exit();
}

include_once dirname(__FILE__) . "/../database/db.php";

// กำหนดวันที่เริ่มต้นและวันที่สิ้นสุด
$minDate = date('Y-m-d'); // วันที่เก่าที่สุด
$maxDate = date('Y-m-d'); // วันที่ปัจจุบัน

include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";






try { // ฟังก์ชันเพื่อดึงข้อมูลขนาดของเซิร์ฟเวอร์
    $url = 'http://www.mesb.in.th:8000/get_images_size/';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Failed to connect to API: ' . curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);
} catch (Exception $e) {
    $data = null;
}
// Define $size and $unit here
if ($data) {
    $size = $data['size'];
    $unit = $data['unit'];
} else {
    $size = "0";
    $unit = "B";
    echo "<script>alert('Failed to retrieve data.');</script>";
}

try { // ฟังก์ชันเพื่อดึง max min DT ของข้อมูล
    $url = 'http://www.mesb.in.th:8000/get_detectDT_max_min_all/';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Failed to connect to API: ' . curl_error($ch));
    }

    curl_close($ch);

    $DTmaxmin = json_decode($response, true);
} catch (Exception $e) {
    $DTmaxmin = null;
}
if ($DTmaxmin) {
    $DTmax = $DTmaxmin['max'];
    $DTmin = $DTmaxmin['min'];

    $date = "2024-09-21 13:45:00";

    // สร้าง DateTime object
    $dateTimeMax = new DateTime($DTmax);
    $dateTimeMin = new DateTime($DTmin);
} else {
    $DTmax = "dd/mm/yyyy";
    $DTmin = "dd/mm/yyyy";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sdate = $_POST['startDate'];
    $edate = $_POST['endDate'];

    $api_data = json_encode(array("startDate" => $sdate, "endDate" => $edate));

    try {
        $url = 'http://www.mesb.in.th:8000/delete_img_by_date/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $api_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Failed to connect to API: ' . curl_error($ch));
        }

        curl_close($ch);
    } catch (Exception $e) {
        echo "<script>alert('เว็บไม่เจอ API ทำให้ดำเนินการไม่สำเร็จ $sdate - $edate');</script>";
    }

    $delete_img_by_date = json_decode($response, true);

    


    if ($delete_img_by_date === true) {
        #header("Location: /web/pages/delete_data.php");
        #exit();
        echo "<script>alert('ดำเนินการลบสำเร็จ');</script>";
    } else {
        echo "<script>alert('Failed to delete data. Please try again.');</script>";
    }
}

?>

<style>
    .deletebg {
        background-color: #00c8e2;
        color: #000000;
    }

    .disk-info {
        margin-top: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f8f9fa;
    }
</style>

<div class="container mt-1 col-md-7">
    <div class="alert alert-danger text-center card" role="alert">
        <div class="card-header">
            <h3><b>ลบข้อมูลการตรวจจับท่านั่งที่ผิดจากหลักสรีรศาสตร์</b></h3>
        </div>
        <p class="fs-4 mt-2">ขนาดข้อมูลที่มีทั้งหมด</p>
        <h2 class="" style="font-size: 40px;"><b><?php echo $size; ?> <?php echo $unit; ?></b></h2>
        <p class="fs-4 mt-3">ข้อมูลที่มี</p>
        <p class="fs-4"><?php echo $dateTimeMin->format('d/m/Y'); ?> ถึง <?php echo $dateTimeMax->format('d/m/Y'); ?></p>
    </div>

    <div class="alert alert-danger text-center card">
        <p class="fs-3"><b>เลือกลบ</b></p>
        <form action="" method="post" onsubmit="return validateAndConfirm()">
            <div class="d-flex justify-content-center mb-4 gap-2">
                <div class="w-50">
                    <label for="startDate" class="form-label fs-4">วันที่เริ่มต้น</label>
                    <input type="date" class="form-control py-2 fs-5 text-center" id="startDate" name="startDate" required>
                </div>
                <p class="fs-4 d-flex align-items-end justify-content-center">ถึง</p>
                <div class="w-50">
                <label for="endDate" class="form-label fs-4">วันที่สิ้นสุด</label>
                    <input type="date" class="form-control py-2 fs-5 text-center" id="endDate" name="endDate" required>
                </div>
            </div>
            <div class="d-flex justify-content-between gap-2">
                <!-- <button type="submit" class="btn btn-secondary fs-5 px-5" onclick="showSelectedData()">แสดงข้อมูล</button> -->
                <a href="?page=delete_data" class="btn btn-secondary w-0 fs-5 p-2 px-4"><i class="bi bi-x-circle"></i> ล้าง</a>
                <a class="btn btn-primary w-0 fs-5 p-2 px-4" onclick="showSelectedData()">แสดงขนาดข้อมูล</a>
                <!-- <a href="?page=delete_data" class="btn btn-secondary fs-5 px-4">ยกเลิก</a> -->
            </div>

            <!-- แสดงผลขนาดของข้อมูลที่เลือกไว้ -->
            <div id="resultSection" style="display: none;" class="alert alert-warning text-center card mt-3">
                <p class="fs-4">ขนาดของข้อมูลที่เลือก</p>
                <h2 id="dataSize" class="fs-3"></h2>
                <div class="d-flex justify-content-center mt-4">
                    <button id="submit" class="btn btn-danger fs-5 px-5"><i class="bi bi-trash3"></i> ลบข้อมูล</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Add Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function showSelectedData() {
        var sdate = document.getElementById("startDate").value;
        var edate = document.getElementById("endDate").value;

        if (!sdate || !edate) {
            alert("กรุณาเลือกวันที่ก่อนกดปุ่ม แสดงข้อมูล");
            return false;
        }

        if (sdate > edate) {
            alert("โปรดเลือกช่วงวันที่ให้ถูกต้อง (วันที่เริ่มต้น ต้องน้อยกว่าหรือเท่ากับ วันที่สิ้นสุด)");
            return false;
        }

        $.ajax({
            type: "POST",
            url: "api/getDataSizePreDel.php", // ไฟล์ PHP ที่ประมวลผล
            data: {
                startDate: sdate,
                endDate: edate
            },
            success: function(response) {
                var data = JSON.parse(response);
                var size = data[0];
                var unit = data[1];

                // แสดงผลข้อมูลที่เลือก
                document.getElementById("dataSize").innerHTML = size + " " + unit;
                // แสดง section ของผลลัพธ์
                document.getElementById("resultSection").style.display = "block";
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + error);
                alert("เกิดข้อผิดพลาดในการเรียกข้อมูล");
            }
        });
    }


    // ฟังก์ชั่นของปุ่ม delete
    function validateAndConfirm() {
        var sdate = document.getElementById("startDate").value;
        var edate = document.getElementById("endDate").value;
        return confirm("คุณต้องการลบข้อมูลในช่วงวันที่ " + sdate + " ถึง " + edate + " ใช่หรือไม่?");
    }

    $(function() {
        $("#startDate").datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: new Date('<?php echo $minDate; ?>'),
            maxDate: new Date('<?php echo $maxDate; ?>')
        });
        $("#endDate").datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: new Date('<?php echo $minDate; ?>'),
            maxDate: new Date('<?php echo $maxDate; ?>')
        });
    });
</script>


<?php

include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";

?>