<?php
namespace HealthConnect\Tests\Feature;

use PHPUnit\Framework\TestCase;
use HealthConnect\Tests\TestHelper;

// *** THIS IS THE FIX ***
// The class name now matches the file name
class AppointmentBookingTest extends TestCase
{
    private $connection;

    protected function setUp(): void
    {
        // Use environment variables from phpunit.xml
        $this->connection = new \mysqli(
            $_SERVER['DB_HOST'], 
            $_SERVER['DB_USER'], 
            $_SERVER['DB_PASS'], 
            $_SERVER['DB_NAME']
        );
        TestHelper::cleanupTestData();
    }

    public function testCompleteAppointmentWorkflow(): void
    {
        // 1. Register a test user
        $userEmail = TestHelper::generateTestEmail();
        $userName = "Test Patient";
        $password = password_hash("test123", PASSWORD_BCRYPT);
        
        $userSql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'patient')";
        $userStmt = $this->connection->prepare($userSql);
        $userStmt->bind_param("sss", $userName, $userEmail, $password);
        $userStmt->execute();
        $userId = $this->connection->insert_id;
        $userStmt->close();
        
        $this->assertGreaterThan(0, $userId, "User creation failed");
        
        // 2. Get a clinic
        $clinicResult = $this->connection->query("SELECT id FROM clinics LIMIT 1");
        $this->assertTrue($clinicResult->num_rows > 0, "No clinics available");
        $clinic = $clinicResult->fetch_assoc();
        $clinicId = $clinic['id'];
        
        // 3. Book an appointment
        $appointmentDate = date('Y-m-d H:i:s', strtotime('+2 days'));
        $appointmentSql = "INSERT INTO appointments (user_id, clinic_id, appointment_date, status) VALUES (?, ?, ?, 'booked')";
        $appointmentStmt = $this->connection->prepare($appointmentSql);
        $appointmentStmt->bind_param("iis", $userId, $clinicId, $appointmentDate);
        $appointmentResult = $appointmentStmt->execute();
        $appointmentId = $this->connection->insert_id;
        $appointmentStmt->close();
        
        $this->assertTrue($appointmentResult, "Appointment booking failed");
        $this->assertGreaterThan(0, $appointmentId);
        
        // 4. Verify appointment was created
        $verifySql = "SELECT COUNT(*) as count FROM appointments WHERE user_id = ? AND clinic_id = ?";
        $verifyStmt = $this->connection->prepare($verifySql);
        $verifyStmt->bind_param("ii", $userId, $clinicId);
        $verifyStmt->execute();
        $verifyStmt->bind_result($count);
        $verifyStmt->fetch();
        $verifyStmt->close();
        
        $this->assertEquals(1, $count, "Appointment verification failed");
        
        // 5. Update appointment status (simulate confirmation)
        $updateSql = "UPDATE appointments SET status = 'confirmed' WHERE id = ?";
        $updateStmt = $this->connection->prepare($updateSql);
        $updateStmt->bind_param("i", $appointmentId);
        $updateResult = $updateStmt->execute();
        $updateStmt->close();
        
        $this->assertTrue($updateResult, "Appointment update failed");
        
        // 6. Verify status update
        $statusSql = "SELECT status FROM appointments WHERE id = ?";
        $statusStmt = $this->connection->prepare($statusSql);
        $statusStmt->bind_param("i", $appointmentId);
        $statusStmt->execute();
        $statusStmt->bind_result($status);
        $statusStmt->fetch();
        $statusStmt->close();
        
        $this->assertEquals('confirmed', $status, "Status update verification failed");
    }

    protected function tearDown(): void
    {
        TestHelper::cleanupTestData();
        if ($this->connection) {
            $this->connection->close();
        }
    }
}