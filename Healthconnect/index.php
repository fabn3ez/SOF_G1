<?php
// index.php - HealthConnect Landing Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthConnect | Riverside Community Health Network</title>
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
        
        .navbar-links a {
            color: white;
            margin-left: 1.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }
        
        .navbar-links a:hover {
            opacity: 0.8;
        }
        
        .hero {
            text-align: center;
            padding: 4rem 1.5rem;
            background: white;
            margin: 2rem auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            max-width: 1000px;
        }
        
        .hero h1 {
            color: #4facfe;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .hero p {
            color: #555;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        
        .features {
            max-width: 1100px;
            margin: 3rem auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 0 2rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: #4facfe;
            margin-bottom: 1rem;
        }
        
        .how-it-works {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .how-it-works h2 {
            color: #4facfe;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            text-align: center;
        }
        
        .step {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #4facfe;
        }
        
        .contact {
            max-width: 900px;
            margin: 3rem auto;
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .contact h2 {
            color: #4facfe;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        input, textarea {
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
        }
        
        footer {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">HealthConnect</div>
        <div class="navbar-links">
            <a href="#features">Features</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#contact">Contact</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <section class="hero">
        <h1>Welcome to HealthConnect</h1>
        <p>Book appointments faster, reduce waiting times, and keep care efficient and human. HealthConnect connects patients, clinics, and administrators through one seamless system.</p>
        <a href="signup.php" class="btn-primary">Get Started</a>
    </section>

    <section id="features" class="features">
        <div class="feature-card">
            <div class="feature-icon">🩺</div>
            <h3>Online Appointments</h3>
            <p>Patients can easily book, reschedule, or cancel appointments from anywhere.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>SMS Reminders</h3>
            <p>Automatic notifications reduce missed appointments and waiting lines.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Clinic Dashboards</h3>
            <p>Doctors and staff can manage schedules, attendance, and reports in real time.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔒</div>
            <h3>Secure Records</h3>
            <p>All data is encrypted and accessible only by authorized healthcare staff.</p>
        </div>
    </section>

    <section id="how-it-works" class="how-it-works">
        <h2>How It Works</h2>
        <div class="steps">
            <div class="step">
                <h4>1. Patient Registers</h4>
                <p>Create a secure profile and choose a clinic.</p>
            </div>
            <div class="step">
                <h4>2. Book Appointment</h4>
                <p>Select your preferred date and time slot easily.</p>
            </div>
            <div class="step">
                <h4>3. Receive SMS Reminder</h4>
                <p>Get notified before your appointment, no more missed visits!</p>
            </div>
            <div class="step">
                <h4>4. Clinic Manages Flow</h4>
                <p>Staff update records and generate reports efficiently.</p>
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <h2>Contact Us</h2>
        <form action="contact.php" method="post">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email (optional)">
            <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
            <button class="btn-primary" type="submit">Send Message</button>
        </form>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> HealthConnect |Community Health Network</p>
    </footer>

</body>
</html>
