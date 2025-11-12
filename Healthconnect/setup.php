<?php
// migration.php
// HealthConnect Database Migration Script

class DatabaseMigration {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    
    public function __construct($host = 'localhost', $username = 'root', $password = '1234', $database = 'healthconnect_db') {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }
    
    public function connect() {
        try {
            // First connect without database to create it if needed
            $this->connection = new mysqli($this->host, $this->username, $this->password);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            echo "✓ Connected to MySQL server successfully\n";
            return true;
        } catch (Exception $e) {
            die("✗ Connection error: " . $e->getMessage() . "\n");
        }
    }
    
    public function createDatabase() {
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->database . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        if ($this->connection->query($sql) === TRUE) {
            echo "✓ Database '{$this->database}' created or already exists\n";
            
            // Select the database
            $this->connection->select_db($this->database);
            return true;
        } else {
            throw new Exception("Error creating database: " . $this->connection->error);
        }
    }
    
    public function executeMigration() {
        $sql_commands = $this->getMigrationSQL();
        $errors = [];
        
        foreach ($sql_commands as $command) {
            // Skip empty lines and comments
            if (trim($command) === '' || strpos(trim($command), '--') === 0) {
                continue;
            }
            
            try {
                if ($this->connection->query($command) === TRUE) {
                    echo "✓ Executed: " . substr($command, 0, 50) . "...\n";
                } else {
                    $errors[] = "Error executing: " . substr($command, 0, 50) . "... - " . $this->connection->error;
                }
            } catch (Exception $e) {
                $errors[] = "Exception: " . substr($command, 0, 50) . "... - " . $e->getMessage();
            }
        }
        
        if (count($errors) > 0) {
            echo "\n⚠ Some errors occurred during migration:\n";
            foreach ($errors as $error) {
                echo "  - " . $error . "\n";
            }
            return false;
        } else {
            echo "\n🎉 Database migration completed successfully!\n";
            return true;
        }
    }
    
    private function getMigrationSQL() {
        return [
            "-- HealthConnect Database Schema Migration",
            
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                date_of_birth DATE,
                address TEXT,
                role ENUM('patient', 'staff', 'admin', 'doctor', 'nurse') DEFAULT 'patient',
                specialization VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_role (role)
            )",
            
            "CREATE TABLE IF NOT EXISTS clinics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                location VARCHAR(200) NOT NULL,
                phone VARCHAR(20),
                email VARCHAR(100),
                opening_hours VARCHAR(100),
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_name (name)
            )",
            
            "CREATE TABLE IF NOT EXISTS appointments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                clinic_id INT NOT NULL,
                appointment_date DATETIME NOT NULL,
                appointment_type ENUM('general', 'dental', 'eye_checkup', 'vaccination', 'follow_up', 'emergency') DEFAULT 'general',
                reason TEXT,
                status ENUM('booked', 'confirmed', 'rescheduled', 'cancelled', 'completed', 'no_show') DEFAULT 'booked',
                doctor_name VARCHAR(100),
                notes TEXT,
                duration_minutes INT DEFAULT 30,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_clinic_id (clinic_id),
                INDEX idx_appointment_date (appointment_date),
                INDEX idx_status (status)
            )",
            
            "CREATE TABLE IF NOT EXISTS appointment_reminders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                appointment_id INT NOT NULL,
                reminder_type ENUM('email', 'sms', 'both') DEFAULT 'email',
                reminder_sent_at TIMESTAMP NULL,
                reminder_scheduled_for TIMESTAMP NOT NULL,
                status ENUM('scheduled', 'sent', 'failed') DEFAULT 'scheduled',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
                INDEX idx_appointment_id (appointment_id),
                INDEX idx_status (status),
                INDEX idx_reminder_scheduled_for (reminder_scheduled_for)
            )",
            
            "CREATE TABLE IF NOT EXISTS prescriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                doctor_id INT NOT NULL,
                appointment_id INT,
                medication_name VARCHAR(255) NOT NULL,
                dosage VARCHAR(100),
                frequency VARCHAR(100),
                duration VARCHAR(100),
                instructions TEXT,
                prescribed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
                FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
                INDEX idx_patient_id (patient_id),
                INDEX idx_doctor_id (doctor_id),
                INDEX idx_status (status)
            )",
            
            "CREATE TABLE IF NOT EXISTS diagnoses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                doctor_id INT NOT NULL,
                appointment_id INT,
                diagnosis_code VARCHAR(50),
                diagnosis_name VARCHAR(255) NOT NULL,
                description TEXT,
                severity ENUM('mild', 'moderate', 'severe'),
                notes TEXT,
                diagnosed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
                INDEX idx_patient_id (patient_id),
                INDEX idx_doctor_id (doctor_id)
            )",
            
            "-- Insert sample data for testing",
            "INSERT IGNORE INTO users (name, email, password, role) VALUES 
                ('System Administrator', 'admin@healthconnect.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
                ('John Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient'),
                ('Dr. Sarah Wilson', 'dr.sarah@healthconnect.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor'),
                ('Nurse Jane Doe', 'nurse.jane@healthconnect.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nurse')",
            
            "INSERT IGNORE INTO clinics (name, location, phone, email, opening_hours) VALUES 
                ('Main Health Center', '123 Medical Drive, City Center', '(555) 123-4567', 'main@healthconnect.com', 'Mon-Fri: 8AM-8PM, Sat: 9AM-4PM'),
                ('Northside Dental Clinic', '456 Healthcare Ave, North District', '(555) 987-6543', 'dental@healthconnect.com', 'Mon-Sat: 8AM-6PM'),
                ('Westend Vision Care', '789 Wellness Street, West Area', '(555) 456-7890', 'vision@healthconnect.com', 'Tue-Sat: 9AM-5PM'),
                ('Downtown Pediatrics', '321 Child Care Lane, Downtown', '(555) 234-5678', 'pediatrics@healthconnect.com', 'Mon-Fri: 8AM-6PM'),
                ('Southside Emergency', '654 Emergency Road, South District', '(555) 876-5432', 'emergency@healthconnect.com', '24/7')",
            
            "INSERT IGNORE INTO appointments (user_id, clinic_id, appointment_date, appointment_type, reason, status) VALUES 
                (2, 1, DATE_ADD(NOW(), INTERVAL 2 DAY), 'general', 'Annual health checkup', 'booked'),
                (2, 2, DATE_ADD(NOW(), INTERVAL 5 DAY), 'dental', 'Regular dental cleaning', 'confirmed'),
                (2, 3, DATE_ADD(NOW(), INTERVAL 1 DAY), 'eye_checkup', 'Vision test and prescription update', 'booked')"
        ];
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Main execution
echo "🚀 HealthConnect Database Migration\n";
echo "===================================\n\n";

// You can modify these credentials to match your environment
$migration = new DatabaseMigration('localhost', 'root', '1234');

try {
    $migration->connect();
    $migration->createDatabase();
    $migration->executeMigration();
    $migration->close();
    
    echo "\n✅ Migration process completed!\n";
    echo "📊 Database: healthconnect_db\n";
    echo "📋 Tables created: users, clinics, appointments, appointment_reminders, prescriptions, diagnoses\n";
    echo "👥 Sample data inserted for testing\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    $migration->close();
    exit(1);
}
?>