<?php

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->select_db("health_connect");

// Create appointment_reminders table
$sql = "CREATE TABLE IF NOT EXISTS appointment_reminders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    reminder_type ENUM('email','sms','both') NOT NULL,
    reminder_sent_at TIMESTAMP NULL,
    reminder_scheduled_for TIMESTAMP NOT NULL,
    status ENUM('scheduled','sent','failed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'appointment_reminders' created successfully\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}