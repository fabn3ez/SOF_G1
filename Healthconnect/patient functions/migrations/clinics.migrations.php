<?php

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->select_db("health_connect");

// Create clinics table
$sql = "CREATE TABLE IF NOT EXISTS clinics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    opening_hours VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'clinics' created successfully\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}