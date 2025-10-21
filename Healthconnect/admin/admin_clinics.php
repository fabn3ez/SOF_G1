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

// Handle clinic actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_clinic'])) {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $opening_hours = trim($_POST['opening_hours']);
    $description = trim($_POST['description']);
    
    $sql = "INSERT INTO clinics (name, location, phone, email, opening_hours, description) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $location, $phone, $email, $opening_hours, $description);
    
    if ($stmt->execute()) {
        $message = "<div class='success'>Clinic added successfully.</div>";
    } else {
        $message = "<div class='error'>Error adding clinic.</div>";
    }
}

if (isset($_GET['delete'])) {
    $clinic_id = $_GET['delete'];
    $sql = "DELETE FROM clinics WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clinic_id);
    
    if ($stmt->execute()) {
        $message = "<div class='success'>Clinic deleted successfully.</div>";
    } else {
        $message = "<div class='error'>Error deleting clinic.</div>";
    }
}

// Fetch all clinics
$clinics = $conn->query("SELECT * FROM clinics ORDER BY name");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clinics - HealthConnect Admin</title>
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
            <li><a href="admin_clinics.php" class="active">Clinics</a></li>
            <li><a href="admin_reports.php">Reports</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h1>🏥 Manage Clinics</h1>
            
            <?php echo $message; ?>
            
            <h3>Add New Clinic</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Clinic Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Opening Hours</label>
                        <input type="text" name="opening_hours" placeholder="Mon-Fri: 9AM-5PM">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <button type="submit" name="add_clinic" class="btn-primary">Add Clinic</button>
                <button type="submit" name="add_clinic" class="btn btn-primary">Add Clinic</button>
            </form>
        </div>

        <div class="card">
            <h3>Existing Clinics</h3>
            <div class="clinics-list">
                <?php while($clinic = $clinics->fetch_assoc()): ?>
                    <div class="clinic-card">
                        <h4><?php echo htmlspecialchars($clinic['name']); ?></h4>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($clinic['location']); ?></p>
                        <?php if ($clinic['phone']): ?>
                            <p><strong>Phone:</strong> <?php echo $clinic['phone']; ?></p>
                        <?php endif; ?>
                        <?php if ($clinic['opening_hours']): ?>
                            <p><strong>Hours:</strong> <?php echo $clinic['opening_hours']; ?></p>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $clinic['id']; ?>" class="btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this clinic?')" class="btn btn-danger">
                            Delete
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>