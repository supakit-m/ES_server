<?php

if (!isset($can_access) || $can_access==false) {
    die(header("HTTP/1.1 404 Not Found"));
}
 
if($_SESSION["change_email_dt"] == null){
    $verifyNoti = "";
}else{
    $verifyNoti = "   กรุณายืนยันอีเมล            ";
}

?>

<header class="nav border-bottom pb-2">
    <nav class="navbar bg-white fixed-top border-bottom border-secondary" style="padding-top: 0.2rem; padding-bottom: 0.1rem; min-height: 40px;">
        <div class="container-fluid">
            <!-- <button class="btn btn-outline-dark ms-4 fs-6" type="button" id="sidebarToggle"> -->
                <i class="bi bi-list ms-2 fs-2" id="sidebarToggle"></i>
            <!-- </button> -->
            <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                <svg class="bi" width="20" height="0">
                </svg>
                <span class="fs-3 text-col-conten "><b>Ergonomic System</b></span>
            </a>
            <span style="color: red;"><?php echo $verifyNoti; ?></span>
            <div class="dropdown text-start">
                <a href="#" class="d-block link-body text-decoration-none dropdown-toggle text-black fs-4 me-3" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-2 m-0 p-0"></i>
                    <span class="text-col-conten fs-2"><b><?php echo $_SESSION["username"]; ?></b></span>
                </a>
                <ul class="dropdown-menu text-small text-center mt-1">
                    <li><a class="dropdown-item hv" href="?page=change_email">เปลี่ยนที่อยู่อีเมลใหม่</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item hv" href="?page=change_pass">เปลี่ยนรหัสผ่านใหม่</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item hv" href="?page=logout">ออกจากระบบ &nbsp; <i class="bi bi-box-arrow-right"></i></a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="bg-white border-end position-fixed top-0 mt-5 vh-100" id="sidebar">
    <a href="#" class="d-flex align-items-center mb-mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <svg class="bi my-4" width="50" height="70">
            <use xlink:href="#bootstrap"></use>
        </svg>
        <img src="/web/asset/Img/logo1.png" alt="Logo" width="130" height="130" class="img-fluid ms-0 mt-3">
    </a>
    <div class="list-group list-group-flush mt-2">
        <a href="?page=admin" class="text-decoration-none" style="border: 0px;">
            <li class="border-bottom border-top list-group-item hv py-0 adminbg border-0">
                <i class="bi bi-person-circle m-1 fs-2"></i>
                <span class="label ms-0 fs-5">ผู้ดูแลระบบ</span>
            </li>
        </a>
        <a href="?page=user" class="text-decoration-none">
            <li class="border-bottom list-group-item hv py-0 userbg border-0">
                <i class="bi bi-people m-1 fs-2"></i>
                <span class="label ms-0 fs-5">ผู้ใช้งาน</span>
            </li>
        </a>
        <a href="?page=statistic" class="text-decoration-none">
            <li class="border-bottom list-group-item hv py-0 ststisbg border-0">
                <i class="bi bi-graph-up m-1 fs-2"></i>
                <span class="label ms-0 fs-5">สถิติ</span>
            </li>
        </a>
        <a href="?page=delete_data" class="text-decoration-none">
            <li class="border-bottom list-group-item hv py-0 deletebg border-0">
                <i class="bi bi-trash3 m-1 fs-2"></i>
                <span class="label ms-0 fs-5">ลบข้อมูล</span>
            </li>
        </a>
        <a href="?page=send_news" class="text-decoration-none">
            <li class="border-bottom list-group-item hv py-0 senbg border-0">
                <i class="bi bi-send m-1 fs-2"></i>
                <span class="label ms-0 fs-5">ส่งข่าวสาร</span>
            </li>
        </a>
        <a href="?page=show_news" class="text-decoration-none">
            <li class="border-bottom list-group-item hv py-0 newsbg border-0">
                <i class="bi bi-card-list m-1 fs-2"></i>
                <span class="label ms-0 fs-5">รายการข่าวสาร</span>
            </li>
        </a>
        <div class="dropdown">
            <a class="text-decoration-none" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                <li class="border-bottom list-group-item hv py-0 editbg border-0">
                    <i class="bi bi-sliders2 m-1 fs-2"></i>
                    <span class="label ms-0 fs-5">แก้ไขค่าเริ่มต้น <i class="bi bi-caret-down"></i></span>
                </li>
            </a>
            <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
                <li><a class="dropdown-item py-2 hv active1" href="?page=edit_default">ความถี่การตรวจจับท่านั่ง</a></li>
                <li><a class="dropdown-item border-top border-bottom py-2 hv active2" href="?page=edit2_default">เวลานั่งต่อเนื่องเพื่อแจ้งเตือน</a></li>
                <li><a class="dropdown-item py-2 hv active3" href="?page=edit3_default">ความถี่แจ้งเตือนนั่งเกินเวลา</a></li>
            </ul>
        </div>

    </div>
</div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sidebarToggle = document.getElementById('sidebarToggle');
            var sidebar = document.getElementById('sidebar');
            var mainContent = document.getElementById('main-content');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('collapsed');
            });
        });
    </script>

    <div class="flex-grow-1 p-3 texe-col-conten mt-5" id="main-content">