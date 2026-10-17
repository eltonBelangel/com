<?php
// Admin credentials. Only a password HASH is stored, never the real password.
// To change the password: make a file hash.php containing
//   <?php echo password_hash('YourNewPassword', PASSWORD_DEFAULT);
// open it in the browser, paste the result below, then DELETE hash.php.
return [
    'username'      => 'admin',
    'password_hash' => '$2y$12$l4ih9zQCwm0ZP982QuQWKeH.fRkZfZ4nWkVSDARC3hbfmXL6m4Bl.',   // current password: "password"  (change it!)
];