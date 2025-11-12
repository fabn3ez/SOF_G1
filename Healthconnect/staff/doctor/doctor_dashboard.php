<?php
session_start();

// Check if user is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
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

$doctor_id = $_SESSION['user_id'];

// Get doctor statistics
$today_appointments = $conn->query("
    SELECT COUNT(*) as count FROM appointments 
    WHERE DATE(appointment_date) = CURDATE() 
    AND status IN ('confirmed')
")->fetch_assoc()['count'];

$total_patients = $conn->query("
    SELECT COUNT(DISTINCT user_id) as count FROM appointments 
    WHERE status = 'completed'
")->fetch_assoc()['count'];

$pending_prescriptions = $conn->query("
    SELECT COUNT(*) as count FROM prescriptions 
    WHERE doctor_id = $doctor_id AND status = 'active'
")->fetch_assoc()['count'];

$monthly_consultations = $conn->query("
    SELECT COUNT(*) as count FROM appointments 
    WHERE doctor_id = $doctor_id 
    AND MONTH(appointment_date) = MONTH(CURDATE())
    AND status = 'completed'
")->fetch_assoc()['count'];

// Get today's appointments
$todays_appointments = $conn->query("
    SELECT a.*, u.name as patient_name, u.phone, u.date_of_birth,
           TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE DATE(a.appointment_date) = CURDATE() 
    AND a.status IN ('confirmed')
    ORDER BY a.appointment_date ASC
    LIMIT 5
");

// Get doctor specialization
$doctor_info = $conn->query("
    SELECT specialization FROM users WHERE id = $doctor_id
")->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - HealthConnect</title>
    <link rel="stylesheet" href="doctor_styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">HealthConnect Doctor</div>
        <div class="navbar-user">
            <span>Welcome, Dr. <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <nav class="doctor-nav">
        <ul class="doctor-nav-links">
            <li><a href="doctor_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="doctor_appointments.php">My Appointments</a></li>
            <li><a href="doctor_patients.php">My Patients</a></li>
            <li><a href="doctor_prescriptions.php">Prescriptions</a></li>
            <li><a href="doctor_records.php">Medical Records</a></li>
            <li><a href="doctor_profile.php">My Profile</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="doctor-welcome">
            <h1>👨‍⚕️ Doctor Dashboard</h1>
            <p>Welcome back, Dr. <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            <div class="doctor-role-badge">Medical Doctor</div>
            <?php if ($doctor_info['specialization']): ?>
                <div class="specialization-badge"><?php echo $doctor_info['specialization']; ?></div>
            <?php endif; ?>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_appointments; ?></div>
                <div class="stat-label">Today's Consultations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_patients; ?></div>
                <div class="stat-label">Total Patients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_prescriptions; ?></div>
                <div class="stat-label">Active Prescriptions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $monthly_consultations; ?></div>
                <div class="stat-label">Monthly Consultations</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>📅 Today's Consultations</h3>
                <?php if ($todays_appointments->num_rows > 0): ?>
                    <div class="appointment-list">
                        <?php while($appointment = $todays_appointments->fetch_assoc()): ?>
                            <div class="appointment-item">
                                <div class="appointment-time">
                                    <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?>
                                </div>
                                <div class="appointment-patient">
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                                    <div class="appointment-details">
                                        <?php if ($appointment['age']): ?>
                                            <span>Age: <?php echo $appointment['age']; ?> • </span>
                                        <?php endif; ?>
                                        <span class="type-badge"><?php echo ucfirst($appointment['appointment_type']); ?></span>
                                    </div>
                                    <?php if ($appointment['reason']): ?>
                                        <div class="appointment-reason">
                                            <small><strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="action-cell">
                                    <a href="doctor_consultation.php?appointment_id=<?php echo $appointment['id']; ?>" 
                                       class="btn btn-primary btn-sm">Start Consultation</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">📅</div>
                        <h3>No Consultations Today</h3>
                        <p>You have no scheduled consultations for today.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>⚡ Quick Actions</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem; padding: 1rem;">
                    <a href="doctor_appointments.php" class="btn btn-primary">View All Appointments</a>
                    <a href="doctor_patients.php" class="btn btn-success">My Patient List</a>
                    <a href="doctor_prescriptions.php" class="btn btn-warning">Manage Prescriptions</a>
                    <a href="doctor_records.php" class="btn btn-medical">Medical Records</a>
                </div>

                <h3 style="margin-top: 2rem;">📊 Quick Stats</h3>
                <div style="padding: 1rem;">
                    <div class="stats-item">
                        <span>Consultations This Week</span>
                        <strong>12</strong>
                    </div>
                    <div class="stats-item">
                        <span>New Patients This Month</span>
                        <strong>8</strong>
                    </div>
                    <div class="stats-item">
                        <span>Prescriptions Today</span>
                        <strong>3</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>