<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Create database connection
$servername = "localhost";
$username = "root";
$password = "qwer4321..E";
$dbname = "healthconnect";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Get user info
$sql = "SELECT name FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get upcoming appointments
$appointments_sql = "SELECT a.*, c.name as clinic_name, c.location 
                     FROM appointments a 
                     JOIN clinics c ON a.clinic_id = c.id 
                     WHERE a.user_id = ? AND a.appointment_date > NOW() 
                     AND a.status != 'cancelled'
                     ORDER BY a.appointment_date ASC 
                     LIMIT 3";
$appointments_stmt = $conn->prepare($appointments_sql);
$appointments_stmt->bind_param("i", $user_id);
$appointments_stmt->execute();
$upcoming_appointments = $appointments_stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HealthConnect</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f8f9fa;
            color: #333;
        }
        
        .navbar {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .welcome-section h1 {
            color: #4facfe;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h3 {
            color: #4facfe;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .appointment-list {
            list-style: none;
        }
        
        .appointment-item {
            padding: 1rem;
            border-left: 4px solid #4facfe;
            background: #f8f9fa;
            margin-bottom: 0.5rem;
            border-radius: 0 8px 8px 0;
        }
        
        .appointment-date {
            font-weight: bold;
            color: #333;
        }
        
        .appointment-clinic {
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .no-appointments {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">HealthConnect</div>
        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome to Your Health Portal</h1>
            <p>Manage your appointments and healthcare needs in one place</p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>📅 Upcoming Appointments</h3>
                <?php if ($upcoming_appointments->num_rows > 0): ?>
                    <ul class="appointment-list">
                        <?php while($appointment = $upcoming_appointments->fetch_assoc()): ?>
                            <li class="appointment-item">
                                <div class="appointment-date">
                                    <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?>
                                </div>
                                <div class="appointment-clinic">
                                    <?php echo htmlspecialchars($appointment['clinic_name']); ?>
                                </div>
                                <div class="appointment-status">
                                    Status: <span style="color: #28a745;"><?php echo ucfirst($appointment['status']); ?></span>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-appointments">No upcoming appointments</div>
                <?php endif; ?>
                <a href="my_appointments.php" class="btn-primary" style="margin-top: 1rem; display: block;">View All Appointments</a>
            </div>

            <div class="card">
                <h3>⚡ Quick Actions</h3>
                <div class="action-grid">
                    <a href="book_appointment.php" class="action-card">
                        <div class="action-icon">📋</div>
                        <h4>Book Appointment</h4>
                        <p>Schedule a new healthcare visit</p>
                    </a>
                    
                    <a href="my_appointments.php" class="action-card">
                        <div class="action-icon">📊</div>
                        <h4>My Appointments</h4>
                        <p>View and manage your bookings</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>