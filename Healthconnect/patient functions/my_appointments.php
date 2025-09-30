<?php
session_start();

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
$message = "";

// Handle cancel action
if (isset($_GET['cancel'])) {
    $appointment_id = $_GET['cancel'];
    
    $verify_sql = "SELECT * FROM appointments WHERE id = ? AND user_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $appointment_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 1) {
        $update_sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $appointment_id);
        
        if ($update_stmt->execute()) {
            $message = "<div class='success'>Appointment cancelled successfully.</div>";
        } else {
            $message = "<div class='error'>Error cancelling appointment.</div>";
        }
    } else {
        $message = "<div class='error'>Appointment not found.</div>";
    }
}

// Handle filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where_conditions = ["a.user_id = ?"];
$params = [$user_id];
$param_types = "i";

switch ($filter) {
    case 'upcoming':
        $where_conditions[] = "a.appointment_date > NOW() AND a.status IN ('booked', 'confirmed')";
        break;
    case 'past':
        $where_conditions[] = "a.appointment_date <= NOW()";
        break;
    case 'cancelled':
        $where_conditions[] = "a.status = 'cancelled'";
        break;
    case 'completed':
        $where_conditions[] = "a.status = 'completed'";
        break;
}

$where_clause = implode(" AND ", $where_conditions);

// Fetch appointments with enhanced details
$sql = "SELECT a.*, c.name as clinic_name, c.location, c.phone as clinic_phone, 
               u.name as patient_name, u.phone as patient_phone
        FROM appointments a 
        JOIN clinics c ON a.clinic_id = c.id 
        JOIN users u ON a.user_id = u.id 
        WHERE $where_clause 
        ORDER BY a.appointment_date DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$appointments = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - HealthConnect</title>
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .card h1 {
            color: #4facfe;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #4facfe;
            background: white;
            color: #4facfe;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: #4facfe;
            color: white;
        }
        
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .appointments-table th {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .appointments-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        
        .appointments-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-booked { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-rescheduled { background: #ffeaa7; color: #856404; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-no_show { background: #e2e3e5; color: #383d41; }
        
        .type-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            background: #e7f3ff;
            color: #4facfe;
            display: inline-block;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-info:hover {
            background: #138496;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-1px);
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .no-appointments {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .no-appointments .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
        }
        
        .past-appointment {
            opacity: 0.8;
            background: #f8f9fa;
        }
        
        .upcoming-appointment {
            background: #fff;
        }
        
        .clinic-info {
            font-weight: bold;
            color: #333;
        }
        
        .clinic-details {
            font-size: 0.8rem;
            color: #666;
        }
        
        .doctor-info {
            font-weight: 500;
            color: #4facfe;
        }
        
        .duration {
            font-size: 0.8rem;
            color: #666;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        @media (max-width: 768px) {
            .appointments-table {
                font-size: 0.8rem;
            }
            
            .appointments-table th,
            .appointments-table td {
                padding: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">HealthConnect</div>
        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h1>📊 My Appointments</h1>
            
            <?php echo $message; ?>
            
            <div class="filters">
                <a href="?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">All Appointments</a>
                <a href="?filter=upcoming" class="filter-btn <?php echo $filter == 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                <a href="?filter=past" class="filter-btn <?php echo $filter == 'past' ? 'active' : ''; ?>">Past</a>
                <a href="?filter=completed" class="filter-btn <?php echo $filter == 'completed' ? 'active' : ''; ?>">Completed</a>
                <a href="?filter=cancelled" class="filter-btn <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
            </div>
            
            <?php if ($appointments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Clinic & Location</th>
                                <th>Appointment Type</th>
                                <th>Doctor</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($appointment = $appointments->fetch_assoc()): 
                                $is_past = strtotime($appointment['appointment_date']) < time();
                                $status_class = 'status-' . $appointment['status'];
                                $row_class = $is_past ? 'past-appointment' : 'upcoming-appointment';
                            ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td>
                                        <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong><br>
                                        <small><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></small>
                                        <?php if ($is_past): ?>
                                            <br><small style="color: #dc3545;">(Past)</small>
                                        <?php else: ?>
                                            <br><small style="color: #28a745;">(Upcoming)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="clinic-info"><?php echo htmlspecialchars($appointment['clinic_name']); ?></div>
                                        <div class="clinic-details"><?php echo htmlspecialchars($appointment['location']); ?></div>
                                        <?php if ($appointment['clinic_phone']): ?>
                                            <div class="clinic-details">📞 <?php echo $appointment['clinic_phone']; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="type-badge">
                                            <?php 
                                            $type_labels = [
                                                'general' => 'General',
                                                'dental' => 'Dental',
                                                'eye_checkup' => 'Eye Checkup',
                                                'vaccination' => 'Vaccination',
                                                'follow_up' => 'Follow-up',
                                                'emergency' => 'Emergency'
                                            ];
                                            echo $type_labels[$appointment['appointment_type']] ?? ucfirst($appointment['appointment_type']);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($appointment['doctor_name']): ?>
                                            <div class="doctor-info">👨‍⚕ <?php echo htmlspecialchars($appointment['doctor_name']); ?></div>
                                        <?php else: ?>
                                            <div style="color: #666; font-style: italic;">Not assigned</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="duration">⏱ <?php echo $appointment['duration_minutes']; ?> min</div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($appointment['reason']): ?>
                                            <div style="max-width: 200px; word-wrap: break-word;">
                                                <?php echo htmlspecialchars($appointment['reason']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #666; font-style: italic;">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if (!$is_past && in_array($appointment['status'], ['booked', 'confirmed'])): ?>
                                                <a href="reschedule.php?id=<?php echo $appointment['id']; ?>" class="btn-warning">Reschedule</a>
                                                <a href="?cancel=<?php echo $appointment['id']; ?>" class="btn-danger" 
                                                   onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                            <?php elseif ($appointment['status'] == 'cancelled'): ?>
                                                <span style="color: #666; font-style: italic;">Cancelled</span>
                                            <?php elseif ($appointment['status'] == 'completed'): ?>
                                                <span style="color: #28a745; font-style: italic;">Completed</span>
                                            <?php else: ?>
                                                <span style="color: #666; font-style: italic;">No actions</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-appointments">
                    <div class="icon">📅</div>
                    <h3>No Appointments Found</h3>
                    <p>No appointments match your current filter.</p>
                    <a href="book_appointment.php" class="btn-secondary" style="margin-top: 1rem;">Book New Appointment</a>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="dashboard.php" class="btn-secondary">← Back to Dashboard</a>
                <a href="book_appointment.php" class="btn-secondary" style="background: #28a745;">+ Book New Appointment</a>
            </div>
        </div>
    </div>
</body>
</html>