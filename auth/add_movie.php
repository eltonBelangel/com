<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
  echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/db.php'; // $pdo

$title   = trim($_POST['title'] ?? '');
$genre   = trim($_POST['genre'] ?? '');
$year    = (int)($_POST['year'] ?? date('Y'));
$runtime = (int)($_POST['runtime'] ?? 0);
$rating  = (float)($_POST['rating'] ?? 7.0);
$moods   = trim($_POST['moods'] ?? '');
$about   = trim($_POST['about'] ?? 'Added from admin panel.');
$price   = (float)($_POST['price'] ?? 0);

if ($title === '') {
  echo json_encode(['ok' => false, 'error' => 'Title is required']);
  exit;
}

$posterPath = null;
$imgName = null;

if (!empty($_FILES['poster']['name']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
  $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $_FILES['poster']['tmp_name']);
  finfo_close($finfo);

  if (!in_array($mime, $allowed)) {
    echo json_encode(['ok' => false, 'error' => 'Only JPG, PNG, WEBP, GIF']);
    exit;
  }
  if ($_FILES['poster']['size'] > 5 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'Max 5MB']);
    exit;
  }

  $ext = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => 'gif',
  };
  $imgName = 'movie_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;

  // Save under assets/Images (user site) AND auth-relative path for admin preview
  $dir1 = __DIR__ . '/../assets/Images/';
  $dir2 = __DIR__ . '/assets/images/';
  foreach ([$dir1, $dir2] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
  }
  $dest = $dir1 . $imgName;
  if (!move_uploaded_file($_FILES['poster']['tmp_name'], $dest)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save image']);
    exit;
  }
  @copy($dest, $dir2 . $imgName);
  $posterPath = 'assets/Images/' . $imgName;
}

try {
  $stmt = $pdo->prepare(
    'INSERT INTO movies (title, genre, price, year, runtime, rating, img, genres, moods, about, poster)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
  );
  $stmt->execute([
    $title,
    $genre ?: null,
    $price,
    $year ?: null,
    $runtime,
    $rating,
    $imgName,
    $genre,           // genres column as comma-separated (single genre ok)
    $moods ?: null,
    $about,
    $posterPath,
  ]);
  $newId = (int)$pdo->lastInsertId();

  echo json_encode([
    'ok' => true,
    'movie' => [
      'id' => $newId,
      'title' => $title,
      'genre' => $genre,
      'year' => $year,
      'runtime' => $runtime,
      'rating' => $rating,
      'poster' => $posterPath,
      'img' => $imgName,
      'moods' => $moods,
      'about' => $about,
    ],
  ]);
} catch (PDOException $e) {
  if ($posterPath && file_exists(__DIR__ . '/../' . $posterPath)) {
    @unlink(__DIR__ . '/../' . $posterPath);
  }
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}