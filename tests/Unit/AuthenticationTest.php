<?php
namespace HealthConnect\Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    public function testPasswordHashing(): void
    {
        $password = "patient123";
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        
        $this->assertTrue(password_verify($password, $hashed));
        $this->assertNotEquals($password, $hashed);
    }

    public function testEmailValidation(): void
    {
        $validEmail = "patient@example.com";
        $invalidEmail = "invalid-email";
        
        $this->assertTrue(filter_var($validEmail, FILTER_VALIDATE_EMAIL) !== false);
        $this->assertFalse(filter_var($invalidEmail, FILTER_VALIDATE_EMAIL) !== false);
    }

    public function testRoleValidation(): void
    {
        $validRoles = ['patient', 'staff', 'doctor', 'admin'];
        $invalidRole = 'invalid_role';
        
        $this->assertContains('patient', $validRoles);
        $this->assertNotContains($invalidRole, $validRoles);
    }
    
    public function testAppointmentDateValidation(): void
    {
        $futureDate = date('Y-m-d H:i:s', strtotime('+1 day'));
        $pastDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        
        $this->assertTrue(strtotime($futureDate) > time());
        $this->assertTrue(strtotime($pastDate) < time());
    }
}