<?php

if (!isset($can_access) || $can_access==false) {
    die(header("HTTP/1.1 404 Not Found"));
}

session_start();
session_destroy();

header("Location: ?home");