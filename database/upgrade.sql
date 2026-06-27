-- Upgrade script for existing installations
USE vexogen_crm;

ALTER TABLE company_settings
    ADD COLUMN IF NOT EXISTS smtp_host VARCHAR(200) NULL AFTER upi_id,
    ADD COLUMN IF NOT EXISTS smtp_port SMALLINT NOT NULL DEFAULT 587 AFTER smtp_host,
    ADD COLUMN IF NOT EXISTS smtp_user VARCHAR(180) NULL AFTER smtp_port,
    ADD COLUMN IF NOT EXISTS smtp_pass VARCHAR(255) NULL AFTER smtp_user,
    ADD COLUMN IF NOT EXISTS smtp_encryption VARCHAR(10) NULL DEFAULT 'tls' AFTER smtp_pass,
    ADD COLUMN IF NOT EXISTS backup_token VARCHAR(64) NULL AFTER smtp_encryption,
    ADD COLUMN IF NOT EXISTS notify_payment_overdue TINYINT(1) NOT NULL DEFAULT 1 AFTER backup_token,
    ADD COLUMN IF NOT EXISTS notify_deadline TINYINT(1) NOT NULL DEFAULT 1 AFTER notify_payment_overdue,
    ADD COLUMN IF NOT EXISTS notify_task_assigned TINYINT(1) NOT NULL DEFAULT 1 AFTER notify_deadline;
