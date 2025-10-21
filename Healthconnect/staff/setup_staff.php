<?php
// setup_staff.php - Add staff features to database
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "healthconnect";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create sample staff accounts
$hashed_password = password_hash('staff123', PASSWORD_BCRYPT);
$staff_members = [
    ['Dr. Sarah Wilson', 'dr.sarah@healthconnect.com', $hashed_password, 'Cardiology', 'doctor'],
    ['Dr. Mike Chen', 'dr.mike@healthconnect.com', $hashed_password, 'Pediatrics', 'doctor'],
    ['Nurse Jane Doe', 'nurse.jane@healthconnect.com', $hashed_password, 'General', 'nurse'],
    ['Receptionist Tom Brown', 'reception@healthconnect.com', $hashed_password, 'Administration', 'staff']
];

foreach ($staff_members as $staff) {
    $conn->query("INSERT IGNORE INTO users (name, email, password, specialization, role) VALUES 
        ('{$staff[0]}', '{$staff[1]}', '{$staff[2]}', '{$staff[3]}', '{$staff[4]}')");
}

echo "✅ Staff system setup completed!<br>";
echo "Staff accounts created:<br>";
foreach ($staff_members as $staff) {
    echo "- {$staff[0]} ({$staff[1]}) - Password: staff123<br>";
}
echo "<br><a href='login.php'>Go to Login Page</a>";

$conn->close();
?>