<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
  echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/db.php'; // $pdo

$id    = (int)($_POST['id'] ?? 0);
$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$pass  = (string)($_POST['password'] ?? '');

if ($id <= 0)                                   { echo json_encode(['ok' => false, 'error' => 'Invalid user id']); exit; }
if ($first === '' || $last === '')              { echo json_encode(['ok' => false, 'error' => 'First and last name are required']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['ok' => false, 'error' => 'Enter a valid email']); exit; }
if ($pass !== '' && strlen($pass) < 8)          { echo json_encode(['ok' => false, 'error' => 'Password must be at least 8 characters']); exit; }

try {
  // email must not belong to someone else
  $chk = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ?');
  $chk->execute([$email, $id]);
  if ($chk->fetch()) {
    echo json_encode(['ok' => false, 'error' => 'That email is already used by another user']);
    exit;
  }

  $sql = 'UPDATE users SET first_name = ?, last_name = ?, email = ?';
  $params = [$first, $last, $email];
  if ($pass !== '') {
    $sql .= ', password_hash = ?';
    $params[] = password_hash($pass, PASSWORD_DEFAULT);
  }
  $sql .= ' WHERE user_id = ?';
  $params[] = $id;

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);

  echo json_encode([
    'ok' => true,
    'user' => [
      'id'    => $id,
      'first' => $first,
      'last'  => $last,
      'name'  => $first . ' ' . $last,
      'email' => $email,
    ],
  ]);
} catch (PDOException $e) {
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}