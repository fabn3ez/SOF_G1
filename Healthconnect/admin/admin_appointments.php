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

$message = "";

// Handle appointment actions
if (isset($_GET['update_status'])) {
    $appointment_id = $_GET['update_status'];
    $new_status = $_GET['status'];
    
    $sql = "UPDATE appointments SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $appointment_id);
    
    if ($stmt->execute()) {
        $message = "<div class='success'>Appointment status updated successfully.</div>";
    } else {
        $message = "<div class='error'>Error updating appointment.</div>";
    }
}

// Fetch all appointments with user and clinic details
$appointments = $conn->query("
    SELECT a.*, u.name as patient_name, u.email as patient_email, 
           c.name as clinic_name, c.location 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN clinics c ON a.clinic_id = c.id 
    ORDER BY a.appointment_date DESC
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - HealthConnect Admin</title>
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
            <li><a href="admin_appointments.php" class="active">Appointments</a></li>
            <li><a href="admin_clinics.php">Clinics</a></li>
            <li><a href="admin_reports.php">Reports</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h1>📅 Manage Appointments</h1>
            <?php echo $message; ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Clinic</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($appointment = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $appointment['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong><br>
                                    <small><?php echo $appointment['patient_email']; ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($appointment['clinic_name']); ?><br>
                                    <small><?php echo htmlspecialchars($appointment['location']); ?></small>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                <td>
                                    <span class="type-badge">
                                        <?php echo ucfirst($appointment['appointment_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="GET" style="display: inline;">
                                        <input type="hidden" name="update_status" value="<?php echo $appointment['id']; ?>">
                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                            <option value="booked" <?php echo $appointment['status'] == 'booked' ? 'selected' : ''; ?>>Booked</option>
                                            <option value="confirmed" <?php echo $appointment['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="?update_status=<?php echo $appointment['id']; ?>&status=cancelled" class="btn btn-danger btn-sm">Cancel</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>