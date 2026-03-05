CREATE DATABASE IF NOT EXISTS finisher_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE finisher_db;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS races (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  date_event DATETIME NOT NULL,
  prix INT NOT NULL,
  latitude DECIMAL(10,7) NOT NULL,
  longitude DECIMAL(10,7) NOT NULL,
  contact_nom VARCHAR(255) NOT NULL,
  contact_email VARCHAR(255) NOT NULL,
  photo_path VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

INSERT INTO admin_users (email, password_hash, created_at)
VALUES ('admin@finisher.test', '$2b$12$rKvZL8Mt1FDFHT6Wj3CgcOwml6xIBjs9UL7E7bKnUso4wk5Nzpcjq', NOW());
