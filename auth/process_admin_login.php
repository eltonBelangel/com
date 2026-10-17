<?php
require __DIR__ . '/../config/bootstrap.php';
$cfg = require __DIR__ . '/../config/admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admin_login.php'); exit; }

// lock out after too many wrong attempts
if (($_SESSION['adm_lock'] ?? 0) > time()) {
    header('Location: admin_login.php?error=locked');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$ok = hash_equals($cfg['username'], $username)
   && password_verify($password, $cfg['password_hash']);

if ($ok) {
    session_regenerate_id(true);          // stop session fixation
    unset($_SESSION['adm_fails'], $_SESSION['adm_lock']);
    $_SESSION['is_admin']   = true;
    $_SESSION['admin_user'] = $username;
    header('Location: admin.php');
    exit;
}

$_SESSION['adm_fails'] = ($_SESSION['adm_fails'] ?? 0) + 1;
if ($_SESSION['adm_fails'] >= 5) {        // 5 misses -> 60 second lock
    $_SESSION['adm_lock']  = time() + 60;
    $_SESSION['adm_fails'] = 0;
}
usleep(500000);                           // slow down guessing
header('Location: admin_login.php?error=1');
exit;