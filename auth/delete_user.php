<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
  echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/db.php'; // $pdo

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['ok' => false, 'error' => 'Invalid user id']);
  exit;
}

try {
  // the watchlist rows are removed automatically (ON DELETE CASCADE)
  $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
  $stmt->execute([$id]);
  echo json_encode(['ok' => true]);
} catch (PDOException $e) {
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}