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

// Get report data
$monthly_appointments = $conn->query("
    SELECT DATE_FORMAT(appointment_date, '%Y-%m') as month, 
           COUNT(*) as count 
    FROM appointments 
    WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(appointment_date, '%Y-%m') 
    ORDER BY month
");

$clinic_stats = $conn->query("
    SELECT c.name, COUNT(a.id) as appointment_count 
    FROM clinics c 
    LEFT JOIN appointments a ON c.id = a.clinic_id 
    GROUP BY c.id 
    ORDER BY appointment_count DESC
");

$status_distribution = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM appointments 
    GROUP BY status
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - HealthConnect Admin</title>
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
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_users.php">Manage Users</a></li>
            <li><a href="admin_appointments.php">Appointments</a></li>
            <li><a href="admin_clinics.php">Clinics</a></li>
            <li><a href="admin_reports.php" class="active">Reports</a></li>
        </ul>
    </nav>

    <div class="container">
    <div class="card">
            <h1>📈 System Reports & Analytics</h1>
            
            <div class="reports-grid">
                <div class="report-card">
                    <h3>📊 Monthly Appointments</h3>
                    <?php while($month = $monthly_appointments->fetch_assoc()): ?>
                        <div class="stats-bar">
                            <div class="stats-fill" style="width: <?php echo min(100, ($month['count'] / 50) * 100); ?>%">
                                <?php echo $month['month']; ?>: <?php echo $month['count']; ?> appointments
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="report-card">
                    <h3>🏥 Clinic Performance</h3>
                    <?php while($clinic = $clinic_stats->fetch_assoc()): ?>
                        <div class="stats-bar">
                            <div class="stats-fill" style="width: <?php echo min(100, ($clinic['appointment_count'] / 20) * 100); ?>%">
                                <?php echo htmlspecialchars($clinic['name']); ?>: <?php echo $clinic['appointment_count']; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="report-card">
                    <h3>📋 Appointment Status Distribution</h3>
                    <?php while($status = $status_distribution->fetch_assoc()): ?>
                        <div class="stats-bar">
                            <div class="stats-fill" style="width: <?php echo min(100, ($status['count'] / 50) * 100); ?>%">
                                <?php echo ucfirst($status['status']); ?>: <?php echo $status['count']; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="report-card">
                    <h3>📊 Quick Stats</h3>
                    <?php
                    // These would come from your database
                    $total_patients = $conn->query(query: "SELECT COUNT(*) as count FROM users WHERE role='patient'")->fetch_assoc()['count'];
                    $total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
                    $today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetch_assoc()['count'];
                    $total_clinics = $conn->query("SELECT COUNT(*) as count FROM clinics")->fetch_assoc()['count'];
                    $conn->close();
                    
                    ?>
                    <div class="stats-item">
                        <span>Total Patients</span>
                        <strong><?php echo $total_patients; ?></strong>
                    </div>
                    <div class="stats-item">
                        <span>Total Appointments</span>
                        <strong><?php echo $total_appointments; ?></strong>
                    </div>
                    <div class="stats-item">
                        <span>Today's Appointments</span>
                        <strong><?php echo $today_appointments; ?></strong>
                    </div>
                    <div class="stats-item">
                        <span>Total Clinics</span>
                        <strong><?php echo $total_clinics; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>