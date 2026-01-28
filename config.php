<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lumeren_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize database tables
function initializeDatabase() {
    try {
        // First connect without database selection to create the database
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        
        // Create customers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create appointments table
        $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            service_type VARCHAR(100),
            status VARCHAR(20) DEFAULT 'scheduled',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )");
        
        // Create prescriptions table
        $pdo->exec("CREATE TABLE IF NOT EXISTS prescriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            prescription_date DATE NOT NULL,
            od_sphere DECIMAL(5,2),
            od_cylinder DECIMAL(5,2),
            od_axis INT,
            os_sphere DECIMAL(5,2),
            os_cylinder DECIMAL(5,2),
            os_axis INT,
            pd INT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )");
        
        // Create products table
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(50),
            brand VARCHAR(50),
            price DECIMAL(10,2),
            stock INT DEFAULT 0,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create sales table
        $pdo->exec("CREATE TABLE IF NOT EXISTS sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            product_id INT,
            quantity INT DEFAULT 1,
            total_price DECIMAL(10,2),
            sale_date DATE NOT NULL,
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");
        
        // Insert sample data
        insertSampleData($pdo);
        
        return true;
    } catch(PDOException $e) {
        return "Database initialization failed: " . $e->getMessage();
    }
}

function insertSampleData($pdo) {
    // Check if data already exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
    if ($stmt->fetchColumn() > 0) {
        return; // Data already exists
    }
    
    // Insert sample customers
    $pdo->exec("INSERT INTO customers (name, email, phone, address) VALUES
        ('John Smith', 'john.smith@email.com', '555-0101', '123 Main St, City'),
        ('Sarah Johnson', 'sarah.j@email.com', '555-0102', '456 Oak Ave, Town'),
        ('Michael Brown', 'mbrown@email.com', '555-0103', '789 Pine Rd, Village'),
        ('Emily Davis', 'emily.d@email.com', '555-0104', '321 Elm St, City'),
        ('David Wilson', 'dwilson@email.com', '555-0105', '654 Maple Dr, Town')
    ");
    
    // Insert sample appointments
    $pdo->exec("INSERT INTO appointments (customer_id, appointment_date, appointment_time, service_type, status) VALUES
        (1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'Eye Exam', 'scheduled'),
        (2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:30:00', 'Contact Lens Fitting', 'scheduled'),
        (3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '11:00:00', 'Frame Selection', 'scheduled'),
        (4, CURDATE(), '09:00:00', 'Eye Exam', 'completed'),
        (5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00', 'Prescription Glasses', 'completed')
    ");
    
    // Insert sample prescriptions
    $pdo->exec("INSERT INTO prescriptions (customer_id, prescription_date, od_sphere, od_cylinder, od_axis, os_sphere, os_cylinder, os_axis, pd) VALUES
        (1, CURDATE(), -2.50, -0.75, 180, -2.75, -0.50, 5, 63),
        (4, CURDATE(), -1.25, -0.25, 90, -1.50, 0.00, 0, 62)
    ");
    
    // Insert sample products
    $pdo->exec("INSERT INTO products (name, category, brand, price, stock, description) VALUES
        ('Classic Metal Frame', 'Frames', 'VisionPro', 129.99, 1, 'Durable metal frame with adjustable nose pads'),
        ('Designer Acetate Frame', 'Frames', 'StyleOptix', 189.99, 2, 'Premium acetate frame in various colors'),
        ('Anti-Blue Light Lenses', 'Lenses', 'ClearVision', 89.99, 50, 'Blue light blocking lenses for digital eye strain'),
        ('Progressive Lenses', 'Lenses', 'FocusMax', 249.99, 30, 'Seamless multifocal lenses'),
        ('Daily Contact Lenses (30pk)', 'Contact Lenses', 'ComfortLens', 34.99, 100, 'Daily disposable contact lenses'),
        ('Monthly Contact Lenses (6pk)', 'Contact Lenses', 'ClearView', 44.99, 75, 'Monthly replacement contact lenses'),
        ('Cleaning Solution (360ml)', 'Accessories', 'LensCare', 12.99, 150, 'All-purpose contact lens solution'),
        ('Microfiber Cloth', 'Accessories', 'CleanVision', 5.99, 200, 'Scratch-free cleaning cloth'),
        ('Premium Sunglasses', 'Sunglasses', 'SunShield', 159.99, 20, 'UV400 protection polarized sunglasses'),
        ('Kids Frame - Flexible', 'Frames', 'KidSafe', 79.99, 15, 'Flexible and durable frame for children')
    ");
    
    // Insert sample sales
    $pdo->exec("INSERT INTO sales (customer_id, product_id, quantity, total_price, sale_date, payment_method) VALUES
        (4, 1, 1, 129.99, CURDATE(), 'Credit Card'),
        (4, 3, 1, 89.99, CURDATE(), 'Credit Card'),
        (5, 2, 1, 189.99, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Cash')
    ");
}
