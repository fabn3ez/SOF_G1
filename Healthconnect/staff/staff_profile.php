<?php
session_start();

$allowed_roles = ['staff', 'doctor', 'nurse'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../staff/login.php");
    exit();
}

// Create database connection
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "healthconnect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$staff_id = $_SESSION['user_id'];
$message = "";

// Get staff details
$staff = $conn->query("SELECT * FROM users WHERE id = $staff_id")->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    
    $sql = "UPDATE users SET name = ?, phone = ?, specialization = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $phone, $specialization, $staff_id);
    
    if ($stmt->execute()) {
        $message = "<div class='success'>Profile updated successfully.</div>";
        $_SESSION['user_name'] = $name;
        // Refresh staff data
        $staff = $conn->query("SELECT * FROM users WHERE id = $staff_id")->fetch_assoc();
    } else {
        $message = "<div class='error'>Error updating profile.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HealthConnect Staff</title>
    <link rel="stylesheet" href="staff_styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">HealthConnect Staff</div>
        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <nav class="admin-nav">
        <ul class="admin-nav-links">
            <li><a href="staff_dashboard.php">Dashboard</a></li>
            <li><a href="staff_appointments.php">Appointments</a></li>
            <li><a href="staff_patients.php">Patients</a></li>
            <li><a href="staff_profile.php" class="active">My Profile</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h1>👤 My Profile</h1>
            
            <?php echo $message; ?>
            
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Profile Information</h3>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" value="<?php echo htmlspecialchars($staff['email']); ?>" disabled>
                                <small style="color: #666;">Email cannot be changed</small>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($staff['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Specialization/Department</label>
                                <input type="text" name="specialization" value="<?php echo htmlspecialchars($staff['specialization'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="<?php echo ucfirst($staff['role']); ?>" disabled>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Account Details</h3>
                    <div style="padding: 1rem;">
                        <div class="stats-item">
                            <span>Staff ID</span>
                            <strong>#<?php echo $staff['id']; ?></strong>
                        </div>
                        <div class="stats-item">
                            <span>Member Since</span>
                            <strong><?php echo date('M j, Y', strtotime($staff['created_at'])); ?></strong>
                        </div>
                        <div class="stats-item">
                            <span>Account Status</span>
                            <strong style="color: #28a745;">Active</strong>
                        </div>
                        
                        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <h4>Quick Stats</h4>
                            <div class="stats-item">
                                <span>Appointments Today</span>
                                <strong>5</strong>
                            </div>
                            <div class="stats-item">
                                <span>Patients This Month</span>
                                <strong>24</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>