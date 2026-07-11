-- Employee Management plugin schema.
-- Run automatically by the plugin manager on install (statements split on ';';
-- lines beginning with '--' are stripped, so keep comments on their own lines).

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_code VARCHAR(20) NULL UNIQUE,
    photo VARCHAR(255) NULL,
    full_name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    phone VARCHAR(40) NULL,
    emergency_contact VARCHAR(120) NULL,
    designation VARCHAR(120) NULL,
    department VARCHAR(120) NULL,
    reporting_manager_id INT NULL,
    date_of_joining DATE NULL,
    employment_type ENUM('full_time','part_time','contract','intern') NOT NULL DEFAULT 'full_time',
    status ENUM('active','inactive','resigned') NOT NULL DEFAULT 'active',
    address TEXT NULL,
    date_of_birth DATE NULL,
    blood_group VARCHAR(8) NULL,
    aadhaar VARCHAR(20) NULL,
    pan VARCHAR(20) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employees_department (department),
    INDEX idx_employees_status (status),
    INDEX idx_employees_dob (date_of_birth),
    CONSTRAINT fk_employees_manager FOREIGN KEY (reporting_manager_id)
        REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    doc_type ENUM('offer_letter','nda','id_proof','resume','other') NOT NULL DEFAULT 'other',
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_documents_employee (employee_id),
    CONSTRAINT fk_documents_employee FOREIGN KEY (employee_id)
        REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
