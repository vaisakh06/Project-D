-- Migration 003: add contact_messages table.
-- Stores messages submitted through the contact form.
-- Safe to run multiple times on MySQL 8.x.

USE initial_d;

DELIMITER $$

CREATE PROCEDURE add_contact_messages_table()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'contact_messages'
    ) THEN
        CREATE TABLE contact_messages (
            message_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('Unread', 'Read', 'Replied') NOT NULL DEFAULT 'Unread',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    END IF;
END$$

DELIMITER ;

CALL add_contact_messages_table();

DROP PROCEDURE add_contact_messages_table;
