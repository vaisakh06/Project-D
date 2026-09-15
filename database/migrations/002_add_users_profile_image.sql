-- Migration 002: add profile image support for users.
-- Vendors already have vendors.profile_image; admins intentionally have none.
-- Safe to run multiple times on MySQL 8.x: the procedure adds the column
-- only when it does not already exist.

USE initial_d;

DELIMITER $$

CREATE PROCEDURE add_users_profile_image_column()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'profile_image'
    ) THEN
        ALTER TABLE users
            ADD COLUMN profile_image VARCHAR(255) NULL AFTER phone;
    END IF;
END$$

DELIMITER ;

CALL add_users_profile_image_column();

DROP PROCEDURE add_users_profile_image_column;
