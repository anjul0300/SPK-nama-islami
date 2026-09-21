<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_role($role)
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: login.php");
        exit();
    }
}

function current_username()
{
    return $_SESSION['username'] ?? '';
}
?>
