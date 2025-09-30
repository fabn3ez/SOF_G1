<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "healthconnect";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
if (!$conn->select_db($dbname)) {
    $conn->query("CREATE DATABASE $dbname");
    $conn->select_db($dbname);
}

// Create tables if they don't exist
$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    address TEXT,
    role ENUM('patient', 'staff', 'admin') DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("
CREATE TABLE IF NOT EXISTS clinics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    opening_hours VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    clinic_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    appointment_type ENUM('general', 'dental', 'eye_checkup', 'vaccination', 'follow_up', 'emergency') DEFAULT 'general',
    reason TEXT,
    status ENUM('booked', 'confirmed', 'rescheduled', 'cancelled', 'completed', 'no_show') DEFAULT 'booked',
    doctor_name VARCHAR(100),
    notes TEXT,
    duration_minutes INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_clinic_id (clinic_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status)
)");

$conn->query("
CREATE TABLE IF NOT appointment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    reminder_type ENUM('email', 'sms', 'both') DEFAULT 'email',
    reminder_sent_at TIMESTAMP NULL,
    reminder_scheduled_for TIMESTAMP NOT NULL,
    status ENUM('scheduled', 'sent', 'failed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
)");

// Insert sample clinics if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM clinics");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO clinics (name, location, phone, email, opening_hours, description) VALUES 
        ('Main Health Center', '123 Medical Drive, City Center', '(555) 123-4567', 'main@healthconnect.com', 'Mon-Fri: 8AM-8PM, Sat: 9AM-4PM', 'Full-service medical center with emergency care'),
        ('Northside Dental Clinic', '456 Healthcare Ave, North District', '(555) 987-6543', 'dental@healthconnect.com', 'Mon-Sat: 8AM-6PM', 'Specialized dental care and oral surgery'),
        ('Westend Vision Care', '789 Wellness Street, West Area', '(555) 456-7890', 'vision@healthconnect.com', 'Tue-Sat: 9AM-5PM', 'Comprehensive eye care and vision testing'),
        ('Downtown Pediatrics', '321 Child Care Lane, Downtown', '(555) 234-5678', 'pediatrics@healthconnect.com', 'Mon-Fri: 8AM-6PM', 'Specialized healthcare for children and adolescents'),
        ('Southside Emergency', '654 Emergency Road, South District', '(555) 876-5432', 'emergency@healthconnect.com', '24/7', '24-hour emergency medical services')");
}

// Insert sample appointments if none exist (for testing)
$result = $conn->query("SELECT COUNT(*) as count FROM appointments");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    // First, create a sample user if none exists
    $user_check = $conn->query("SELECT COUNT(*) as count FROM users");
    $user_count = $user_check->fetch_assoc()['count'];
    
    if ($user_count == 0) {
        $hashed_password = password_hash('password123', PASSWORD_BCRYPT);
        $conn->query("INSERT INTO users (name, email, password, phone, date_of_birth, address) VALUES 
            ('John Doe', 'john.doe@example.com', '$hashed_password', '(555) 111-2222', '1990-05-15', '123 Main St, City, State')");
        
        $user_id = $conn->insert_id;
        
        // Insert sample appointments
        $conn->query("INSERT INTO appointments (user_id, clinic_id, appointment_date, appointment_type, reason, status, doctor_name, duration_minutes) VALUES 
            ($user_id, 1, DATE_ADD(NOW(), INTERVAL 2 DAY), 'general', 'Annual health checkup', 'booked', 'Dr. Smith', 45),
            ($user_id, 2, DATE_ADD(NOW(), INTERVAL 5 DAY), 'dental', 'Regular dental cleaning', 'confirmed', 'Dr. Johnson', 30),
            ($user_id, 3, DATE_ADD(NOW(), INTERVAL 1 DAY), 'eye_checkup', 'Vision test and prescription update', 'booked', 'Dr. Brown', 60),
            ($user_id, 1, DATE_SUB(NOW(), INTERVAL 7 DAY), 'follow_up', 'Follow-up for previous treatment', 'completed', 'Dr. Smith', 30),
            ($user_id, 4, DATE_ADD(NOW(), INTERVAL 10 DAY), 'vaccination', 'Flu shot vaccination', 'booked', 'Dr. Wilson', 15)");
    }
}

$conn->close();
?>