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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clinic_id = $_POST['clinic_id'];
    $appointment_date = $_POST['appointment_date'];
    
    // Validate date is in the future
    if (strtotime($appointment_date) <= time()) {
        $message = "<div class='error'>Please select a future date and time.</div>";
    } else {
        $sql = "INSERT INTO appointments (user_id, clinic_id, appointment_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $clinic_id, $appointment_date);
        
        if ($stmt->execute()) {
            $message = "<div class='success'>Appointment booked successfully!</div>";
        } else {
            $message = "<div class='error'>Error booking appointment: " . $stmt->error . "</div>";
        }
    }
}

// Fetch clinics
$clinics = $conn->query("SELECT * FROM clinics ORDER BY name");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - HealthConnect</title>
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
            max-width: 800px;
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
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }
        
        .clinic-option {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .clinic-name {
            font-weight: bold;
            color: #333;
        }
        
        .clinic-location {
            color: #666;
            font-size: 0.9rem;
        }
        
        .clinic-phone {
            color: #4facfe;
            font-size: 0.9rem;
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
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #4facfe;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0 8px 8px 0;
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
            <h1>📅 Book New Appointment</h1>
            
            <?php echo $message; ?>
            
            <div class="info-box">
                <strong>📋 Important:</strong> Please select your preferred clinic and choose a suitable date and time for your appointment. 
                You'll receive a confirmation once booked.
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="clinic_id">Select Clinic:</label>
                    <select id="clinic_id" name="clinic_id" required>
                        <option value="">Choose a clinic...</option>
                        <?php while($clinic = $clinics->fetch_assoc()): ?>
                            <option value="<?php echo $clinic['id']; ?>">
                                <?php echo htmlspecialchars($clinic['name']); ?> - 
                                <?php echo htmlspecialchars($clinic['location']); ?>
                                <?php if($clinic['phone']): ?> (<?php echo $clinic['phone']; ?>)<?php endif; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="appointment_date">Preferred Date & Time:</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required 
                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Please select a future date and time
                    </small>
                </div>
                
                <button type="submit" class="btn-primary">Book Appointment</button>
            </form>
            
            <a href="dashboard.php" class="btn-secondary">← Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Set minimum datetime to current time
        document.getElementById('appointment_date').min = new Date().toISOString().slice(0, 16);
    </script>
</body>
</html>