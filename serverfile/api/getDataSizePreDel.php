<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sdate = $_POST['startDate'];
    $edate = $_POST['endDate'];

    // สร้างข้อมูล JSON สำหรับส่งไปยัง API
    $inputJson = json_encode([
        'startDate' => $sdate,
        'endDate' => $edate
    ]);

    // ตั้งค่า cURL
    $ch = curl_init("http://www.mesb.in.th:8000/pre_delete_by_date/"); // เปลี่ยนเป็น URL ของ API
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($inputJson)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJson);

    // ส่งคำขอ
    $response = curl_exec($ch);

    // ตรวจสอบข้อผิดพลาด
    if (curl_errno($ch)) {
        echo json_encode(['error' => curl_error($ch)]);
    } else {
        // ส่งผลลัพธ์กลับไปยัง JavaScript
        echo $response; // คาดว่าผลลัพธ์จะเป็น JSON
    }

    // ปิดการเชื่อมต่อ cURL
    curl_close($ch);
}
?>
