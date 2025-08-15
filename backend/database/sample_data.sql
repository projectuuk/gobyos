-- Sample data for Fio Trans Cargo Website
-- Run this after importing schema_clean.sql

-- Insert default categories first
INSERT INTO categories (name, slug, description) VALUES
('News', 'news', 'Latest news and updates'),
('Tips', 'tips', 'Shipping tips and guides'),
('Company Updates', 'company-updates', 'Company news and announcements');

-- Insert default pages
INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
('Home', 'home', '<h1>Welcome to Fio Trans Cargo</h1><p>Your trusted shipping partner for all of Indonesia.</p>', 'Fio Trans Cargo - Jasa Pengiriman Barang Terpercaya', 'Fio Trans Cargo menyediakan layanan pengiriman barang ke seluruh Indonesia dengan aman, cepat, dan terpercaya.');

INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
('About Us', 'about-us', '<h1>About Fio Trans Cargo</h1><p>We are a leading shipping company in Indonesia.</p>', 'About Fio Trans Cargo', 'Learn more about Fio Trans Cargo, your trusted shipping partner.');

INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
('Services', 'services', '<h1>Our Services</h1><p>We offer various shipping services including land, sea, and air transport.</p>', 'Our Services - Fio Trans Cargo', 'Discover our comprehensive shipping services for all your logistics needs.');

INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES
('Contact', 'contact', '<h1>Contact Us</h1><p>Get in touch with us for all your shipping needs.</p>', 'Contact Fio Trans Cargo', 'Contact Fio Trans Cargo for reliable shipping services across Indonesia.');

-- Insert sample posts
INSERT INTO posts (title, slug, content, excerpt, category_id) VALUES
('Welcome to Our New Website', 'welcome-to-our-new-website', '<p>We are excited to launch our new website with improved features and better user experience.</p>', 'We are excited to launch our new website with improved features.', 3);

INSERT INTO posts (title, slug, content, excerpt, category_id) VALUES
('Shipping Tips for Safe Delivery', 'shipping-tips-for-safe-delivery', '<p>Here are some important tips to ensure your packages are delivered safely.</p>', 'Important tips to ensure your packages are delivered safely.', 2);

-- Insert settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'Fio Trans Cargo'),
('site_description', 'Your trusted shipping partner for all of Indonesia'),
('contact_phone', '+62-123-456-7890'),
('contact_email', 'info@fiotranscargo.com'),
('contact_address', 'Jakarta, Indonesia');

-- Create default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@fiotranscargo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default SEO data for homepage
INSERT INTO seo_meta (page_type, page_id, meta_title, meta_description, meta_keywords, og_title, og_description, canonical_url, robots) VALUES
('home', 1, 'Fio Trans Cargo - Jasa Pengiriman Barang Terpercaya ke Seluruh Indonesia', 'Fio Trans Cargo menyediakan layanan pengiriman barang ke seluruh Indonesia dengan aman, cepat, dan terpercaya. Spesialis pengiriman luar pulau dengan harga terjangkau.', 'jasa pengiriman, ekspedisi, cargo, pengiriman barang, logistik, trucking, kapal roro, container', 'Fio Trans Cargo - Jasa Pengiriman Terpercaya', 'Layanan pengiriman barang ke seluruh Indonesia dengan keamanan terjamin dan harga terjangkau', '/', 'index,follow');

