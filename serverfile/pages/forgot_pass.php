<?php
if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}
$can_access = true;
include_once dirname(__FILE__) . "/../database/db.php";
session_start();
if (isset($_SESSION['is_login']) && $_SESSION['is_login'] == true) {
    header("Location: ?page=admin");
    exit;
}
?>





<!doctype html>
<html lang="en">

<head>
    <link rel="icon" href="asset/Img/logoicon.ico" type="image/ico">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ergonomic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<style>
    @font-face {
        font-family: Myfont;
        src: url(Mitr-Regular.ttf);
    }

    body {
        font-family: Myfont;
        background-color: #cde4e9;
        height: 98vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .lg {
        border-bottom: 10px solid #ffffff;
        border-right: 10px solid #ffffff;
    }

    .btn-back {
        display: block;
        margin: 20px auto;
        font-size: 1.25rem;
    }
</style>

<body>
    <div class="container-sm">
        <div class="card col-md-4 m-auto border-secondary lg" style="border-radius: 30px;">
            <img src="./asset/Img/logo1.png" alt="Logo" width="40%" height="40%" class="m-auto mt-4">
            <div class="card-body">
                <h1 class="text-center mb-5 mt-3 fs-1">ขอรหัสผ่านใหม่</h1>
                <form id="forgetPass_form" method="POST" action="/web/api/sendNewPassword.php">
                    <div class="mb-3">
                        <label for="email" class="form-label fs-4">อีเมลผู้ดูแลระบบ :</label>
                        <input type='hidden' name='backPath' id='backPath' value="?page=login">
                        <input type="email" name="email" id="email" class="form-control p-2 fs-5" placeholder="aaa@mail.com" required />
                    </div>
                    <button type="submit" id="button_login" class="btn btn-primary btn-lg mt-3 w-100 fs-5">ตกลง</button>
                </form>
                <a href="?page=login" class="btn btn-secondary btn-back fs-5">กลับไปหน้าเข้าสู่ระบบ</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>