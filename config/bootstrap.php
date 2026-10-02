<?php
// Shared by auth/ and api/: starts the session and opens the database.
// Change the credentials here if your MySQL isn't the XAMPP default.
mysqli_report(MYSQLI_REPORT_OFF);
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
  session_start();
}
$conn = new mysqli("localhost", "root", "", "MovieBooking");
if ($conn->connect_error) {
  http_response_code(500);
  exit("Database connection failed. Is MySQL running and is schema.sql imported?");
}
$conn->set_charset("utf8mb4");
