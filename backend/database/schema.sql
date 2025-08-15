-- Tables for Fio Trans Cargo Website

-- Pages table
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    excerpt TEXT,
    featured_image VARCHAR(255),
    meta_title VARCHAR(255),
    meta_description TEXT,
    category_id INT,
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL,
    sender_name VARCHAR(255) NOT NULL,
    sender_phone VARCHAR(20) NOT NULL,
    sender_address TEXT,
    receiver_name VARCHAR(255) NOT NULL,
    receiver_phone VARCHAR(20) NOT NULL,
    receiver_address TEXT,
    service_type VARCHAR(100) NOT NULL,
    item_description TEXT,
    weight VARCHAR(50),
    dimensions VARCHAR(100),
    estimated_cost DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'confirmed', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table (for admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default data
INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
("Home", "home", "<h1>Welcome to Fio Trans Cargo</h1><p>Your trusted shipping partner for all of Indonesia.</p>", "Fio Trans Cargo - Jasa Pengiriman Barang Terpercaya", "Fio Trans Cargo menyediakan layanan pengiriman barang ke seluruh Indonesia dengan aman, cepat, dan terpercaya.");

INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
("About Us", "about-us", "<h1>About Fio Trans Cargo</h1><p>We are a leading shipping company in Indonesia.</p>", "About Fio Trans Cargo", "Learn more about Fio Trans Cargo, your trusted shipping partner.");

INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
("Services", "services", "<h1>Our Services</h1><p>We offer various shipping services including land, sea, and air transport.</p>", "Our Services - Fio Trans Cargo", "Discover our comprehensive shipping services for all your logistics needs.");

INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
("Contact", "contact", "<h1>Contact Us</h1><p>Get in touch with us for all your shipping needs.</p>", "Contact Fio Trans Cargo", "Contact Fio Trans Cargo for reliable shipping services across Indonesia.");

INSERT INTO categories (name, slug, description) VALUES
('News', 'news', 'Latest news and updates'),
('Tips', 'tips', 'Shipping tips and guides'),
('Company Updates', 'company-updates', 'Company news and announcements');

INSERT INTO posts (title, slug, content, excerpt, category_id) VALUES
('Welcome to Our New Website', 'welcome-to-our-new-website', '<p>We are excited to launch our new website with improved features and better user experience.</p>', 'We are excited to launch our new website with improved features.', 3),
('Shipping Tips for Safe Delivery', 'shipping-tips-for-safe-delivery', '<p>Here are some important tips to ensure your packages are delivered safely.</p>', 'Important tips to ensure your packages are delivered safely.', 2);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'Fio Trans Cargo'),
('site_description', 'Your trusted shipping partner for all of Indonesia'),
('contact_phone', '+62-123-456-7890'),
('contact_email', 'info@fiotranscargo.com'),
('contact_address', 'Jakarta, Indonesia');

-- Create default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@fiotranscargo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


-- SEO Meta table
CREATE TABLE IF NOT EXISTS seo_meta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_type ENUM('page', 'post', 'category', 'home') NOT NULL,
    page_id INT NOT NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(255),
    canonical_url VARCHAR(255),
    robots VARCHAR(100) DEFAULT 'index,follow',
    schema_markup TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_page (page_type, page_id)
);

-- Analytics Visits table
CREATE TABLE IF NOT EXISTS analytics_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_url VARCHAR(500),
    page_title VARCHAR(255),
    referrer VARCHAR(500),
    session_id VARCHAR(100),
    browser VARCHAR(50),
    operating_system VARCHAR(50),
    device_type VARCHAR(20),
    visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_visit_time (visit_time),
    INDEX idx_page_url (page_url(255)),
    INDEX idx_ip_address (ip_address)
);

-- Insert default SEO data for homepage
INSERT INTO seo_meta (page_type, page_id, meta_title, meta_description, meta_keywords, og_title, og_description, canonical_url, robots) VALUES
('home', 1, 'Fio Trans Cargo - Jasa Pengiriman Barang Terpercaya ke Seluruh Indonesia', 'Fio Trans Cargo menyediakan layanan pengiriman barang ke seluruh Indonesia dengan aman, cepat, dan terpercaya. Spesialis pengiriman luar pulau dengan harga terjangkau.', 'jasa pengiriman, ekspedisi, cargo, pengiriman barang, logistik, trucking, kapal roro, container', 'Fio Trans Cargo - Jasa Pengiriman Terpercaya', 'Layanan pengiriman barang ke seluruh Indonesia dengan keamanan terjamin dan harga terjangkau', '/', 'index,follow');

