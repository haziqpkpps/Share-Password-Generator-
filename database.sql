CREATE DATABASE IF NOT EXISTS `password_share` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `password_share`;

-- Table for storing shared passwords
CREATE TABLE `shares` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(64) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `view_once` TINYINT(1) NOT NULL DEFAULT 0,
  `consumed_at` DATETIME NULL,
  `consumed_ip` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin user table
CREATE TABLE `admin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (use a secure password hash)
-- Example (run in PHP):
--   php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
-- Then paste the hash below:
INSERT INTO `admin` (`username`, `password_hash`)
VALUES ('admin', '<PASTE_HASH_HERE>');