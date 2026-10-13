<?php
require __DIR__ . '/../config/bootstrap.php';
header('Content-Type: application/json');
echo json_encode(isset($_SESSION['user_id'])
  ? ['loggedIn' => true, 'user_id' => (int)$_SESSION['user_id'], 'name' => $_SESSION['name'] ?? '']
  : ['loggedIn' => false]);