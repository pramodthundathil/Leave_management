-- Insert sample users
INSERT INTO Users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- Password: password
('Approver User', 'approver@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'approver'), -- Password: password
('Employee User', 'employee@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'); -- Password: password

-- Insert sample leave types
INSERT INTO LeaveTypes (leave_type_name) VALUES
('Sick Leave'),
('Casual Leave'),
('Annual Leave');

-- Insert sample leave balances
INSERT INTO LeaveBalances (user_id, leave_type_id, balance) VALUES
(3, 1, 10), -- Employee User: 10 Sick Leave days
(3, 2, 15), -- Employee User: 15 Casual Leave days
(3, 3, 20); -- Employee User: 20 Annual Leave days

-- Insert sample leave applications
INSERT INTO LeaveApplications (user_id, leave_type_id, start_date, end_date, reason, status) VALUES
(3, 1, '2023-10-01', '2023-10-03', 'Feeling unwell', 'Approved'),
(3, 2, '2023-10-05', '2023-10-06', 'Family event', 'Pending');