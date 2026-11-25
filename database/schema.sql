-- VNCHIP Technology JSC Database Schema
-- UTF-8 Support for Vietnamese and Global Multilingual Content

CREATE DATABASE IF NOT EXISTS `vnchip_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `vnchip_db`;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` ENUM('superadmin', 'editor') NOT NULL DEFAULT 'superadmin',
  `last_login` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. System Settings Table (Homepage Stats, Contact info, Slogans)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. News / Media Center Articles Table
CREATE TABLE IF NOT EXISTS `news_articles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `thumbnail` VARCHAR(255) NOT NULL DEFAULT 'assets/images/media_htv9_event.webp',
  `summary` TEXT NOT NULL,
  `content` LONGTEXT NOT NULL,
  `author` VARCHAR(100) DEFAULT 'VNCHIP Editorial Team',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `views` INT NOT NULL DEFAULT 0,
  `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_news_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Inquiries / Contact Requests Table
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `company` VARCHAR(150) NULL,
  `service_interest` VARCHAR(100) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'in_review', 'responded') DEFAULT 'new',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Menus Table (Ported from vnchips_old)
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT DEFAULT 0 COMMENT 'ID of parent menu, 0 for main navigation',
  `title` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) DEFAULT '#',
  `sort_order` INT DEFAULT 0,
  `target` VARCHAR(20) DEFAULT '_self',
  `is_divider` TINYINT(1) DEFAULT 0,
  `column_group` INT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Seed Default Superadmin (Username: admin | Password: admin123)
INSERT INTO `admins` (`id`, `username`, `password_hash`, `fullname`, `email`, `role`)
VALUES (1, 'admin', '$2y$10$R.dncMXr4PlkdkbGCMpyfuLfgVz0URHzoKpP6yVSws6PPBsCXO8Dq', 'System Administrator', 'admin@vnchip-inc.com', 'superadmin')
ON DUPLICATE KEY UPDATE 
  `password_hash` = VALUES(`password_hash`),
  `fullname` = VALUES(`fullname`),
  `email` = VALUES(`email`);


-- 8. Partner Case Studies (Ported from vnchips_old posts where category = partners)
CREATE TABLE IF NOT EXISTS `partners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `category_name` VARCHAR(100) NOT NULL DEFAULT 'Partners',
  `image_url` VARCHAR(255) NULL,
  `excerpt` TEXT NULL,
  `content_image` VARCHAR(255) NULL,
  `content_heading` VARCHAR(255) NULL,
  `content_body` TEXT NULL,
  `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Team Members / Executive Leaders (Ported from vnchips_old members)
CREATE TABLE IF NOT EXISTS `members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `short_position` VARCHAR(255) NULL COMMENT 'Short role shown on About Us cards',
  `team_group` VARCHAR(20) NOT NULL DEFAULT 'leadership' COMMENT 'leadership | key_team (About Us section)',
  `image_url` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `linkedin_url` VARCHAR(255) NULL,
  `twitter_url` VARCHAR(255) NULL,
  `email_address` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Media Library (Ported from vnchips_old media)
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` VARCHAR(50) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Visitor Logs for Traffic Analytics (Ported from vnchips_old visitor_logs)
CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `page_url` VARCHAR(255) NOT NULL,
  `referrer` VARCHAR(255) NULL,
  `browser` VARCHAR(100) NULL,
  `device` VARCHAR(50) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_visitor_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
