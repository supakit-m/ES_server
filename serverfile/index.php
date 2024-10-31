<?php

$can_access = true;
$page = $_GET['page'];

// ตรวจสอบ query string ชื่อ page จาก URL แล้วส่งคืนหน้านั้นๆตามที่ถูกขอมา
// โดยถ้าสิ่งที่ขอมาหรือ page ไม่มีในเงื่อนไงจะทำการ เปลี่ยนหน้าไปยังหน้า admin (?page=admin)

if ($page == "login") {
    include_once dirname(__FILE__). "/pages/login.php";
}

elseif ($page == "forgot_pass") {
    include_once dirname(__FILE__). "/pages/forgot_pass.php";
}

elseif ($page == "forgot2_pass") {
    include_once dirname(__FILE__). "/pages/forgot2_pass.php";
}

elseif ($page == "change_email") {
    include_once dirname(__FILE__). "/pages/change_email.php";
}

elseif ($page == "change_pass") {
    include_once dirname(__FILE__). "/pages/change_pass.php";
}

elseif ($page == "logout") {
    include_once dirname(__FILE__). "/pages/logout.php";
}

elseif ($page == "admin") {
    include_once dirname(__FILE__). "/pages/admin.php";
} 

elseif ($page == "add_admin") {
    include_once dirname(__FILE__). "/pages/add_admin.php";
} 

elseif ($page == "user") {
    include_once dirname(__FILE__). "/pages/user.php";
} 

elseif ($page == "statistic") {
    include_once dirname(__FILE__). "/pages/statistic.php";
} 

elseif ($page == "delete_data") {
    include_once dirname(__FILE__). "/pages/delete_data.php";
} 

elseif ($page == "send_news") {
    include_once dirname(__FILE__). "/pages/send_news.php";
} 

elseif ($page == "show_news") {
    include_once dirname(__FILE__). "/pages/show_news.php";
} 

elseif ($page == "edit_default") {
    include_once dirname(__FILE__). "/pages/edit_default.php";
} 

elseif ($page == "edit2_default") {
    include_once dirname(__FILE__). "/pages/edit2_default.php";
} 

elseif ($page == "edit3_default") {
    include_once dirname(__FILE__). "/pages/edit3_default.php";
} 
else {
    header("Location: ?page=admin");
    
}
?>
<head>
    <link rel="icon" href="asset/Img/logoicon.ico" type="image/ico">
</head>