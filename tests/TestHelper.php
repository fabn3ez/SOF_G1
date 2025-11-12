<?php
namespace HealthConnect\Tests;

class TestHelper
{
    private static function getConnection(): \mysqli
    {
        // Credentials from phpunit.xml
        $host = 'localhost';
        $db   = 'healthconnect_db';
        $user = 'root';
        $pass = '1234';

        $conn = new \mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    public static function cleanupTestData(): void
    {
        $conn = self::getConnection();
        
        // Disable foreign key checks to truncate
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear out all test data from tables
        $conn->query('TRUNCATE TABLE appointments');
        $conn->query('TRUNCATE TABLE users');
        
        // Re-enable foreign key checks
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
        $conn->close();
    }

    public static function generateTestEmail(): string
    {
        return 'test-' . uniqid() . '@example.com';
    }
}