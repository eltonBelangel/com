<?php
// GET                      -> {"ids":[1,5,...]}          the user's watchlist
// POST   {"movie_id":3}    -> INSERT row (ignored if already there)
// POST   {"movie_ids":[..]}-> INSERT several (used to merge a guest's list on login)
// DELETE ?movie_id=3       -> DELETE the row
require __DIR__ . '/../config/bootstrap.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error' => 'login required']); exit; }
$uid = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Writes must come from our own JS (a cross-site form can't set this header).
if ($method !== 'GET' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'fetch') { http_response_code(403); echo json_encode(['error' => 'forbidden']); exit; }

if ($method === 'GET') {
  $stmt = $conn->prepare("SELECT movie_id FROM watchlist WHERE user_id = ? ORDER BY added_at");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $ids = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'movie_id'));
  echo json_encode(['ids' => $ids]);

} elseif ($method === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true) ?: [];
  $ids = isset($body['movie_ids']) ? (array)$body['movie_ids'] : [$body['movie_id'] ?? 0];
  $stmt = $conn->prepare("INSERT IGNORE INTO watchlist (user_id, movie_id) VALUES (?, ?)");
  foreach ($ids as $mid) {
    $mid = (int)$mid;
    $stmt->bind_param("ii", $uid, $mid);
    $stmt->execute(); // unknown movie ids are rejected by the foreign key
  }
  echo json_encode(['ok' => true]);

} elseif ($method === 'DELETE') {
  $mid = (int)($_GET['movie_id'] ?? 0);
  $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
  $stmt->bind_param("ii", $uid, $mid);
  $stmt->execute();
  echo json_encode(['ok' => true, 'deleted' => $stmt->affected_rows]);

} else {
  http_response_code(405);
}
