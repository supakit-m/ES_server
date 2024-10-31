<?php

if (!isset($can_access) || $can_access==false) {
    die(header("HTTP/1.1 404 Not Found"));
}


// db.php
$servername     =  "localhost";
$username       =  "root01";
$password       =  "1234";
$dbname         =  "mesb_database";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Connect Success";
}
?>