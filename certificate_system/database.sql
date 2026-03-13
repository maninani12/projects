Certificate Generation System Database Schema

CREATE DATABASE IF NOT EXISTS certificate_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE certificate_db;

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO roles (role_name, description, permissions) VALUES
('admin', 'Administrator with full access', 'all'),
('user', 'Regular user with limited access', 'view_own_certificates,download_own_certificates');

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default users (password: admin123 and user123)
INSERT INTO users (name, email, password, role_id) VALUES
('Admin User', 'admin@system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Test User', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2);

-- Certificate templates table
CREATE TABLE certificate_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_html TEXT NOT NULL,
    background_image VARCHAR(255),
    variables TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample template
INSERT INTO certificate_templates (template_name, template_html, variables, created_by) VALUES
('Basic Certificate', 
'<div style="text-align:center; padding:50px; font-family:Arial,sans-serif; border:5px solid #1a73e8;">
<h1 style="color:#1a73e8; font-size:48px; margin-bottom:20px;">CERTIFICATE OF ACHIEVEMENT</h1>
<p style="font-size:18px; margin:20px 0;">This is to certify that</p>
<h2 style="color:#333; font-size:36px; margin:20px 0;">{recipient_name}</h2>
<p style="font-size:18px; margin:20px 0;">has successfully completed</p>
<h3 style="color:#1a73e8; font-size:28px; margin:20px 0;">{course_name}</h3>
<p style="font-size:16px; margin:30px 0;">Issued on {issue_date}</p>
<p style="font-size:14px; margin-top:50px;">Certificate ID: {certificate_id}</p>
</div>',
'recipient_name,course_name,issue_date,certificate_id',
1);

-- Certificates table
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    template_id INT NOT NULL,
    certificate_data TEXT,
    file_path VARCHAR(255),
    issued_date DATE NOT NULL,
    issued_by INT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES certificate_templates(id) ON DELETE RESTRICT,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_certificates_user ON certificates(user_id);
CREATE INDEX idx_certificates_number ON certificates(certificate_number);
CREATE INDEX idx_activity_user ON activity_logs(user_id);
