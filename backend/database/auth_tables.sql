-- Authentication and Authorization Tables for Fio Trans Cargo

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `email` varchar(100) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    `role` enum('subscriber', 'contributor', 'author', 'editor', 'admin', 'super_admin') NOT NULL DEFAULT 'subscriber',
    `status` enum('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `first_name` varchar(50) DEFAULT NULL,
    `last_name` varchar(50) DEFAULT NULL,
    `avatar` varchar(255) DEFAULT NULL,
    `bio` text DEFAULT NULL,
    `website` varchar(255) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` timestamp NULL DEFAULT NULL,
    `email_verified` tinyint(1) NOT NULL DEFAULT 0,
    `email_verification_token` varchar(64) DEFAULT NULL,
    `password_reset_token` varchar(64) DEFAULT NULL,
    `password_reset_expires` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_username` (`username`),
    KEY `idx_email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `session_token` varchar(64) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_session` (`user_id`, `session_token`),
    KEY `idx_session_token` (`session_token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_last_activity` (`last_activity`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts table (for brute force protection)
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) DEFAULT NULL,
    `ip_address` varchar(45) NOT NULL,
    `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `success` tinyint(1) NOT NULL DEFAULT 0,
    `user_agent` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ip_time` (`ip_address`, `attempt_time`),
    KEY `idx_username_time` (`username`, `attempt_time`),
    KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User permissions table (for granular permissions)
CREATE TABLE IF NOT EXISTS `user_permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `permission` varchar(50) NOT NULL,
    `granted` tinyint(1) NOT NULL DEFAULT 1,
    `granted_by` int(11) DEFAULT NULL,
    `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_permission` (`user_id`, `permission`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_permission` (`permission`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`granted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role permissions table (default permissions for roles)
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `role` enum('subscriber', 'contributor', 'author', 'editor', 'admin', 'super_admin') NOT NULL,
    `permission` varchar(50) NOT NULL,
    `description` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_role_permission` (`role`, `permission`),
    KEY `idx_role` (`role`),
    KEY `idx_permission` (`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User activity log
CREATE TABLE IF NOT EXISTS `user_activity_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `resource_type` varchar(50) DEFAULT NULL,
    `resource_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text DEFAULT NULL,
    `details` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_resource` (`resource_type`, `resource_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default role permissions
INSERT INTO `role_permissions` (`role`, `permission`, `description`) VALUES
-- Subscriber permissions
('subscriber', 'read_posts', 'Can read published posts'),
('subscriber', 'comment_posts', 'Can comment on posts'),

-- Contributor permissions
('contributor', 'read_posts', 'Can read published posts'),
('contributor', 'comment_posts', 'Can comment on posts'),
('contributor', 'create_posts', 'Can create posts (draft only)'),
('contributor', 'edit_own_posts', 'Can edit own posts'),

-- Author permissions
('author', 'read_posts', 'Can read published posts'),
('author', 'comment_posts', 'Can comment on posts'),
('author', 'create_posts', 'Can create posts'),
('author', 'edit_own_posts', 'Can edit own posts'),
('author', 'publish_own_posts', 'Can publish own posts'),
('author', 'delete_own_posts', 'Can delete own posts'),
('author', 'upload_files', 'Can upload files'),

-- Editor permissions
('editor', 'read_posts', 'Can read all posts'),
('editor', 'comment_posts', 'Can comment on posts'),
('editor', 'create_posts', 'Can create posts'),
('editor', 'edit_posts', 'Can edit all posts'),
('editor', 'publish_posts', 'Can publish all posts'),
('editor', 'delete_posts', 'Can delete posts'),
('editor', 'manage_categories', 'Can manage categories'),
('editor', 'upload_files', 'Can upload files'),
('editor', 'moderate_comments', 'Can moderate comments'),

-- Admin permissions
('admin', 'read_posts', 'Can read all posts'),
('admin', 'comment_posts', 'Can comment on posts'),
('admin', 'create_posts', 'Can create posts'),
('admin', 'edit_posts', 'Can edit all posts'),
('admin', 'publish_posts', 'Can publish all posts'),
('admin', 'delete_posts', 'Can delete posts'),
('admin', 'manage_categories', 'Can manage categories'),
('admin', 'upload_files', 'Can upload files'),
('admin', 'moderate_comments', 'Can moderate comments'),
('admin', 'manage_users', 'Can manage users'),
('admin', 'view_analytics', 'Can view analytics'),
('admin', 'manage_settings', 'Can manage site settings'),

-- Super Admin permissions (all permissions)
('super_admin', 'read_posts', 'Can read all posts'),
('super_admin', 'comment_posts', 'Can comment on posts'),
('super_admin', 'create_posts', 'Can create posts'),
('super_admin', 'edit_posts', 'Can edit all posts'),
('super_admin', 'publish_posts', 'Can publish all posts'),
('super_admin', 'delete_posts', 'Can delete posts'),
('super_admin', 'manage_categories', 'Can manage categories'),
('super_admin', 'upload_files', 'Can upload files'),
('super_admin', 'moderate_comments', 'Can moderate comments'),
('super_admin', 'manage_users', 'Can manage users'),
('super_admin', 'view_analytics', 'Can view analytics'),
('super_admin', 'manage_settings', 'Can manage site settings'),
('super_admin', 'system_admin', 'Full system administration access');

-- Create default admin user (password: Admin123!)
-- Note: This should be changed immediately after installation
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `status`, `first_name`, `last_name`) VALUES
('admin', 'admin@fiotranscargo.com', '$argon2id$v=19$m=65536,t=4,p=3$YWJjZGVmZ2hpams$8K6rKZbKjTWKQeUuC1J8K5fN2sGvYx7wX9mP4qR3sT0', 'admin', 'active', 'System', 'Administrator');

-- Create indexes for better performance
CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_sessions_user_token ON user_sessions(user_id, session_token);
CREATE INDEX idx_login_attempts_ip_time ON login_attempts(ip_address, attempt_time);
CREATE INDEX idx_activity_user_action ON user_activity_log(user_id, action);

