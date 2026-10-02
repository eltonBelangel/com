<?php include 'db.php'; include 'header.php'; ?>

<h1>Movies</h1>

<table>
<tr>
  <th>ID</th>
  <th>Title</th>
  <th>Genre</th>
  <th>Price</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM movies");
while ($row = $result->fetch_assoc()):
?>
<tr>
  <td><?= $row['movie_id'] ?></td>
  <td><?= $row['title'] ?></td>
  <td><?= $row['genre'] ?></td>
  <td>$<?= $row['price'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php include 'footer.php'; ?>
