-- Vexogen CRM Seed Data
-- Import this file after selecting your existing database in phpMyAdmin.

INSERT INTO company_settings (company_name, tagline, email, phone, address, city, state, pincode, gst_number, website, bank_name, bank_account, bank_ifsc, invoice_terms, quotation_terms) VALUES
('Vexogen', 'Digital Agency & Creative Studio', 'hello@vexogen.com', '+91 98400 00000', 'Salem, Tamil Nadu 636001, India', 'Salem', 'Tamil Nadu', '636001', '33AABCV1234R1Z5', 'www.vexogen.com', 'HDFC Bank', '50200123456789', 'HDFC0001234', 'Payment due within 7 days. Late payments attract 2% monthly interest.', 'Quotation valid for 15 days from issue date.');

INSERT INTO users (name, email, password, role, avatar, phone) VALUES
('Admin User', 'admin@vexogen.com', '$2y$10$OHea/C3iimsQDC0dlku9s.a7tngJgE8Il.sKs3Q6eDJACZ0iNfNNq', 'admin', 'AK', '+91 98400 00001'),
('Sowmya Krishnan', 'accounts@vexogen.com', '$2y$10$OHea/C3iimsQDC0dlku9s.a7tngJgE8Il.sKs3Q6eDJACZ0iNfNNq', 'accounts', 'SK', '+91 98400 00002'),
('Rajan Kumar', 'rajan@vexogen.com', '$2y$10$OHea/C3iimsQDC0dlku9s.a7tngJgE8Il.sKs3Q6eDJACZ0iNfNNq', 'designer', 'RK', '+91 98400 00003');

-- password for seeded users: admin123

INSERT INTO employees (user_id, employee_code, name, email, phone, designation, department, skills, salary, join_date, status) VALUES
(3, 'EMP-001', 'Rajan Kumar', 'rajan@vexogen.com', '+91 98400 00003', 'Senior Designer', 'Creative', '["Branding","Packaging"]', 45000, '2022-04-01', 'active'),
(NULL, 'EMP-002', 'Arun Mani', 'arun@vexogen.com', '+91 98400 00004', 'Full Stack Developer', 'Technology', '["Web Dev","UI/UX"]', 55000, '2021-08-15', 'active'),
(NULL, 'EMP-003', 'Priya Suresh', 'priya@vexogen.com', '+91 98400 00005', 'Digital Marketing Lead', 'Marketing', '["SEO","Social Media"]', 42000, '2023-01-10', 'active'),
(NULL, 'EMP-004', 'Vijay Nataraj', 'vijay@vexogen.com', '+91 98400 00006', 'Photographer & Videographer', 'Production', '["Photography","Video"]', 38000, '2022-11-20', 'active'),
(2, 'EMP-005', 'Sowmya Krishnan', 'accounts@vexogen.com', '+91 98400 00002', 'Accounts Manager', 'Finance', '["Finance","GST"]', 40000, '2020-06-01', 'active');

INSERT INTO clients (company_name, contact_person, phone, email, address, industry, website, status, outstanding_balance, created_by) VALUES
('Nexus Brands Pvt Ltd', 'Ramesh Kumar', '+91 98400 12345', 'ramesh@nexusbrands.com', 'Chennai, Tamil Nadu', 'FMCG', 'nexusbrands.com', 'active', 42750, 1),
('Stellar Media Group', 'Ananya Mehta', '+91 98765 43210', 'ananya@stellarmedia.in', 'Chennai, Tamil Nadu', 'Media', 'stellarmedia.in', 'active', 0, 1),
('FreshMart Retail', 'Priya Sharma', '+91 94432 87654', 'priya@freshmart.co.in', 'Coimbatore, Tamil Nadu', 'E-Commerce', 'freshmart.co.in', 'active', 32500, 1),
('Ola Organics', 'Vikram Nair', '+91 90000 11223', 'vikram@olaorganics.com', 'Bangalore, Karnataka', 'FMCG', 'olaorganics.com', 'follow_up', 32500, 1);

INSERT INTO projects (project_code, client_id, name, category, description, start_date, expected_delivery, priority, assigned_employee_id, estimated_cost, selling_price, status, completion_percentage, created_by) VALUES
('PRJ-2026-001', 1, 'Brand Identity', 'branding', 'Complete brand identity package', '2026-01-05', '2026-03-22', 'high', 1, 35000, 95000, 'design', 65, 1),
('PRJ-2026-002', 2, 'E-commerce Website', 'website', 'Full e-commerce build', '2025-11-01', '2026-03-18', 'high', 2, 90000, 180000, 'review', 85, 1),
('PRJ-2026-003', 3, 'Packaging Design', 'packaging', 'Product packaging suite', '2026-01-15', '2026-03-20', 'medium', 1, 22000, 65000, 'revision', 45, 1),
('PRJ-2026-004', 4, 'Product Video', 'video', 'Product launch video', '2025-12-01', '2026-02-28', 'medium', 4, 18000, 45000, 'completed', 100, 1),
('PRJ-2026-005', 1, 'Mobile App — FinTrack', 'mobile_app', 'Finance tracking app', '2026-02-01', '2026-06-30', 'medium', 2, 120000, 250000, 'lead', 5, 1),
('PRJ-2026-006', 3, 'SEO Campaign', 'seo', '6-month SEO retainer', '2026-01-20', '2026-07-20', 'low', 3, 15000, 45000, 'lead', 0, 1);

INSERT INTO tasks (project_id, title, status, priority, assigned_to, due_date, created_by) VALUES
(1, 'Logo variations v3', 'todo', 'high', 1, CURDATE(), 1),
(2, 'Homepage wireframe', 'todo', 'medium', 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 1),
(3, 'Social media content', 'review', 'low', 3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 1),
(4, 'Product photos edit', 'in_progress', 'medium', 4, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 1),
(1, 'Brand guidelines document', 'in_progress', 'medium', 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1);

INSERT INTO quotations (quote_number, client_id, subject, subtotal, gst_rate, gst_amount, total_amount, valid_until, status, created_by) VALUES
('QUO-2026-001', 1, 'Mobile App Development', 296610.17, 18, 53389.83, 350000, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'sent', 1),
('QUO-2026-002', 3, 'Branding + Packaging', 105932.20, 18, 19067.80, 125000, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'draft', 1);

INSERT INTO quotation_items (quotation_id, service_name, description, quantity, rate, amount, position) VALUES
(1, 'Mobile App Development', 'iOS & Android app', 1, 250000, 250000, 0),
(1, 'UI/UX Design', 'App interface design', 1, 46610.17, 46610.17, 1),
(2, 'Branding', 'Logo and identity', 1, 75000, 75000, 0),
(2, 'Packaging Design', '3 SKU packaging', 1, 30932.20, 30932.20, 1);

INSERT INTO invoices (invoice_number, client_id, project_id, invoice_date, due_date, subtotal, gst_rate, gst_amount, total_amount, received_amount, pending_amount, status, created_by) VALUES
('INV-2026-001', 2, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 152542.37, 18, 27457.63, 180000, 180000, 0, 'paid', 1),
('INV-2026-002', 1, 1, DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 80508.47, 18, 14491.53, 95000, 52250, 42750, 'overdue', 1),
('INV-2026-003', 3, 3, DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 55084.75, 18, 9915.25, 65000, 32500, 32500, 'partial', 1);

INSERT INTO invoice_items (invoice_id, service_name, description, quantity, rate, amount, position) VALUES
(1, 'Web Development', 'E-commerce website', 1, 140000, 140000, 0),
(1, 'UI/UX Design', 'Wireframes and mockups', 1, 25000, 25000, 1),
(1, 'SEO Setup', 'On-page SEO', 1, 12542.37, 12542.37, 2),
(2, 'Brand Identity', 'Logo, colors, typography', 1, 75000, 75000, 0),
(2, 'Brand Guidelines', 'PDF brand book', 1, 5508.47, 5508.47, 1),
(3, 'Packaging Design', '3 product packages', 1, 55084.75, 55084.75, 0);

INSERT INTO payments (invoice_id, client_id, project_id, amount, payment_stage, payment_method, transaction_id, payment_date, created_by) VALUES
(1, 2, 2, 90000, 'advance', 'neft', 'NEFT2026001', DATE_SUB(CURDATE(), INTERVAL 30 DAY), 1),
(1, 2, 2, 90000, 'final', 'neft', 'NEFT2026002', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 1),
(2, 1, 1, 28500, 'advance', 'upi', 'UPI2026001', DATE_SUB(CURDATE(), INTERVAL 20 DAY), 1),
(2, 1, 1, 23750, '25', 'upi', 'UPI2026002', DATE_SUB(CURDATE(), INTERVAL 12 DAY), 1),
(3, 3, 3, 32500, 'advance', 'rtgs', 'RTGS2026001', DATE_SUB(CURDATE(), INTERVAL 14 DAY), 1);

INSERT INTO expenses (category, description, amount, expense_date, paid_via, created_by) VALUES
('salary', 'Employee salaries', 78000, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 'bank_transfer', 1),
('office_rent', 'Office rent', 18000, DATE_FORMAT(CURDATE(), '%Y-%m-02'), 'upi', 1),
('software', 'Adobe CC, Figma, Slack', 12400, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'credit_card', 1),
('internet', 'Broadband + mobile', 4500, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'auto_debit', 1),
('equipment', 'Camera lens rental', 8200, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'cash', 1);

INSERT INTO calendar_events (title, event_type, event_date, color, project_id, created_by) VALUES
('Team standup', 'meeting', CURDATE(), 'blue', NULL, 1),
('Deadline: Logo variations', 'deadline', CURDATE(), 'orange', 1, 1),
('Delivery: E-commerce Website', 'deadline', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'orange', 2, 1),
('Product shoot - Ola', 'photo_shoot', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'blue', 4, 1),
('Salary day', 'other', DATE_FORMAT(CURDATE(), '%Y-%m-09'), 'green', NULL, 1);

INSERT INTO notifications (user_id, type, title, message, is_read) VALUES
(1, 'payment_overdue', 'Payment overdue', 'Nexus Brands — ₹42,750 overdue', 0),
(1, 'deadline', 'Deadline tomorrow', 'Project Packaging Redesign due soon', 0),
(1, 'invoice', 'Invoice generated', 'INV-2026-001 paid by Stellar Media', 1);

INSERT INTO invoice_sequences (type, year, last_number) VALUES
('invoice', 2026, 3), ('quotation', 2026, 2), ('project', 2026, 6);

INSERT INTO activity_logs (user_id, action, entity_type, description, created_at) VALUES
(1, 'login', 'user', 'Admin logged in', NOW());
