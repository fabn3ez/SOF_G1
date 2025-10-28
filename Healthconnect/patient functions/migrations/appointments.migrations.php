<?php

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->select_db("health_connect");

// Create appointments table
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    clinic_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    appointment_date DATETIME NOT NULL,
    appointment_type ENUM('general','dental','eye_checkup','vaccination','follow_up','emergency') NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('booked','confirmed','rescheduled','cancelled','completed','no_show') DEFAULT 'booked',
    doctor_name VARCHAR(100) NOT NULL,
    notes TEXT,
    duration_minutes INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'appointments' created successfully\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}