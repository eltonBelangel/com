-- ReelPick database. Import in phpMyAdmin (Import tab) or: mysql -u root < database/schema.sql
CREATE DATABASE IF NOT EXISTS MovieBooking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE MovieBooking;

CREATE TABLE IF NOT EXISTS users (
  user_id       INT AUTO_INCREMENT PRIMARY KEY,
  first_name    VARCHAR(60)  NOT NULL,
  last_name     VARCHAR(60)  NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Already have a users table from the booking version? Uncomment and run this once (MariaDB/XAMPP):
-- ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NOT NULL DEFAULT '';
-- ALTER TABLE users ADD UNIQUE IF NOT EXISTS uq_users_email (email);

CREATE TABLE IF NOT EXISTS movies (
  movie_id INT PRIMARY KEY,
  title    VARCHAR(150) NOT NULL,
  genre    VARCHAR(60),
  price    DECIMAL(6,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ids 1-12 match src/screens/js/data.js
INSERT INTO movies (movie_id, title, genre) VALUES
 (1,'Furiosa: A Mad Max Saga','Action'),(2,'Interstellar','Sci-Fi'),(3,'The Martian','Sci-Fi'),
 (4,'Ready or Not','Horror'),(5,'Bird Box','Horror'),(6,'A Quiet Place','Horror'),
 (7,'The Exorcist: Believer','Horror'),(8,'A Nightmare on Elm Street','Horror'),(9,'Annabelle','Horror'),
 (10,'Corpse Bride','Animation'),(11,'Frankenweenie','Animation'),(12,'ParaNorman','Animation')
ON DUPLICATE KEY UPDATE title = VALUES(title), genre = VALUES(genre);

-- One row per (user, movie). Removing from the watchlist deletes the row.
CREATE TABLE IF NOT EXISTS watchlist (
  watchlist_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id  INT NOT NULL,
  movie_id INT NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_movie (user_id, movie_id),
  FOREIGN KEY (user_id)  REFERENCES users(user_id)   ON DELETE CASCADE,
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
) ENGINE=InnoDB;
