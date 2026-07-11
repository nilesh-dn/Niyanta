-- Salary & Performance Management schema.
-- Run automatically by the plugin manager on install. This plugin is fully
-- self-contained: it owns all four tables below and never depends on tables
-- from other modules. The optional link to an employee is a soft integer
-- reference (employee_id), not a foreign key, so it works standalone.

CREATE TABLE IF NOT EXISTS salary_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NULL,
    employee_name VARCHAR(191) NOT NULL,
    employee_email VARCHAR(191) NULL,
    apploye_ref VARCHAR(191) NULL,
    hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    monthly_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    overtime_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sp_employee (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS salary_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    period_month VARCHAR(7) NOT NULL,
    worked_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    productive_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    overtime_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    leave_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    expected_hours DECIMAL(8,2) NOT NULL DEFAULT 176,
    source VARCHAR(20) NOT NULL DEFAULT 'manual',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_attendance (profile_id, period_month),
    INDEX idx_att_profile (profile_id),
    CONSTRAINT fk_att_profile FOREIGN KEY (profile_id) REFERENCES salary_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS salary_slips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    period_month VARCHAR(7) NOT NULL,
    hours_worked DECIMAL(8,2) NOT NULL DEFAULT 0,
    overtime_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    overtime_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    base_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    overtime_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    bonuses DECIMAL(12,2) NOT NULL DEFAULT 0,
    deductions DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    net_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','processed','paid') NOT NULL DEFAULT 'pending',
    notes VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_slip (profile_id, period_month),
    INDEX idx_slip_status (status),
    CONSTRAINT fk_slip_profile FOREIGN KEY (profile_id) REFERENCES salary_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS performance_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    period_month VARCHAR(7) NOT NULL,
    attendance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    productive_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    overtime_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    leave_hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    grade VARCHAR(2) NOT NULL DEFAULT 'C',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_perf (profile_id, period_month),
    INDEX idx_perf_grade (grade),
    CONSTRAINT fk_perf_profile FOREIGN KEY (profile_id) REFERENCES salary_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
