<?php include 'db.php'; include 'header.php'; ?>

<h1>Bookings</h1>

<table>
<tr>
  <th>ID</th>
  <th>User</th>
  <th>Movie</th>
  <th>Schedule</th>
  <th>Status</th>
</tr>

<?php
$sql = "
SELECT booking.booking_id, users.first_name, movies.title,
       booking.schedule, booking.status
FROM booking
JOIN users ON booking.user_id = users.user_id
JOIN movies ON booking.movie_id = movies.movie_id
";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()):
?>
<tr>
  <td><?= $row['booking_id'] ?></td>
  <td><?= $row['first_name'] ?></td>
  <td><?= $row['title'] ?></td>
  <td><?= $row['schedule'] ?></td>
  <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php include 'footer.php'; ?>
