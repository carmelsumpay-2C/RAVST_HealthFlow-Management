/*
# =============================================
# Project: Hospital Queue Management System
# Authors:  [ Jusminne Alipio    ], [2410050]
            [ Angel Charm Rabino ], [2411057]
            [ Carmel Sumpay      ], [2411111]
            [Alby Avery Tolentino], [2411125]
            [ Benjie Villarin    ], [2411173]
# Date: [December 16, 2025]
# Description:
    The HealthFlow Management System is based on the web-based GUI developed in HTML,
CSS, and JavaScript. Patients (kiosk and queue display) and staff (admin and front desk)
have separate interfaces, thus making the interface straightforward and easy to use 
depending on the user roles.
# =============================================
*/

<?php
// db.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "healthflow";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    // Create tables if they don't exist
    createTablesIfNotExist($conn);
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    die("Database connection error. Please check your configuration.");
}

function createTablesIfNotExist($conn) {
    $tables = [
        "patients" => "CREATE TABLE IF NOT EXISTS patients (
            id INT PRIMARY KEY AUTO_INCREMENT,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            birthdate DATE,
            gender ENUM('Male', 'Female', 'Other'),
            contact VARCHAR(50),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "appointments" => "CREATE TABLE IF NOT EXISTS appointments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            patient_id INT NOT NULL,
            service ENUM('CHECK-UP', 'FIRST AID', 'REQUEST MEDICINE', 'EMERGENCY ASSISTANCE') NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
        )",
        
        "bills" => "CREATE TABLE IF NOT EXISTS bills (
            id INT PRIMARY KEY AUTO_INCREMENT,
            patient_id INT NOT NULL,
            appointment_id INT,
            amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
        )",
        
        "tokens" => "CREATE TABLE IF NOT EXISTS tokens (
            id INT PRIMARY KEY AUTO_INCREMENT,
            code VARCHAR(20) UNIQUE NOT NULL,
            service ENUM('CHECK-UP', 'FIRST AID', 'REQUEST MEDICINE', 'EMERGENCY ASSISTANCE') NOT NULL,
            status ENUM('waiting', 'serving', 'done', 'cancelled') DEFAULT 'waiting',
            linked TINYINT(1) DEFAULT 0,
            started_at TIMESTAMP NULL,
            ended_at TIMESTAMP NULL,
            serving_seconds INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $tableName => $createSQL) {
        // Check if table exists
        $result = $conn->query("SHOW TABLES LIKE '$tableName'");
        if ($result->num_rows == 0) {
            if (!$conn->query($createSQL)) {
                error_log("Failed to create table $tableName: " . $conn->error);
            }
        }
    }
}
?>