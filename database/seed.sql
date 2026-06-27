-- Vexogen CRM Seed Data
-- Import this file after selecting your existing database in phpMyAdmin.
-- Admin login: admin@vexogen.in / admin123

INSERT INTO company_settings (company_name, tagline, email, phone, address, city, state, pincode, gst_number, website, bank_name, bank_account, bank_ifsc, invoice_terms, quotation_terms) VALUES
('Vexogen', 'Digital Agency & Creative Studio', 'hello@vexogen.in', '+91 98400 00000', 'Salem, Tamil Nadu 636001, India', 'Salem', 'Tamil Nadu', '636001', '33AABCV1234R1Z5', 'www.vexogen.in', 'HDFC Bank', '50200123456789', 'HDFC0001234', 'Payment due within 7 days. Late payments attract 2% monthly interest.', 'Quotation valid for 15 days from issue date.');

INSERT INTO users (name, email, password, role, avatar, phone) VALUES
('Admin', 'admin@vexogen.in', '$2y$10$5cvH4Dmck1Bi32w6tHOe7uRD.rHar/EPfGacw8J3Symfu68JmwH0i', 'admin', 'AD', '+91 98400 00001');

-- password: admin123

INSERT INTO invoice_sequences (type, year, last_number) VALUES
('invoice', 2026, 0), ('quotation', 2026, 0), ('project', 2026, 0);

INSERT INTO activity_logs (user_id, action, entity_type, description, created_at) VALUES
(1, 'login', 'user', 'Admin logged in', NOW());
