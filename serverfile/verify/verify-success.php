<?php
session_start();
$verify_access = $_SESSION['verify_access'];
if (!isset($verify_access) || $verify_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}
$_SESSION['verify_access'] = false;

echo "<script>alert('Verification Successful.'); window.location.href = '/index.php';</script>";
?>
