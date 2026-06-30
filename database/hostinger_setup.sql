-- ============================================
-- VEXOGEN ERP - HOSTINGER CLEAN SETUP
-- ============================================
-- Use this single file for a fresh live database.
-- Paste/import this in Hostinger phpMyAdmin after selecting the database.
--
-- This will drop existing Vexogen ERP tables and create a clean database.
-- 
-- LOGIN:    admin@vexogen.in
-- PASSWORD: admin123
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS communications;
DROP TABLE IF EXISTS project_comments;
DROP TABLE IF EXISTS project_revisions;
DROP TABLE IF EXISTS project_approvals;
DROP TABLE IF EXISTS task_checklists;
DROP TABLE IF EXISTS task_time_logs;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS quotation_items;
DROP TABLE IF EXISTS quotations;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS calendar_events;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS company_settings;
DROP TABLE IF EXISTS invoice_sequences;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','designer','developer','marketing','accounts','client') NOT NULL DEFAULT 'designer',
    avatar VARCHAR(10) NULL,
    phone VARCHAR(20) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE company_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL DEFAULT 'Vexogen',
    tagline VARCHAR(200) NULL,
    email VARCHAR(180) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    gst_number VARCHAR(20) NULL,
    website VARCHAR(200) NULL,
    logo_path VARCHAR(500) NULL,
    signature_path VARCHAR(500) NULL,
    bank_name VARCHAR(120) NULL,
    bank_account VARCHAR(40) NULL,
    bank_ifsc VARCHAR(20) NULL,
    upi_id VARCHAR(100) NULL,
    smtp_host VARCHAR(200) NULL,
    smtp_port SMALLINT NOT NULL DEFAULT 587,
    smtp_user VARCHAR(180) NULL,
    smtp_pass VARCHAR(255) NULL,
    smtp_encryption VARCHAR(10) NULL DEFAULT 'tls',
    backup_token VARCHAR(64) NULL,
    notify_payment_overdue TINYINT(1) NOT NULL DEFAULT 1,
    notify_deadline TINYINT(1) NOT NULL DEFAULT 1,
    notify_task_assigned TINYINT(1) NOT NULL DEFAULT 1,
    financial_year_start TINYINT NOT NULL DEFAULT 4,
    invoice_prefix VARCHAR(20) NOT NULL DEFAULT 'INV',
    quotation_prefix VARCHAR(20) NOT NULL DEFAULT 'QUO',
    project_prefix VARCHAR(20) NOT NULL DEFAULT 'PRJ',
    default_gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    invoice_terms TEXT NULL,
    quotation_terms TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE invoice_sequences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('invoice','quotation','project') NOT NULL,
    year SMALLINT NOT NULL,
    last_number INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY uk_type_year (type, year)
) ENGINE=InnoDB;

CREATE TABLE employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    employee_code VARCHAR(20) NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NULL,
    phone VARCHAR(20) NULL,
    designation VARCHAR(120) NULL,
    department VARCHAR(80) NULL,
    skills JSON NULL,
    salary DECIMAL(12,2) NULL,
    join_date DATE NULL,
    address TEXT NULL,
    emergency_contact VARCHAR(120) NULL,
    documents JSON NULL,
    status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(120) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(180) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    gst_number VARCHAR(20) NULL,
    industry VARCHAR(80) NULL,
    website VARCHAR(200) NULL,
    notes TEXT NULL,
    tags VARCHAR(500) NULL,
    status ENUM('active','inactive','follow_up','lead') NOT NULL DEFAULT 'active',
    outstanding_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_company (company_name),
    FULLTEXT idx_search (company_name, contact_person, email, phone)
) ENGINE=InnoDB;

CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_code VARCHAR(30) NOT NULL UNIQUE,
    client_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(60) NOT NULL,
    description TEXT NULL,
    start_date DATE NULL,
    expected_delivery DATE NULL,
    priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    assigned_employee_id INT UNSIGNED NULL,
    estimated_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
    selling_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    advance DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    balance DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    profit DECIMAL(14,2) GENERATED ALWAYS AS (selling_price - estimated_cost) STORED,
    status ENUM(
        'lead','discussion','quotation_sent','advance_received','planning',
        'design','development','review','revision','final_approval',
        'completed','delivered','closed'
    ) NOT NULL DEFAULT 'lead',
    completion_percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
    quotation_id INT UNSIGNED NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_client (client_id),
    INDEX idx_delivery (expected_delivery),
    FULLTEXT idx_search (project_code, name, description)
) ENGINE=InnoDB;

CREATE TABLE tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    status ENUM('todo','in_progress','review','done') NOT NULL DEFAULT 'todo',
    priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    assigned_to INT UNSIGNED NULL,
    due_date DATE NULL,
    estimated_hours DECIMAL(6,2) NULL,
    actual_hours DECIMAL(6,2) NULL DEFAULT 0,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_due (due_date),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB;

CREATE TABLE task_checklists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    is_done TINYINT(1) NOT NULL DEFAULT 0,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE task_time_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    hours DECIMAL(6,2) NOT NULL,
    log_date DATE NOT NULL,
    notes VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quotations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_number VARCHAR(30) NOT NULL UNIQUE,
    client_id INT UNSIGNED NOT NULL,
    subject VARCHAR(200) NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    terms TEXT NULL,
    valid_until DATE NULL,
    status ENUM('draft','sent','accepted','rejected','expired','converted') NOT NULL DEFAULT 'draft',
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE quotation_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT UNSIGNED NOT NULL,
    service_name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    rate DECIMAL(14,2) NOT NULL DEFAULT 0,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    client_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    received_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    pending_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('draft','sent','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
    billing_address TEXT NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_due (due_date)
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT UNSIGNED NOT NULL,
    service_name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    rate DECIMAL(14,2) NOT NULL DEFAULT 0,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT UNSIGNED NULL,
    client_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NULL,
    amount DECIMAL(14,2) NOT NULL,
    payment_stage ENUM('advance','25','50','75','final','other') NOT NULL DEFAULT 'other',
    payment_method ENUM('upi','neft','rtgs','cash','cheque','card','other') NOT NULL DEFAULT 'upi',
    transaction_id VARCHAR(100) NULL,
    payment_date DATE NOT NULL,
    gst_included TINYINT(1) NOT NULL DEFAULT 1,
    notes TEXT NULL,
    is_overdue TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_date (payment_date),
    INDEX idx_client (client_id)
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('office_rent','salary','internet','fuel','software','printing','equipment','photography','miscellaneous') NOT NULL,
    description VARCHAR(500) NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    expense_date DATE NOT NULL,
    paid_via ENUM('bank_transfer','upi','cash','credit_card','auto_debit','cheque') NOT NULL DEFAULT 'bank_transfer',
    receipt_path VARCHAR(500) NULL,
    employee_id INT UNSIGNED NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_date (expense_date)
) ENGINE=InnoDB;

CREATE TABLE attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present','absent','half_day','leave','holiday') NOT NULL DEFAULT 'present',
    check_in TIME NULL,
    check_out TIME NULL,
    notes VARCHAR(255) NULL,
    UNIQUE KEY uk_emp_date (employee_id, attendance_date),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE leave_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    leave_type ENUM('casual','sick','earned','unpaid') NOT NULL DEFAULT 'casual',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    approved_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE calendar_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    event_type ENUM('deadline','meeting','payment_reminder','birthday','leave','photo_shoot','video_shoot','printing','other') NOT NULL DEFAULT 'other',
    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    all_day TINYINT(1) NOT NULL DEFAULT 1,
    color VARCHAR(20) NULL DEFAULT 'blue',
    project_id INT UNSIGNED NULL,
    client_id INT UNSIGNED NULL,
    employee_id INT UNSIGNED NULL,
    description TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_date (event_date)
) ENGINE=InnoDB;

CREATE TABLE project_approvals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    stage VARCHAR(80) NOT NULL,
    approved_by VARCHAR(120) NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_revisions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    revision_number INT UNSIGNED NOT NULL DEFAULT 1,
    description TEXT NOT NULL,
    requested_by VARCHAR(120) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE communications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    type ENUM('email','phone','whatsapp','meeting','note') NOT NULL DEFAULT 'note',
    subject VARCHAR(200) NULL,
    message TEXT NOT NULL,
    user_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    extension VARCHAR(10) NULL,
    project_id INT UNSIGNED NULL,
    client_id INT UNSIGNED NULL,
    uploaded_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_project (project_id),
    INDEX idx_client (client_id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT UNSIGNED NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- INSERT ADMIN USER
-- Email:    admin@vexogen.in
-- Password: admin123
-- ============================================
INSERT INTO users (name, email, password, role, avatar, phone) VALUES
('Admin', 'admin@vexogen.in', '$2y$10$5cvH4Dmck1Bi32w6tHOe7uRD.rHar/EPfGacw8J3Symfu68JmwH0i', 'admin', 'AD', '+91 98400 00001');

INSERT INTO company_settings (company_name, tagline, email, phone, address, city, state, pincode, gst_number, website, bank_name, bank_account, bank_ifsc, invoice_terms, quotation_terms) VALUES
('Vexogen', 'Digital Agency & Creative Studio', 'hello@vexogen.in', '+91 98400 00000', 'Salem, Tamil Nadu 636001, India', 'Salem', 'Tamil Nadu', '636001', '33AABCV1234R1Z5', 'www.vexogen.in', 'HDFC Bank', '50200123456789', 'HDFC0001234', 'Payment due within 7 days. Late payments attract 2% monthly interest.', 'Quotation valid for 15 days from issue date.');

INSERT INTO invoice_sequences (type, year, last_number) VALUES
('invoice', 2026, 0), ('quotation', 2026, 0), ('project', 2026, 0);

INSERT INTO activity_logs (user_id, action, entity_type, description, created_at) VALUES
(1, 'login', 'user', 'Admin logged in', NOW());
