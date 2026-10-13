<?php
require __DIR__ . '/../config/bootstrap.php';
$wasAdmin = !empty($_SESSION['is_admin']);
$_SESSION = [];
session_destroy();
header('Location: ' . ($wasAdmin ? 'admin_login.php' : '../src/screens/index.html'));