<?php
require __DIR__ . '/../config/bootstrap.php';
header('Content-Type: application/json');

$movies = [];
$res = $conn->query("SELECT * FROM movies ORDER BY movie_id ASC");
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $genres = array_filter(array_map('trim', explode(',', $r['genres'] ?? ($r['genre'] ?? ''))));
    $moods  = array_filter(array_map('trim', explode(',', $r['moods'] ?? '')));
    $img    = $r['img'] ?? '';
    $poster = $r['poster'] ?? '';
    // Prefer poster path; else filename in assets/Images
    $fromDb = !empty($poster) || ((int)$r['movie_id'] > 12);

    $movies[] = [
      'id'      => (int)$r['movie_id'],
      'title'   => $r['title'],
      'year'    => (int)($r['year'] ?? date('Y')),
      'runtime' => (int)($r['runtime'] ?? 0),
      'rating'  => (float)($r['rating'] ?? 7.0),
      'img'     => $img ?: ($poster ? basename($poster) : ''),
      'poster'  => $poster,
      'genres'  => $genres ?: ['Other'],
      'moods'   => $moods ?: [],
      'about'   => $r['about'] ?? 'Added from admin panel.',
      'fromDb'  => $fromDb,
    ];
  }
}
echo json_encode($movies);