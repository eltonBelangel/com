<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
  echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/db.php'; // $pdo

$ALLOWED_GENRES = ['Action','Horror','Adventure','Animation','Comedy','Sci-Fi','Romance','Drama','Supernatural','Slasher','Fantasy'];
$ALLOWED_MOODS  = ['clever','creepy','dark humor','epic','gory','heartfelt','intense','mind-bending','survival','whimsical'];

$id      = (int)($_POST['id'] ?? 0);
$title   = trim($_POST['title'] ?? '');
$genre   = trim($_POST['genre'] ?? '');
$year    = (int)($_POST['year'] ?? date('Y'));
$runtime = (int)($_POST['runtime'] ?? 0);
$rating  = max(0, min(10, (float)($_POST['rating'] ?? 7.0)));
$price   = (float)($_POST['price'] ?? 0);
$about   = trim($_POST['about'] ?? '');
$moodList = array_values(array_intersect((array)($_POST['moods'] ?? []), $ALLOWED_MOODS));
$moods   = implode(',', $moodList);

if ($id <= 0)        { echo json_encode(['ok' => false, 'error' => 'Invalid movie id']); exit; }
if ($title === '')   { echo json_encode(['ok' => false, 'error' => 'Title is required']); exit; }
if (!in_array($genre, $ALLOWED_GENRES, true)) {
  echo json_encode(['ok' => false, 'error' => 'Please choose a genre']);
  exit;
}

try {
  $stmt = $pdo->prepare('SELECT poster, img FROM movies WHERE movie_id = ?');
  $stmt->execute([$id]);
  $old = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$old) {
    echo json_encode(['ok' => false, 'error' => 'Movie not found']);
    exit;
  }
} catch (PDOException $e) {
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
  exit;
}

/* ---- optional new poster ---- */
$newPoster = null;
$newImg = null;

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
  $newImg = 'movie_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;

  $dir1 = __DIR__ . '/../assets/Images/';
  $dir2 = __DIR__ . '/assets/images/';
  foreach ([$dir1, $dir2] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
  }
  if (!move_uploaded_file($_FILES['poster']['tmp_name'], $dir1 . $newImg)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save image']);
    exit;
  }
  @copy($dir1 . $newImg, $dir2 . $newImg);
  $newPoster = 'assets/Images/' . $newImg;
}

try {
  $sql = 'UPDATE movies SET title = ?, genre = ?, price = ?, year = ?, runtime = ?, rating = ?,
                            genres = ?, moods = ?, about = ?';
  $params = [$title, $genre, $price, $year ?: null, $runtime, $rating, $genre, $moods ?: null, $about];

  if ($newPoster !== null) {
    $sql .= ', img = ?, poster = ?';
    $params[] = $newImg;
    $params[] = $newPoster;
  }
  $sql .= ' WHERE movie_id = ?';
  $params[] = $id;

  $pdo->prepare($sql)->execute($params);

  // new poster saved OK -> remove the old image files
  if ($newPoster !== null) {
    foreach ([$old['poster'] ?? '', $old['img'] ? 'assets/Images/' . $old['img'] : ''] as $rel) {
      if ($rel && $rel !== $newPoster) {
        $f = __DIR__ . '/../' . $rel;
        if (is_file($f)) @unlink($f);
      }
    }
  }

  echo json_encode([
    'ok' => true,
    'movie' => [
      'id'      => $id,
      'title'   => $title,
      'genre'   => $genre,
      'year'    => $year,
      'runtime' => $runtime,
      'rating'  => $rating,
      'price'   => $price,
      'poster'  => $newPoster ?? ($old['poster'] ?? ''),
      'moods'   => $moods,
      'about'   => $about,
    ],
  ]);
} catch (PDOException $e) {
  if ($newPoster && is_file(__DIR__ . '/../' . $newPoster)) {
    @unlink(__DIR__ . '/../' . $newPoster);
  }
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}