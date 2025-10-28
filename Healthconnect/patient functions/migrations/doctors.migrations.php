<?php

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->select_db("health_connect");

// Create doctors table
$sql = "CREATE TABLE IF NOT EXISTS doctors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    clinic_id INT UNSIGNED NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    availability ENUM('available','unavailable','on_leave') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'doctors' created successfully\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}