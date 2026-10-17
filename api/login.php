<?php
require __DIR__ . '/../config/bootstrap.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'fetch') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Bad request']);
  exit;
}

// too many wrong passwords -> short lock
if (($_SESSION['login_lock'] ?? 0) > time()) {
  $wait = $_SESSION['login_lock'] - time();
  echo json_encode(['ok' => false, 'error' => "Too many attempts. Try again in {$wait}s."]);
  exit;
}

$in    = json_decode(file_get_contents('php://input'), true) ?: [];
$email = strtolower(trim($in['email'] ?? ''));
$pass  = (string)($in['password'] ?? '');

$stmt = $conn->prepare('SELECT user_id, first_name, password_hash FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($pass, $user['password_hash'])) {
  $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
  if ($_SESSION['login_fails'] >= 5) {
    $_SESSION['login_lock']  = time() + 30;
    $_SESSION['login_fails'] = 0;
  }
  usleep(400000);
  echo json_encode(['ok' => false, 'error' => 'Wrong email or password.']);
  exit;
}

session_regenerate_id(true);
unset($_SESSION['login_fails'], $_SESSION['login_lock']);
$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['name']    = $user['first_name'];
echo json_encode(['ok' => true, 'name' => $user['first_name']]);