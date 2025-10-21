<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
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
$appointment_id = $_GET['id'];
$message = "";

// Get appointment details
$sql = "SELECT a.*, c.name as clinic_name, c.location 
        FROM appointments a 
        JOIN clinics c ON a.clinic_id = c.id 
        WHERE a.id = ? AND a.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    header("Location: my_appointments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_date = $_POST['appointment_date'];
    
    // Validate date is in the future
    if (strtotime($new_date) <= time()) {
        $message = "<div class='error'>Please select a future date and time.</div>";
    } else {
        $update_sql = "UPDATE appointments SET appointment_date = ?, status = 'rescheduled' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_date, $appointment_id);
        
        if ($update_stmt->execute()) {
            $message = "<div class='success'>Appointment rescheduled successfully!</div>";
            // Refresh appointment data
            $appointment['appointment_date'] = $new_date;
            $appointment['status'] = 'rescheduled';
        } else {
            $message = "<div class='error'>Error rescheduling appointment: " . $update_stmt->error . "</div>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment - HealthConnect</title>
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
            max-width: 600px;
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
        
        .current-appointment {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #4facfe;
            margin-bottom: 2rem;
        }
        
        .current-appointment h3 {
            color: #4facfe;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
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
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .action-buttons .btn-secondary {
            flex: 1;
        }
        
        .action-buttons .btn-primary {
            flex: 2;
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
            <h1>🔄 Reschedule Appointment</h1>
            
            <?php echo $message; ?>
            
            <div class="current-appointment">
                <h3>Current Appointment Details</h3>
                <p><strong>Clinic:</strong> <?php echo htmlspecialchars($appointment['clinic_name']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($appointment['location']); ?></p>
                <p><strong>Current Date:</strong> <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($appointment['status']); ?></p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="appointment_date">New Preferred Date & Time:</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>"
                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Please select a future date and time
                    </small>
                </div>
                
                <div class="action-buttons">
                    <a href="my_appointments.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Reschedule Appointment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Set minimum datetime to current time
        document.getElementById('appointment_date').min = new Date().toISOString().slice(0, 16);
    </script>
</body>
</html>