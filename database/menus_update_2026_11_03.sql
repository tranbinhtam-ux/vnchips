-- Menu update 2026-11-03
--  * "About Us" renamed to "VNCHIP JOURNEY", its "VNCHIP Journey" sub item removed
--  * Sustainable order: Social Responsibilities, Clean Room, Talents Development, Confidentiality, Partners
-- Run in phpMyAdmin on the database the site uses (see DB_NAME in config/config.local.php)
SET NAMES utf8mb4;
START TRANSACTION;

UPDATE `menus` SET `title` = 'VNCHIP JOURNEY' WHERE `id` = 3 AND `parent_id` = 0;
DELETE FROM `menus` WHERE `parent_id` = 3 AND `link` = 'about-us.php#journey';

UPDATE `menus` SET `sort_order` = 1 WHERE `parent_id` = 4 AND `link` = 'sustainable.php#social-responsibilities';
UPDATE `menus` SET `sort_order` = 2 WHERE `parent_id` = 4 AND `link` = 'sustainable.php#clean-room';
UPDATE `menus` SET `sort_order` = 3 WHERE `parent_id` = 4 AND `link` = 'sustainable.php#talents-development';
UPDATE `menus` SET `sort_order` = 4 WHERE `parent_id` = 4 AND `link` = 'sustainable.php#confidentiality';
UPDATE `menus` SET `sort_order` = 5, `title` = 'Partners' WHERE `parent_id` = 4 AND `link` = 'sustainable.php#partner';

COMMIT;
