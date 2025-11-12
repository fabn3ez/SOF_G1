<?php
namespace HealthConnect\Tests\Integration;

use PHPUnit\Framework\TestCase;
use HealthConnect\Tests\TestHelper;

class DatabaseTest extends TestCase
{
    private $connection;

    protected function setUp(): void
    {
        $this->connection = new \mysqli('localhost', 'root', '1234', 'healthconnect');
        TestHelper::cleanupTestData();
    }

    public function testDatabaseConnection(): void
    {
        $this->assertTrue($this->connection->connect_errno === 0, 
            "Database connection failed: " . $this->connection->connect_error);
    }

    public function testRequiredTablesExist(): void
    {
        $tables = ['users', 'clinics', 'appointments'];
        
        foreach ($tables as $table) {
            $result = $this->connection->query("SHOW TABLES LIKE '$table'");
            $this->assertEquals(1, $result->num_rows, "Table '$table' does not exist");
        }
    }

    public function testUserRegistration(): void
    {
        $email = TestHelper::generateTestEmail();
        $name = "Test User";
        $password = password_hash("test123", PASSWORD_BCRYPT);
        $role = "patient";

        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        
        $result = $stmt->execute();
        
        $this->assertTrue($result, "User registration failed");
        $this->assertEquals(0, $stmt->errno, "SQL error: " . $stmt->error);
        
        $stmt->close();
    }

    protected function tearDown(): void
    {
        TestHelper::cleanupTestData();
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>