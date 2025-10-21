<?php
session_start();

$allowed_roles = ['staff', 'doctor', 'nurse'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
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

// Handle appointment status updates
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

// Handle adding medical notes
if (isset($_POST['add_notes'])) {
    $appointment_id = $_POST['appointment_id'];
    $notes = trim($_POST['notes']);
    
    $sql = "UPDATE appointments SET notes = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $notes, $appointment_id);
    
    if ($stmt->execute()) {
        $message = "<div class='success'>Notes added successfully.</div>";
    } else {
        $message = "<div class='error'>Error adding notes.</div>";
    }
}

// Fetch appointments
$appointments = $conn->query("
    SELECT a.*, u.name as patient_name, u.email as patient_email, u.phone as patient_phone,
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
    <title>Manage Appointments - HealthConnect Staff</title>
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
            <li><a href="staff_appointments.php" class="active">Appointments</a></li>
            <li><a href="staff_patients.php">Patients</a></li>
            <li><a href="staff_profile.php">My Profile</a></li>
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
                                <td>
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong><br>
                                    <small><?php echo $appointment['patient_email']; ?></small><br>
                                    <?php if ($appointment['patient_phone']): ?>
                                        <small>📞 <?php echo $appointment['patient_phone']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($appointment['clinic_name']); ?><br>
                                    <small><?php echo htmlspecialchars($appointment['location']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong><br>
                                    <small><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></small>
                                </td>
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
                                    <div class="action-cell">
                                        <button onclick="openNotesModal(<?php echo $appointment['id']; ?>, '<?php echo htmlspecialchars($appointment['notes'] ?? ''); ?>')" 
                                                class="btn btn-sm btn-primary">
                                            📝 Notes
                                        </button>
                                        <a href="?update_status=<?php echo $appointment['id']; ?>&status=completed" 
                                           class="btn btn-sm btn-success">✅ Complete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div id="notesModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: white; margin: 5% auto; padding: 2rem; border-radius: 15px; width: 90%; max-width: 500px;">
            <h3>Add Medical Notes</h3>
            <form method="POST" id="notesForm">
                <input type="hidden" name="appointment_id" id="appointmentId">
                <div class="form-group">
                    <label>Medical Notes:</label>
                    <textarea name="notes" id="appointmentNotes" rows="6" placeholder="Enter medical notes, observations, or follow-up instructions..." style="width: 100%; padding: 1rem; border: 2px solid #e1e5e9; border-radius: 8px;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" name="add_notes" class="btn btn-primary">Save Notes</button>
                    <button type="button" onclick="closeNotesModal()" class="btn btn-danger">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openNotesModal(appointmentId, currentNotes) {
            document.getElementById('appointmentId').value = appointmentId;
            document.getElementById('appointmentNotes').value = currentNotes;
            document.getElementById('notesModal').style.display = 'block';
        }
        
        function closeNotesModal() {
            document.getElementById('notesModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('notesModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotesModal();
            }
        });
    </script>
</body>
</html>