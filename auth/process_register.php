<?php

require __DIR__ . '/../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$pass  = $_POST['password'] ?? '';

/* Validation */
if (
    $first === '' ||
    $last === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    header('Location: register.php?error=invalid');
    exit;
}

if (strlen($pass) < 8) {
    header('Location: register.php?error=short');
    exit;
}

/* Check if email already exists */
$stmt = $conn->prepare(
    "SELECT user_id FROM users WHERE email = ?"
);

if (!$stmt) {
    die("Database error while checking email: " . $conn->error);
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    die("Database error while executing email check: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header('Location: register.php?error=exists');
    exit;
}

$stmt->close();

/* Hash password */
$hash = password_hash($pass, PASSWORD_DEFAULT);

/* Insert user */
$stmt = $conn->prepare(
    "INSERT INTO users
    (first_name, last_name, email, password_hash)
    VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    die("Database error while creating user: " . $conn->error);
}

$stmt->bind_param(
    "ssss",
    $first,
    $last,
    $email,
    $hash
);

if (!$stmt->execute()) {
    die("Database error while inserting user: " . $stmt->error);
}

$stmt->close();

/* Login user automatically */
session_regenerate_id(true);

$_SESSION['user_id'] = $conn->insert_id;
$_SESSION['name']    = $first;

header('Location: ../src/screens/index.html');
exit;