<?php
session_start();

// Check if user is staff
// $allowed_roles = ['staff', 'doctor', 'nurse'];
$allowed_roles = ['staff','doctor','nurse'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: login.php");
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

// Get staff statistics
$today_appointments = $conn->query("
    SELECT COUNT(*) as count FROM appointments 
    WHERE DATE(appointment_date) = CURDATE() 
    AND status IN ('booked', 'confirmed')
")->fetch_assoc()['count'];

$total_patients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='patient'")->fetch_assoc()['count'];
$upcoming_appointments = $conn->query("
    SELECT COUNT(*) as count FROM appointments 
    WHERE appointment_date > NOW() 
    AND status IN ('booked', 'confirmed')
")->fetch_assoc()['count'];

// Get today's appointments for this staff member
$todays_schedule = $conn->query("
    SELECT a.*, u.name as patient_name, c.name as clinic_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN clinics c ON a.clinic_id = c.id 
    WHERE DATE(a.appointment_date) = CURDATE() 
    AND a.status IN ('booked', 'confirmed')
    ORDER BY a.appointment_date ASC
    LIMIT 5
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - HealthConnect</title>
    <link rel="stylesheet" href="staff_styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">HealthConnect Staff</div>
        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <nav class="admin-nav">
        <ul class="admin-nav-links">
            <li><a href="staff_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="staff_appointments.php">Appointments</a></li>
            <li><a href="staff_patients.php">Patients</a></li>
            <li><a href="staff_profile.php">My Profile</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="staff-welcome">
            <h1>👨‍⚕️ Staff Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            <div class="role-badge"><?php echo ucfirst($_SESSION['role']); ?></div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_appointments; ?></div>
                <div class="stat-label">Today's Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $upcoming_appointments; ?></div>
                <div class="stat-label">Upcoming Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_patients; ?></div>
                <div class="stat-label">Total Patients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Medical Records</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>📅 Today's Schedule</h3>
                <?php if ($todays_schedule->num_rows > 0): ?>
                    <div class="appointment-list">
                        <?php while($appointment = $todays_schedule->fetch_assoc()): ?>
                            <div class="appointment-item">
                                <div class="appointment-time">
                                    <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?>
                                </div>
                                <div class="appointment-patient">
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                                    <div class="appointment-details">
                                        <?php echo htmlspecialchars($appointment['clinic_name']); ?> • 
                                        <span class="type-badge"><?php echo ucfirst($appointment['appointment_type']); ?></span>
                                    </div>
                                </div>
                                <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">📅</div>
                        <h3>No Appointments Today</h3>
                        <p>You have no scheduled appointments for today.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>⚡ Quick Actions</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem; padding: 1rem;">
                    <a href="staff_appointments.php" class="btn btn-primary">View All Appointments</a>
                    <a href="staff_patients.php" class="btn btn-success">Manage Patients</a>
                    <a href="staff_profile.php" class="btn btn-warning">Update Profile</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>