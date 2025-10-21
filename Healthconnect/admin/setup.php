<?php
// setup_admin.php - Run this once to create admin account
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "healthconnect";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create users table
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create admin account
$hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
$result = $conn->query("INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('System Administrator', 'admin@healthconnect.com', '$hashed_password', 'admin')");

if ($conn->affected_rows > 0) {
    echo "✅ Admin account created successfully!<br>";
} else {
    echo "ℹ️ Admin account already exists<br>";
}

echo "Email: <strong>admin@healthconnect.com</strong><br>";
echo "Password: <strong>admin123</strong><br>";
echo "<br><a href='login.php' style='color: blue;'>Go to Login Page</a>";

$conn->close();
?>