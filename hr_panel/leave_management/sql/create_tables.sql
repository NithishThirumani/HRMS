-- Create leave types table first
CREATE TABLE IF NOT EXISTS leave_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    leave_type VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default leave types
INSERT INTO leave_types (leave_type, description) VALUES
('Annual Leave', 'Regular annual leave entitlement'),
('Sick Leave', 'Leave for medical reasons'),
('Casual Leave', 'Short-term leave for personal matters'),
('Maternity Leave', 'Leave for female employees during pregnancy and childbirth'),
('Paternity Leave', 'Leave for male employees when their spouse gives birth'),
('Bereavement Leave', 'Leave due to death of immediate family member'),
('Unpaid Leave', 'Leave without pay for extended absences');

SET FOREIGN_KEY_CHECKS = 0;

-- Create leave policies table
CREATE TABLE IF NOT EXISTS leave_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    leave_type VARCHAR(50) NOT NULL,
    max_days INT NOT NULL DEFAULT 0,
    min_service_months INT NOT NULL DEFAULT 0,
    requires_certificate BOOLEAN DEFAULT FALSE,
    gender_restriction ENUM('all', 'male', 'female') DEFAULT 'all',
    is_paid BOOLEAN DEFAULT TRUE,
    monthly_accrual DECIMAL(4,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Drop existing tables if they exist
DROP TABLE IF EXISTS leave_history;
DROP TABLE IF EXISTS leaves;

-- Create leaves table
CREATE TABLE IF NOT EXISTS leaves (
    id INT PRIMARY KEY AUTO_INCREMENT,
    emp_id VARCHAR(20),
    user_name VARCHAR(100),
    type_of_leave VARCHAR(50),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT,
    status ENUM('Pending', 'Recommended', 'Approved', 'Rejected') DEFAULT 'Pending',
    hod_id VARCHAR(20),
    hod_name VARCHAR(100),
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    current_month_ldays INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (emp_id) REFERENCES employees(eid),
    FOREIGN KEY (hod_id) REFERENCES employees(eid)
);

-- Create leave history table
CREATE TABLE IF NOT EXISTS leave_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    leave_id INT,
    action ENUM('Applied', 'Recommended', 'Approved', 'Rejected', 'Cancelled') NOT NULL,
    actor_id VARCHAR(20),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leave_id) REFERENCES leaves(id),
    FOREIGN KEY (actor_id) REFERENCES employees(eid)
);

-- Clear existing leave policies
TRUNCATE TABLE leave_policies;

-- Insert default leave policies
INSERT INTO leave_policies (leave_type, max_days, min_service_months, requires_certificate, gender_restriction, is_paid, monthly_accrual) VALUES
('Annual Leave', 30, 12, FALSE, 'all', TRUE, 2.50),
('Casual Leave', 4, 6, FALSE, 'all', TRUE, 2.00),
('Sick Leave', 15, 3, TRUE, 'all', TRUE, 1.25),
('Sick Leave Half Pay', 30, 3, TRUE, 'all', FALSE, 0.00),
('Maternity Leave', 60, 6, TRUE, 'female', TRUE, 0.00),
('Paternity Leave', 5, 0, TRUE, 'male', TRUE, 0.00),
('Hajj and Umrah Leave', 30, 0, FALSE, 'all', FALSE, 0.00),
('Unpaid', 30, 0, FALSE, 'all', FALSE, 0.00);

SET FOREIGN_KEY_CHECKS = 1; 