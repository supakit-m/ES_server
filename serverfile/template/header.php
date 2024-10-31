<?php

if (!isset($can_access) || $can_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="asset/Img/logoicon.ico" type="image/ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ergonomic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- <link rel="stylesheet" href="asset/css/body_menu.css"> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @font-face {
            font-family: Myfont;
            src: url(Mitr-Regular.ttf);
        }
        #sidebar {
            height: 100%;
            width: 226px;
            overflow: scroll;
            -ms-overflow-style: none;
            /* ซ่อน scrollbar ใน Internet Explorer และ Edge */
            scrollbar-width: none;
            /* ซ่อน scrollbar ใน Firefox */
            -webkit-overflow-scrolling: none;
        }

        #sidebar.collapsed {
            width: 0px;
            overflow: hidden;
        }

        /* #sidebar:not(.collapsed) .label { 
                display: none;
            }*/

        #main-content {
            margin-left: 226px;
            width: calc(100% - 226px);
            /* transition: all 0.3s; */
        }

        #main-content.collapsed {
            margin-left: 0px;
            width: 100%;
        }

        body {
            font-family: Myfont;
            background-color: #dfeef1;
        }

        .text-col-conten {
            color: #00c8e2;
        }

        .hv:hover {
            background-color: #00c8e2;
            color: #000000;
        }

        .dropdown-menu {
            max-width: 300px; /* กำหนดความกว้างสูงสุดของ dropdown */
            overflow-wrap: break-word; /* ตัดคำถ้าคำยาวเกินไป */
            white-space: normal; /* ทำให้ข้อความสามารถห่อขึ้นบรรทัดใหม่ได้ */
        }

    </style>
    <script>
       $(document).ready(function() {
           $(".status-checkbox").change(function() {
               var adminID = $(this).data("adminid");
               var usable = $(this).is(":checked") ? 1 : 0;
               $.ajax({
                   url: 'deploy/update_status.php',
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
</head>

<body>