<?php

if (!isset($can_access) || $can_access==false) {
    die(header("HTTP/1.1 404 Not Found"));
}

session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] == false) {
    die(header("Location: ?page=login"));
}

// ตรวจสอบว่ามีการส่งข้อมูลเดือนมา
if (isset($_GET['month'])) {
    include_once dirname(__FILE__) . "/../database/db.php";
    $selectedMonth = $_GET['month'];
    $month = date('m', strtotime($selectedMonth));
    $year = date('Y', strtotime($selectedMonth));

    $sql = "SELECT DAY(detectDate) as day, SUM(detectAmount) as detectAmount 
            FROM dailyreport 
            WHERE MONTH(detectDate) = $month AND YEAR(detectDate) = $year 
            GROUP BY DAY(detectDate)";

    $result = $conn->query($sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    header("Content-Type: application/json", true, 200);
    echo json_encode($data);
    exit();
}

include_once dirname(__FILE__) . "/../template/header.php";
include_once dirname(__FILE__) . "/../template/nav-menu.php";
?>

<style>
    .ststisbg {
        background-color: #00c8e2;
        color: #000000;
    }
</style>
    <div class="container">
        <!-- ส่วนของตัวเลือกเดือน/ปี -->
        <div class="d-flex justify-content-center align-items-center my-0">
            <form id="monthForm" class="row g-4">
                <div class="col-auto">
                    <label for="bdaymonth" class="col-form-label fs-3"><b>ปริมาณการตรวจจับท่านั่งที่ผิดจากหลักสรีรศาสตร์ :</b></label>
                </div>
                <div class="col-auto">
                    <input type="month" id="bdaymonth" name="bdaymonth" class="form-control fs-5">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary fs-5 px-4">เลือก</button>
                </div>
            </form>
        </div>
    
        <!-- ส่วนของกราฟ -->
        <div class="mt-4" style="width: 85%; margin:auto;">
            <div class="card">
                <div class="card-body">
                    <div class="chart-container" style="position: relative; width:90%; margin:auto;">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


<script>
    function loadCurrentMonth() {
        const today = new Date();
        const year = today.getFullYear();
        const month = (today.getMonth() + 1).toString().padStart(2, '0');
        const currentMonth = `${year}-${month}`;
        document.getElementById('bdaymonth').value = currentMonth;
        fetchData(currentMonth);
    }

    document.getElementById('monthForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const selectedMonth = document.getElementById('bdaymonth').value;
        fetchData(selectedMonth);
    });

    async function fetchData(selectedMonth) {
        try {
            const response = await fetch(`?<?php echo $_SERVER['QUERY_STRING']; ?>&month=${selectedMonth}`);
            // const response = await fetch(`./api/for_statistic.php?month=${selectedMonth}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            console.log(data); // ทดสอบดูข้อมูลที่ได้รับ
            renderChart(data);
        } catch (error) {
            console.log('Error fetching data:', error);
        }
    }

    function renderChart(data) {
    const ctx = document.getElementById('usageChart').getContext('2d');
    const labels = data.map(item => item.day);
    const values = data.map(item => item.detectAmount);

    // Clear existing chart if any
    if (window.myChart) {
        window.myChart.destroy();
    }

    window.myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'ปริมาณการตรวจจับ',
                data: values,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'วันที่',
                        color: 'gray',
                        font: {
                            size: 25,
                            family: 'Myfont', // เปลี่ยนฟอนต์ของแกน X
                            // weight: 'bold'
                        }
                    },
                    ticks: {
                        font: {
                            size: 18,
                            family: 'Myfont', // เปลี่ยนฟอนต์ของตัวเลขในแกน X
                            // weight: 'bold'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวน (ครั้ง)',
                        color: 'gray',
                        font: {
                            size: 25,
                            family: 'Myfont', // เปลี่ยนฟอนต์ของแกน Y
                            // weight: 'bold'
                        }
                    },
                    ticks: {
                        font: {
                            size: 18,
                            family: 'Myfont', // เปลี่ยนฟอนต์ของตัวเลขในแกน Y
                            // weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'gray'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 25,
                            family: 'Myfont', // เปลี่ยนฟอนต์ของ legend
                            // weight: 'bold'
                        }
                    }
                }
            }
        }
    });
}
    // โหลดเดือนปัจจุบันเมื่อโหลดหน้าเพจ
    loadCurrentMonth();
</script>

<?php
include_once dirname(__FILE__) . "/../template/nav-menu-end.php";
include_once dirname(__FILE__) . "/../template/footer.php";
