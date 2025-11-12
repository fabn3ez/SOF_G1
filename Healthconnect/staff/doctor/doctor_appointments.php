<?php
session_start();

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
$message = "";

// Handle consultation completion
if (isset($_GET['complete_consultation'])) {
    $appointment_id = $_GET['complete_consultation'];
    
    $sql = "UPDATE appointments SET status = 'completed' WHERE id = ? AND status = 'confirmed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        $message = "<div class='success'>Consultation marked as completed.</div>";
    } else {
        $message = "<div class='error'>Error completing consultation.</div>";
    }
}

// Fetch doctor's appointments
$appointments = $conn->query("
    SELECT a.*, u.name as patient_name, u.email, u.phone, u.date_of_birth,
           TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age,
           c.name as clinic_name
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN clinics c ON a.clinic_id = c.id 
    WHERE a.status IN ('confirmed', 'completed')
    ORDER BY a.appointment_date DESC
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - HealthConnect Doctor</title>
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
            <li><a href="doctor_dashboard.php">Dashboard</a></li>
            <li><a href="doctor_appointments.php" class="active">My Appointments</a></li>
            <li><a href="doctor_patients.php">My Patients</a></li>
            <li><a href="doctor_prescriptions.php">Prescriptions</a></li>
            <li><a href="doctor_records.php">Medical Records</a></li>
            <li><a href="doctor_profile.php">My Profile</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h1>📅 My Consultations</h1>
            
            <?php echo $message; ?>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search patients...">
                <div class="filter-buttons">
                    <button class="btn btn-primary btn-sm" onclick="filterAppointments('all')">All</button>
                    <button class="btn btn-success btn-sm" onclick="filterAppointments('today')">Today</button>
                    <button class="btn btn-warning btn-sm" onclick="filterAppointments('upcoming')">Upcoming</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Appointment Details</th>
                            <th>Clinic</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($appointment = $appointments->fetch_assoc()): 
                            $is_today = date('Y-m-d', strtotime($appointment['appointment_date'])) == date('Y-m-d');
                            $is_upcoming = strtotime($appointment['appointment_date']) > time();
                        ?>
                            <tr class="appointment-row" 
                                data-date="<?php echo date('Y-m-d', strtotime($appointment['appointment_date'])); ?>"
                                data-status="<?php echo $appointment['status']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                                    <div class="patient-details">
                                        <?php if ($appointment['age']): ?>
                                            <span>Age: <?php echo $appointment['age']; ?></span> •
                                        <?php endif; ?>
                                        <?php if ($appointment['phone']): ?>
                                            <span>📞 <?php echo $appointment['phone']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong>
                                    <div><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></div>
                                    <?php if ($appointment['reason']): ?>
                                        <div><small><strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['clinic_name']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <?php if ($appointment['status'] === 'confirmed'): ?>
                                            <a href="doctor_consultation.php?appointment_id=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-primary btn-sm">Start Consultation</a>
                                            <a href="?complete_consultation=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('Mark this consultation as completed?')">Complete</a>
                                        <?php elseif ($appointment['status'] === 'completed'): ?>
                                            <a href="doctor_consultation.php?appointment_id=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-warning btn-sm">View Details</a>
                                        <?php endif; ?>
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
        function filterAppointments(filter) {
            const rows = document.querySelectorAll('.appointment-row');
            const today = new Date().toISOString().split('T')[0];
            
            rows.forEach(row => {
                const appointmentDate = row.getAttribute('data-date');
                const isToday = appointmentDate === today;
                const isUpcoming = new Date(appointmentDate) > new Date();
                
                switch(filter) {
                    case 'today':
                        row.style.display = isToday ? '' : 'none';
                        break;
                    case 'upcoming':
                        row.style.display = isUpcoming ? '' : 'none';
                        break;
                    default:
                        row.style.display = '';
                }
            });
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.appointment-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (row.style.display !== 'none') {
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                }
            });
        });
    </script>
</body>
</html>