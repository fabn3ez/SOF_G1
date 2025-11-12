<?php
// doctor_profile.php

session_start();

// Example: Check if doctor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Database connection (update with your credentials)
$host = 'localhost';
$db   = 'healthconnect';
$user = 'root';
$pass = '1234';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit();
}

// Fetch doctor profile
$doctor_id = $_SESSION['doctor_id'];
$stmt = $pdo->prepare('SELECT name, email, specialization, phone, profile_pic FROM doctors WHERE id = ?');
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    echo "Doctor profile not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .profile-container { max-width: 500px; margin: auto; border: 1px solid #ccc; padding: 30px; border-radius: 8px; }
        .profile-pic { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 20px; }
        .profile-info { margin-bottom: 10px; }
        .edit-btn { display: inline-block; padding: 8px 16px; background: #007bff; color: #fff; border: none; border-radius: 4px; text-decoration: none; }
        .edit-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Doctor Profile</h2>
        <?php if ($doctor['profile_pic']): ?>
            <img src="<?php echo htmlspecialchars($doctor['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <img src="default_profile.png" alt="Profile Picture" class="profile-pic">
        <?php endif; ?>
        <div class="profile-info"><strong>Name:</strong> <?php echo htmlspecialchars($doctor['name']); ?></div>
        <div class="profile-info"><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></div>
        <div class="profile-info"><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></div>
        <div class="profile-info"><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone']); ?></div>
        <a href="edit_profile.php" class="edit-btn">Edit Profile</a>
    </div>
</body>
</html>