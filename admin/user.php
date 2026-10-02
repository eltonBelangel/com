<?php include 'db.php'; include 'header.php'; ?>

<h1>Users</h1>

<table>
<tr>
  <th>ID</th>
  <th>Name</th>
  <th>Email</th>
  <th>Created At</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()):
?>
<tr>
  <td><?= $row['user_id'] ?></td>
  <td><?= $row['first_name'] . " " . $row['last_name'] ?></td>
  <td><?= $row['email'] ?></td>
  <td><?= $row['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php include 'footer.php'; ?>
