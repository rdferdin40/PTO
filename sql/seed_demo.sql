-- ========================================
-- PTO Manager - Demo/Seed Data
-- ========================================

-- Insert demo company
INSERT INTO companies (name, slug, timezone, default_locale, default_theme, max_future_year) VALUES
('Acme Corporation', 'acme', 'America/New_York', 'en', 'light', 2026);

SET @company_id = LAST_INSERT_ID();

-- Insert departments
INSERT INTO departments (company_id, name, include_bank_holidays) VALUES
(@company_id, 'Engineering', 1),
(@company_id, 'Human Resources', 1),
(@company_id, 'Sales', 1);

SET @dept_eng = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'Engineering');
SET @dept_hr = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'Human Resources');
SET @dept_sales = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'Sales');

-- Insert users (password for all: Password123!)
-- Password hash generated via: password_hash('Password123!', PASSWORD_DEFAULT)
INSERT INTO users (company_id, department_id, email, password_hash, first_name, last_name, role, locale, is_active, ical_token) VALUES
(@company_id, NULL, 'admin@acme.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'en', 1, SHA2(CONCAT('admin@acme.test', NOW()), 256)),
(@company_id, @dept_eng, 'john.doe@acme.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'manager', 'en', 1, SHA2(CONCAT('john.doe@acme.test', NOW()), 256)),
(@company_id, @dept_eng, 'jane.smith@acme.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'employee', 'en', 1, SHA2(CONCAT('jane.smith@acme.test', NOW()), 256)),
(@company_id, @dept_hr, 'alice.johnson@acme.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Johnson', 'manager', 'en', 1, SHA2(CONCAT('alice.johnson@acme.test', NOW()), 256)),
(@company_id, @dept_sales, 'bob.williams@acme.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob', 'Williams', 'employee', 'es', 1, SHA2(CONCAT('bob.williams@acme.test', NOW()), 256));

SET @user_admin = (SELECT id FROM users WHERE email = 'admin@acme.test' AND company_id = @company_id);
SET @user_john = (SELECT id FROM users WHERE email = 'john.doe@acme.test' AND company_id = @company_id);
SET @user_jane = (SELECT id FROM users WHERE email = 'jane.smith@acme.test' AND company_id = @company_id);
SET @user_alice = (SELECT id FROM users WHERE email = 'alice.johnson@acme.test' AND company_id = @company_id);
SET @user_bob = (SELECT id FROM users WHERE email = 'bob.williams@acme.test' AND company_id = @company_id);

-- Update department managers
UPDATE departments SET manager_user_id = @user_john WHERE id = @dept_eng;
UPDATE departments SET manager_user_id = @user_alice WHERE id = @dept_hr;

-- Insert leave types
INSERT INTO leave_types (company_id, name, color, deducts_allowance, requires_approval, is_active) VALUES
(@company_id, 'Vacation', '#38c172', 1, 1, 1),
(@company_id, 'Sick Leave', '#e3342f', 1, 0, 1),
(@company_id, 'Personal', '#f6993f', 1, 1, 1),
(@company_id, 'Unpaid', '#6574cd', 0, 1, 1);

SET @lt_vacation = (SELECT id FROM leave_types WHERE company_id = @company_id AND name = 'Vacation');
SET @lt_sick = (SELECT id FROM leave_types WHERE company_id = @company_id AND name = 'Sick Leave');
SET @lt_personal = (SELECT id FROM leave_types WHERE company_id = @company_id AND name = 'Personal');

-- Insert default schedule (Mon-Fri, 8 hours/day)
INSERT INTO schedules (company_id, name, is_default) VALUES
(@company_id, 'Standard Week (Mon-Fri)', 1);

SET @schedule_default = LAST_INSERT_ID();

INSERT INTO schedule_days (schedule_id, weekday, is_working_day, hours) VALUES
(@schedule_default, 0, 0, 0.00), -- Sunday
(@schedule_default, 1, 1, 8.00), -- Monday
(@schedule_default, 2, 1, 8.00), -- Tuesday
(@schedule_default, 3, 1, 8.00), -- Wednesday
(@schedule_default, 4, 1, 8.00), -- Thursday
(@schedule_default, 5, 1, 8.00), -- Friday
(@schedule_default, 6, 0, 0.00); -- Saturday

-- Assign default schedule to all users
INSERT INTO user_schedules (user_id, schedule_id) VALUES
(@user_admin, @schedule_default),
(@user_john, @schedule_default),
(@user_jane, @schedule_default),
(@user_alice, @schedule_default),
(@user_bob, @schedule_default);

-- Insert 2025 allowances
INSERT INTO allowances (user_id, year, entitled_days, carried_over_days, manual_adjustment_days) VALUES
(@user_admin, 2025, 25.00, 0.00, 0.00),
(@user_john, 2025, 20.00, 2.00, 0.00),
(@user_jane, 2025, 15.00, 0.00, 0.00),
(@user_alice, 2025, 20.00, 0.00, 0.00),
(@user_bob, 2025, 15.00, 0.00, 1.00);

-- Insert 2025 holidays
INSERT INTO holidays (company_id, date, name, is_bank_holiday, is_blackout) VALUES
(@company_id, '2025-01-01', 'New Year\'s Day', 1, 0),
(@company_id, '2025-07-04', 'Independence Day', 1, 0),
(@company_id, '2025-12-25', 'Christmas Day', 1, 0),
(@company_id, '2025-12-15', 'Company Blackout Period Start', 0, 1),
(@company_id, '2025-12-31', 'New Year\'s Eve', 0, 1);

-- Insert sample leaves
INSERT INTO leaves (company_id, user_id, leave_type_id, start_date, end_date, start_half, end_half, days, status, request_date, manager_id, manager_comment, employee_comment) VALUES
(@company_id, @user_jane, @lt_vacation, '2025-03-10', '2025-03-14', 'full', 'full', 5.00, 'approved', NOW(), @user_john, 'Approved. Have a great vacation!', 'Family trip'),
(@company_id, @user_bob, @lt_sick, '2025-02-05', '2025-02-05', 'full', 'full', 1.00, 'approved', NOW(), @user_alice, 'Feel better soon.', 'Not feeling well'),
(@company_id, @user_jane, @lt_personal, '2025-04-15', '2025-04-15', 'am', 'am', 0.50, 'pending', NOW(), NULL, NULL, 'Doctor appointment');

-- Insert settings
INSERT INTO settings (company_id, `key`, value) VALUES
(@company_id, 'block_blackout_requests', '1'),
(@company_id, 'allow_self_registration', '0');

-- Insert audit log
INSERT INTO audit_logs (company_id, user_id, action, details) VALUES
(@company_id, @user_admin, 'SEED_DATA', 'Demo data seeded successfully');
