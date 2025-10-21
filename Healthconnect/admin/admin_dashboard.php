<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

$user_id = $_SESSION['user_id'];

// Get statistics for dashboard
$total_patients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='patient'")->fetch_assoc()['count'];
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetch_assoc()['count'];
$total_clinics = $conn->query("SELECT COUNT(*) as count FROM clinics")->fetch_assoc()['count'];

// Get recent appointments
$recent_appointments = $conn->query("
    SELECT a.*, u.name as patient_name, c.name as clinic_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN clinics c ON a.clinic_id = c.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

// Get appointment status distribution
$status_stats = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM appointments 
    GROUP BY status
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HealthConnect</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">HealthConnect Admin</div>
        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)</span>
            <a href="../login.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <nav class="admin-nav">
        <ul class="admin-nav-links">
            <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="admin_users.php">Manage Users</a></li>
            <li><a href="admin_appointments.php">Appointments</a></li>
            <li><a href="admin_clinics.php">Clinics</a></li>
            <li><a href="admin_reports.php">Reports</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Admin Dashboard</h1>
            <p>Manage your healthcare system efficiently</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_patients; ?></div>
                <div class="stat-label">Total Patients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_appointments; ?></div>
                <div class="stat-label">Total Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_appointments; ?></div>
                <div class="stat-label">Today's Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_clinics; ?></div>
                <div class="stat-label">Clinics</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>📅 Recent Appointments</h3>
                <?php if ($recent_appointments->num_rows > 0): ?>
                    <ul class="appointment-list">
                        <?php while($appointment = $recent_appointments->fetch_assoc()): ?>
                            <li class="appointment-item">
                                <div class="appointment-patient">
                                    <?php echo htmlspecialchars($appointment['patient_name']); ?>
                                </div>
                                <div class="appointment-details">
                                    <?php echo htmlspecialchars($appointment['clinic_name']); ?> • 
                                    <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?>
                                </div>
                                <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent appointments</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>📊 Appointment Status</h3>
                <ul class="stats-list">
                    <?php while($stat = $status_stats->fetch_assoc()): ?>
                        <li class="stats-item">
                            <span><?php echo ucfirst($stat['status']); ?></span>
                            <strong><?php echo $stat['count']; ?></strong>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

        <div class="quick-actions">
            <a href="admin_users.php" class="action-btn">👥 Manage Users</a>
            <a href="admin_appointments.php" class="action-btn">📅 Manage Appointments</a>
            <a href="admin_clinics.php" class="action-btn">🏥 Manage Clinics</a>
            <a href="admin_reports.php" class="action-btn">📈 View Reports</a>
        </div>
    </div>
</body>
</html>