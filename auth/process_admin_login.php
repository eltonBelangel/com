<?php
session_start();

// Hardcoded admin credentials
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'password';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
    // Success → create admin session
    $_SESSION['is_admin'] = true;
    $_SESSION['admin_user'] = $username;

    header('Location: admin.php');   // ← your admin page
    exit;
}

// Failed login
header('Location: admin_login.php?error=1');
exit;