<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/db.php';

$title = trim($_POST['title'] ?? '');
$genre = trim($_POST['genre'] ?? '');
$price = isset($_POST['price']) && $_POST['price'] !== '' 
           ? (float) $_POST['price'] 
           : 0;

// Only title is required by the database
if ($title === '') {
    echo json_encode(['ok' => false, 'error' => 'Title is required']);
    exit;
}

$posterPath = null;

// Handle image upload
if (!empty($_FILES['poster']['name']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $_FILES['poster']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        echo json_encode(['ok' => false, 'error' => 'Only JPG, PNG, WEBP or GIF allowed']);
        exit;
    }

    if ($_FILES['poster']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'error' => 'Image too large (max 5MB)']);
        exit;
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg'
    };

    $filename  = 'movie_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadDir = __DIR__ . '/assets/images/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $dest = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['poster']['tmp_name'], $dest)) {
        echo json_encode(['ok' => false, 'error' => 'Failed to save image']);
        exit;
    }

    $posterPath = 'assets/images/' . $filename;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO movies (title, genre, price, poster) 
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$title, $genre ?: null, $price, $posterPath]);

    $newId = (int) $pdo->lastInsertId();

    echo json_encode([
        'ok'    => true,
        'movie' => [
            'id'     => $newId,
            'title'  => $title,
            'genre'  => $genre,
            'price'  => $price,
            'poster' => $posterPath
        ]
    ]);
} catch (PDOException $e) {
    if ($posterPath && file_exists(__DIR__ . '/' . $posterPath)) {
        unlink(__DIR__ . '/' . $posterPath);
    }
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}