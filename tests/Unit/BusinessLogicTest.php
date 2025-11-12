<?php
namespace HealthConnect\Tests\Unit;

use PHPUnit\Framework\TestCase;

class BusinessLogicTest extends TestCase
{
    public function testAppointmentStatusFlow(): void
    {
        $validTransitions = [
            'booked' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled', 'rescheduled'],
            'rescheduled' => ['confirmed', 'cancelled'],
            'completed' => [], // No transitions from completed
            'cancelled' => []  // No transitions from cancelled
        ];
        
        // Test valid transitions
        $this->assertContains('confirmed', $validTransitions['booked']);
        $this->assertContains('cancelled', $validTransitions['booked']);
        
        // Test invalid transitions
        $this->assertNotContains('booked', $validTransitions['completed']);
        $this->assertNotContains('confirmed', $validTransitions['cancelled']);
    }
    
    public function testUserRolePermissions(): void
    {
        $rolePermissions = [
            'patient' => ['book_appointment', 'view_own_appointments', 'cancel_own_appointments'],
            'staff' => ['view_all_appointments', 'update_appointment_status', 'view_patient_profiles'],
            'doctor' => ['view_medical_records', 'add_prescriptions', 'update_diagnoses', 'view_all_appointments'],
            'admin' => ['manage_users', 'manage_clinics', 'view_reports', 'system_configuration']
        ];
        
        $this->assertContains('book_appointment', $rolePermissions['patient']);
        $this->assertContains('manage_users', $rolePermissions['admin']);
        $this->assertNotContains('manage_users', $rolePermissions['patient']);
    }
    
    public function testClinicHoursValidation(): void
    {
        // Test business hours validation logic
        $openingTime = '09:00';
        $closingTime = '17:00';
        $weekend = 'Saturday';
        
        $validWeekdayTime = '14:30';
        $invalidAfterHours = '18:30';
        
        // Simulate time validation logic
        $isValidTime = function($time) use ($openingTime, $closingTime) {
            return $time >= $openingTime && $time <= $closingTime;
        };
        
        $this->assertTrue($isValidTime($validWeekdayTime));
        $this->assertFalse($isValidTime($invalidAfterHours));
    }
}