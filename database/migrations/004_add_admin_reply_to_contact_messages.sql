-- Migration 004: add admin_reply and replied_at columns to contact_messages.
-- Stores admin replies submitted through the admin panel.
-- Safe to run multiple times on MySQL 8.x.

USE initial_d;

DELIMITER $$

CREATE PROCEDURE add_admin_reply_to_contact_messages()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'contact_messages'
          AND COLUMN_NAME = 'admin_reply'
    ) THEN
        ALTER TABLE contact_messages
            ADD COLUMN admin_reply TEXT NULL AFTER message,
            ADD COLUMN replied_at DATETIME NULL AFTER admin_reply;
    END IF;
END$$

DELIMITER ;

CALL add_admin_reply_to_contact_messages();

DROP PROCEDURE add_admin_reply_to_contact_messages;
