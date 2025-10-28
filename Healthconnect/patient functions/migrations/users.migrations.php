<?php

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database if not exists
$mysqli->query("CREATE DATABASE IF NOT EXISTS health_connect");
$mysqli->select_db("health_connect");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    date_of_birth DATE NOT NULL,
    address TEXT,
    role ENUM('patient','staff','admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'users' created successfully\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}