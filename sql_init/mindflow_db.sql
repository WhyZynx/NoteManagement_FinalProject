CREATE DATABASE IF NOT EXISTS mindflow_db;
USE mindflow_db;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `shared_notes`;
DROP TABLE IF EXISTS `note_passwords`;
DROP TABLE IF EXISTS `note_images`;
DROP TABLE IF EXISTS `note_labels`;
DROP TABLE IF EXISTS `notes`;
DROP TABLE IF EXISTS `labels`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 1,
  `theme_mode` enum('light','dark','hologram','custom','gradient') DEFAULT 'light',
  `font_size` int(11) DEFAULT 14,
  `font_style` varchar(50) DEFAULT 'Sans-serif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT 'Assets/images/avatar/default.png',
  `verify_token` varchar(255) DEFAULT NULL,
  `note_color` varchar(50) DEFAULT 'pink',
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  `view_mode` enum('grid','list') DEFAULT 'grid',
  `theme_color` varchar(50) DEFAULT '#5385c7',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users
(email, display_name, password_hash)
VALUES
(
 'testuser1@gmail.com',
 'User One',
 '$2y$10$hLcMKYc4.Ix/NpEocZyOvOXuWIVfIvw94ccG4oO2HZHhYXy.BSzf2'
),
(
 'testuser2@gmail.com',
 'User Two',
 '$2y$10$hLcMKYc4.Ix/NpEocZyOvOXuWIVfIvw94ccG4oO2HZHhYXy.BSzf2'
);

CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `pinned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `font_size` int(11) DEFAULT 16,
  `font_style` varchar(100) DEFAULT 'Arial',
  `note_color` varchar(20) DEFAULT '#ffffff',
  `view_mode` enum('grid','list') DEFAULT 'grid',
  `is_locked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notes_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notes`
(`user_id`, `title`, `content`, `note_color`)
VALUES
(
  1,
  'Welcome to MindFlow',
  'This is your first note. Enjoy organizing your ideas and study materials!',
  '#fff9c4'
),
(
  1,
  'Docker Test',
  'This note is used to test Docker Compose deployment.',
  '#c8e6c9'
),
(
  2,
  'User Two Private Note',
  'This is a private note created by User Two.',
  '#bbdefb'
);

CREATE TABLE `labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `label_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `labels_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `labels`
(`user_id`, `label_name`)
VALUES
(1, 'Study'),
(1, 'Important'),
(2, 'Personal');

CREATE TABLE `note_labels` (
  `note_id` int(11) NOT NULL,
  `label_id` int(11) NOT NULL,
  PRIMARY KEY (`note_id`,`label_id`),
  KEY `label_id` (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `note_labels`
  ADD CONSTRAINT `note_labels_ibfk_1`
    FOREIGN KEY (`note_id`)
    REFERENCES `notes` (`id`)
    ON DELETE CASCADE,

  ADD CONSTRAINT `note_labels_ibfk_2`
    FOREIGN KEY (`label_id`)
    REFERENCES `labels` (`id`)
    ON DELETE CASCADE;
    
CREATE TABLE `note_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  CONSTRAINT `note_images_ibfk_1`
    FOREIGN KEY (`note_id`)
    REFERENCES `notes` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `note_passwords` (
  `note_id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`note_id`),
  CONSTRAINT `note_passwords_ibfk_1`
    FOREIGN KEY (`note_id`)
    REFERENCES `notes` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `shared_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `shared_with` int(11) NOT NULL,
  `permission` enum('read','edit') DEFAULT 'read',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  KEY `owner_id` (`owner_id`),
  KEY `shared_with` (`shared_with`),
  CONSTRAINT `shared_notes_ibfk_1`
    FOREIGN KEY (`note_id`)
    REFERENCES `notes` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `shared_notes_ibfk_2`
    FOREIGN KEY (`owner_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `shared_notes_ibfk_3`
    FOREIGN KEY (`shared_with`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `shared_notes`
(`note_id`, `owner_id`, `shared_with`, `permission`)
VALUES
(1, 1, 2, 'read');

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;