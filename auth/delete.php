<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
  echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/db.php';

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['ok' => false, 'error' => 'Invalid id']);
  exit;
}

// Optional: only allow delete of admin-added movies (id > 12)
// Remove this check if admin may delete any movie
// if ($id <= 12) {
//   echo json_encode(['ok' => false, 'error' => 'Cannot delete built-in movies']);
//   exit;
// }

try {
  $stmt = $pdo->prepare('SELECT poster, img FROM movies WHERE movie_id = ?');
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $pdo->prepare('DELETE FROM movies WHERE movie_id = ?')->execute([$id]);
  // watchlist rows cascade if FK is set

  if ($row) {
    if (!empty($row['poster'])) {
      $p = __DIR__ . '/../' . $row['poster'];
      if (is_file($p)) @unlink($p);
    }
    if (!empty($row['img'])) {
      $p = __DIR__ . '/../assets/Images/' . $row['img'];
      if (is_file($p)) @unlink($p);
    }
  }

  echo json_encode(['ok' => true]);
} catch (PDOException $e) {
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}