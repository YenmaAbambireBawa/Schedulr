<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=schedulr_db;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Create course_registrations table
    $db->exec("
        CREATE TABLE IF NOT EXISTS course_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            student_email VARCHAR(255) NOT NULL,
            registration_data JSON NOT NULL,
            email_verified TINYINT(1) DEFAULT 0,
            verification_token VARCHAR(64) NULL,
            verification_sent_at TIMESTAMP NULL,
            verified_at TIMESTAMP NULL,
            mycamu_email VARCHAR(255) NULL,
            mycamu_password_encrypted TEXT NULL,
            status ENUM('pending', 'verified', 'processing', 'completed', 'failed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_token (verification_token),
            INDEX idx_user (user_id)
        )
    ");
    
    echo "Database setup completed successfully!";
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>