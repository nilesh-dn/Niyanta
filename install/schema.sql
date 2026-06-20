-- Niyanta core schema. Executed by the installation wizard.
-- Engine InnoDB, utf8mb4. Statements are split on ';' by the installer.

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(128) NOT NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(128) NOT NULL UNIQUE,
    description VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    theme_pref ENUM('light','dark') NOT NULL DEFAULT 'light',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(128) NOT NULL PRIMARY KEY,
    `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS palettes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    `primary` VARCHAR(32) NOT NULL,
    secondary VARCHAR(32) NOT NULL,
    accent VARCHAR(32) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(128) NOT NULL UNIQUE,
    name VARCHAR(191) NOT NULL,
    version VARCHAR(32) NOT NULL DEFAULT '1.0',
    author VARCHAR(191) NOT NULL DEFAULT '',
    description VARCHAR(255) NOT NULL DEFAULT '',
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    installed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(128) NOT NULL,
    context VARCHAR(255) NOT NULL DEFAULT '',
    ip VARCHAR(64) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default roles (hierarchy: super_admin > manager > employee)
INSERT IGNORE INTO roles (id, slug, name, is_system) VALUES
    (1, 'super_admin', 'Super Admin', 1),
    (2, 'manager', 'Manager', 1),
    (3, 'employee', 'Employee', 1);

-- Core permission slugs. Plugins register more at activation time.
INSERT IGNORE INTO permissions (slug, description) VALUES
    ('manage_plugins',  'Install, activate and remove plugins'),
    ('manage_branding', 'Manage company branding and logo'),
    ('manage_palettes', 'Manage colour palettes'),
    ('manage_theme',    'Manage theme settings'),
    ('manage_settings', 'Access system settings'),
    ('view_logs',       'View system activity logs'),
    ('manage_integrations', 'Manage external integrations'),
    ('manage_backups',  'Manage backups');

-- Default colour palettes (palette 1 active).
INSERT IGNORE INTO palettes (id, name, `primary`, secondary, accent, is_active) VALUES
    (1, 'Palette 1', '#0B1F4D', '#6EC1FF', '#FFD84D', 1),
    (2, 'Palette 2', '#1A237E', '#64B5F6', '#FFC107', 0),
    (3, 'Palette 3', '#003366', '#66CCFF', '#FFCC00', 0);
