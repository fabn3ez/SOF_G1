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

// Handle user actions
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Don't allow deleting admin users
    $check_sql = "SELECT role FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $user = $check_stmt->get_result()->fetch_assoc();
    
    if ($user && $user['role'] !== 'admin') {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = "<div class='success'>User deleted successfully.</div>";
        } else {
            $message = "<div class='error'>Error deleting user.</div>";
        }
    } else {
        $message = "<div class='error'>Cannot delete admin users.</div>";
    }
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - HealthConnect Admin</title>
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
            <li><a href="admin_users.php" class="active">Manage Users</a></li>
            <li><a href="admin_appointments.php">Appointments</a></li>
            <li><a href="admin_clinics.php">Clinics</a></li>
            <li><a href="admin_reports.php">Reports</a></li>
        </ul>
    </nav>
  <div class="container">
    <div class="card">
        <h1>👥 Manage Users</h1>
        
        <?php echo $message; ?>
        
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Search users by name, email, or role...">
            <div class="user-count">
                Total Users: <?php echo $users->num_rows; ?>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <?php if ($user['phone']): ?>
                                        <small style="color: #666;">📞 <?php echo $user['phone']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                                    <small style="color: #666;"><?php echo date('g:i A', strtotime($user['created_at'])); ?></small>
                                </td>
                                <td class="action-cell">
                                    <?php if ($user['role'] != 'admin'): ?>
                                        <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            🗑️ Delete
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #666; font-style: italic; font-size: 0.85rem;">System Admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="no-users">
                                    <div class="icon">👥</div>
                                    <h3>No Users Found</h3>
                                    <p>There are no users in the system yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>