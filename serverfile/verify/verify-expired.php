<?php
if (!isset($verify_access) || $verify_access == false) {
    die(header("HTTP/1.1 404 Not Found"));
}
$verify_access = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Token Expired</title>
</head>
<body>
    <h1>Sorry, your email verification link has expired.</h1>
    <p>Please request a new verification link.</p>
</body>
</html>
