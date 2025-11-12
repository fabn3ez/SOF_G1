<?php
// setup_doctor.php - Add doctor-specific features
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "healthconnect";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create prescriptions table
$conn->query("
    CREATE TABLE IF NOT EXISTS prescriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        doctor_id INT NOT NULL,
        appointment_id INT,
        medication_name VARCHAR(255) NOT NULL,
        dosage VARCHAR(100),
        frequency VARCHAR(100),
        duration VARCHAR(100),
        instructions TEXT,
        prescribed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
    )
");

// Create diagnoses table
$conn->query("
    CREATE TABLE IF NOT EXISTS diagnoses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        doctor_id INT NOT NULL,
        appointment_id INT,
        diagnosis_code VARCHAR(50),
        diagnosis_name VARCHAR(255) NOT NULL,
        description TEXT,
        severity ENUM('mild', 'moderate', 'severe'),
        notes TEXT,
        diagnosed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
    )
");

// Update sample doctors with specializations
$conn->query("UPDATE users SET specialization = 'Cardiology' WHERE email = 'dr.sarah@healthconnect.com'");
$conn->query("UPDATE users SET specialization = 'Pediatrics' WHERE email = 'dr.mike@healthconnect.com'");

echo "✅ Doctor system setup completed!<br>";
echo "Doctor-specific tables created:<br>";
echo "- Prescriptions table<br>";
echo "- Diagnoses table<br>";
echo "<br><a href='login.php'>Go to Login Page</a>";

$conn->close();
?>