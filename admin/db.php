<?php
$conn = new mysqli("localhost", "root", "", "MovieBooking");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
