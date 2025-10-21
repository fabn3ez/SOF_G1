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

// Fetch all patients
$patients = $conn->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM appointments WHERE user_id = u.id) as appointment_count,
           (SELECT MAX(appointment_date) FROM appointments WHERE user_id = u.id) as last_visit
    FROM users u 
    WHERE u.role = 'patient' 
    ORDER BY u.name
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - HealthConnect Staff</title>
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
            <li><a href="staff_patients.php" class="active">Patients</a></li>
            <li><a href="staff_profile.php">My Profile</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h1>👥 Patient Management</h1>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search patients by name, email, or phone...">
                <div class="user-count">
                    Total Patients: <?php echo $patients->num_rows; ?>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Contact Information</th>
                            <th>Appointments</th>
                            <th>Last Visit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($patient = $patients->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($patient['name']); ?></strong>
                                    <?php if ($patient['date_of_birth']): ?>
                                        <br><small>🎂 <?php echo date('M j, Y', strtotime($patient['date_of_birth'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($patient['email']); ?></div>
                                    <?php if ($patient['phone']): ?>
                                        <div>📞 <?php echo $patient['phone']; ?></div>
                                    <?php endif; ?>
                                    <?php if ($patient['address']): ?>
                                        <small>📍 <?php echo htmlspecialchars($patient['address']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge"><?php echo $patient['appointment_count']; ?> appointments</span>
                                </td>
                                <td>
                                    <?php if ($patient['last_visit']): ?>
                                        <?php echo date('M j, Y', strtotime($patient['last_visit'])); ?>
                                    <?php else: ?>
                                        <span style="color: #666; font-style: italic;">No visits yet</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <button class="btn btn-sm btn-primary">View History</button>
                                        <button class="btn btn-sm btn-success">Add Record</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>