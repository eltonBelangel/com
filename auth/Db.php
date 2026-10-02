<?php
// Database connection. Fill in your own details (or skip this file if your
// config/bootstrap.php already creates a PDO connection named $pdo).
$DB_HOST = 'localhost';
$DB_NAME = 'moviebooking';   // your database name
$DB_USER = 'root';       // XAMPP default
$DB_PASS = '';           // XAMPP default is empty

$pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);