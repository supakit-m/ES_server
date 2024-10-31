<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $response = [];
    $can_access = true;
    include_once dirname(__FILE__) . "/../database/db.php";

    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid username or password';
        header("Content-Type: application/json", true, 400);
        echo json_encode($response);
        exit();
    }

    $username = $_POST['username'];
    $input_password = hash('sha256', $_POST['password']); // Encrypt password with SHA-256

    // Prepare and bind
    $stmt = $conn->prepare("SELECT `adminID`, `username`, `changeEmailDT`, `password`, `email` FROM `admin` WHERE username = ? AND usable = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows != 1) {
        $response['status'] = 'error';
        $response['message'] = 'username or password is incorrect.';
        header("Content-Type: application/json", true, 400);
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_result($admin_id, $username, $changeEmailDT, $password, $email);
    $stmt->fetch();

    // Compare hashed password
    if ($input_password === $password) {
        // Login success
        session_start();
        $_SESSION["is_login"] = true;
        $_SESSION["username"] = $username; // Store the fetched username in session
        $_SESSION["change_email_dt"] = $changeEmailDT; // Store the changeEmailDT in session
        $_SESSION["email"] = $email; // 
        $_SESSION['admin_id'] = $admin_id;
        $response['status'] = 'success';
        $response['message'] = 'Login successful';
        header("Content-Type: application/json", true, 200);
        echo json_encode($response);
        exit();
    }

    $response['status'] = 'error';
    $response['message'] = 'username or password is incorrect.';
    header("Content-Type: application/json", true, 400);
    echo json_encode($response);
    exit();

    $stmt->close();

} else {
    header("HTTP/1.1 404 Not Found");
    exit();
}
