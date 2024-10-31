<?php

if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}

session_start();
if (isset($_SESSION['is_login']) && $_SESSION['is_login'] == true) {
    header("Location: ?page=admin");
}


?>

<!doctype html>
<html lang="en">

<head>
    <link rel="icon" href="asset/Img/logoicon.ico" type="image/ico">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MESB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    .lg  {
        border-bottom :10px solid #ffffff;
        border-right :10px solid #ffffff;
    }

    .position-relative {
        position: relative;
    }

    #togglePasswordIcon {
        cursor: pointer;
        position: absolute;
        top: 75%;
        right: 10px;
        transform: translateY(-50%);
    }

</style>

<body>
    <div class="container-sm">
        <div class="card col-md-4 m-auto border-secondary lg" style="border-radius: 30px;">
            
            <img src="./asset/Img/logo1.png" alt="Logo" width="40%" height="40%" class="m-auto mt-4">

            <div class="card-body">
            <h2 class="text-center fs-1">เข้าสู่ระบบ</h2>
                <form id="login_form">
                    <div class="mb-3">
                        <label for="username" class="form-label fs-4">ชื่อผู้ใช้ :</label>
                        <input type="text" name="username" id="username" class="form-control p-2 fs-5" placeholder="admin" required />
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label fs-4">รหัสผ่าน :</label>
                        <input type="password" name="password" id="password" class="form-control p-2 fs-5" placeholder="Password1234" required />
                        <i class="bi bi-eye-slash position-absolute  end-0 translate-middle-y me-3" id="togglePasswordIcon" style="cursor: pointer;"></i>
                    </div>
                    <button type="submit" id="button_login" class="btn btn-primary btn-lg my-3 w-100 fs-5">เข้าสู่ระบบ</button>
                </form>
                <p class="text-center mb-1 fs-5"><a class="link-opacity-50-hover" href="?page=forgot_pass">ลืมรหัสผ่าน ใช่หรือไม่</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var username_input = document.getElementById('username');
            var password_input = document.getElementById('password');
            var login_button = document.getElementById('button_login');
            var login_form = document.getElementById('login_form');

            username_input.addEventListener("input", function() {
                username_input.classList.remove('is-invalid');
            });
            password_input.addEventListener("input", function() {
                password_input.classList.remove('is-invalid');
            });

            login_form.addEventListener("submit", async function(event) {
                event.preventDefault();
                let user_value = username_input.value.trim();
                let pass_value = password_input.value.trim();
                if (user_value === '' || pass_value === '') {
                    if (user_value === '') {
                        username_input.classList.add('is-invalid');
                    }
                    if (pass_value === '') {
                        password_input.classList.add('is-invalid');
                    }
                    return;
                }

                var formdata = new FormData();
                formdata.append("username", user_value);
                formdata.append("password", pass_value);

                let result = await fetch("./api/login.php", {
                    method: 'POST',
                    body: formdata
                }).then(response => response.json())
                  .catch(error => console.log('error', error));

                if (result.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "เข้าสู่ระบบสำเร็จ",
                        showConfirmButton: false,
                        timer: 1000
                    }).then(() => {
                        window.location.href = '?page=home';
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        //title: result.message,
                        title: "ชื่อผู้ใช้ หรือ รหัสผ่าน ไม่ถูกต้อง!",
                        showConfirmButton: false,
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var password_input = document.getElementById('password');
            var toggle_password_icon = document.getElementById('togglePasswordIcon');

            toggle_password_icon.addEventListener('click', function() {
                // Toggle the password visibility
                const type = password_input.getAttribute('type') === 'password' ? 'text' : 'password';
                password_input.setAttribute('type', type);

                // Toggle the icon
                toggle_password_icon.classList.toggle('bi-eye');
                toggle_password_icon.classList.toggle('bi-eye-slash');
            });
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
