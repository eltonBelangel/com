<?php include 'db.php'; include 'header.php'; ?>

<h1>Dashboard</h1>

<div class="cards">
  <div class="card">
    <h3>Users</h3>
    <p>
      <?php
      $res = $conn->query("SELECT COUNT(*) AS total FROM users");
      echo $res->fetch_assoc()['total'];
      ?>
    </p>
  </div>

  <div class="card">
    <h3>Movies</h3>
    <p>
      <?php
      $res = $conn->query("SELECT COUNT(*) AS total FROM movies");
      echo $res->fetch_assoc()['total'];
      ?>
    </p>
  </div>

  <div class="card">
    <h3>Bookings</h3>
    <p>
      <?php
      $res = $conn->query("SELECT COUNT(*) AS total FROM booking");
      echo $res->fetch_assoc()['total'];
      ?>
    </p>
  </div>
</div>

<?php include 'footer.php'; ?>
