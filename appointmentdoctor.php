<?php
include 'connection.php';
session_start();
// Check form status
$statusQuery = $conn->query("SELECT value FROM settings WHERE name = 'doctor_form_status'");
$form_status = ($statusQuery && $statusQuery->num_rows > 0) ? $statusQuery->fetch_assoc()['value'] : 'closed';

if ($form_status === 'closed') {
    echo "<h2 style='text-align:center; color: red;'>Doctor recruitment is currently closed.</h2>";
    exit();
}
// Check application form status
$formStatus = 'open';
$statusCheck = $conn->query("SELECT value FROM settings WHERE name='doctor_application'");
if ($statusCheck && $statusCheck->num_rows > 0) {
    $formStatus = $statusCheck->fetch_assoc()['value'];
}

// Handle form submission if open
if ($_SERVER["REQUEST_METHOD"] == "POST" && $formStatus === 'open') {
    $first_name = trim($_POST['first_name']);
    $surname = trim($_POST['surname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $gender = trim($_POST['gender']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if (empty($first_name) || empty($surname) || empty($username) || empty($email) ||
        empty($gender) || empty($phone) || empty($country) || empty($password)) {
        die("Error: All fields are required!");
    }

    if ($password !== $confirmPassword) {
        die("Error: Passwords do not match!");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $check_query = "SELECT * FROM doctor WHERE username='$username' OR email='$email'";
    $result = $conn->query($check_query);
    if ($result->num_rows > 0) {
        die("Error: Username or Email already exists!");
    }

    $sql = "INSERT INTO doctor (first_name, surname, username, email, gender, phone, country, password, status, date_reg) 
            VALUES ('$first_name', '$surname', '$username', '$email', '$gender', '$phone', '$country', '$hashedPassword', 'pending', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Doctor registered successfully. Wait for Admin Approval!'); location.href='appointmentdoctor.php';</script>";
        exit();
    } else {
        die("Error: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Healthcare Workflow Platform</title>
    <script src="https://kit.fontawesome.com/c1df782baf.js"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/bg blur2.jpg');
            background-size: cover;
            background-position: center;
            font-family: 'IBM Plex Sans', sans-serif;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        .logo img {
            max-height: 50px;
        }
        .navbar a {
            margin-left: 20px;
            text-decoration: none;
            font-size: 18px;
            color: #333;
        }
        .form-container {
            width: 60%;
            max-width: 600px;
            height: auto;
            overflow-y: auto;
            margin-top: 120px;
            padding: 20px;
            font-size: 18px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 100px;
        }
        .form-label {
            font-size: 18px;
        }
        .form-control, .form-select {
            font-size: 16px;
            padding: 10px;
        }
        .btn {
            font-size: 18px;
            padding: 12px;
        }
        ::-webkit-scrollbar {
            display: none;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            .navbar {
                text-align: center;
                width: 100%;
            }
            .navbar a {
                display: block;
                margin: 10px 0;
            }
            .form-container {
                max-width: 100%;
                padding: 20px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="logo"><img src="images/logo3.png" alt="Logo"></div>
    <nav class="navbar">
        <a href="index.php">Go to Home</a>
    </nav>
</header>

<div class="container d-flex justify-content-center align-items-center min-vh-100 px-3">
    <div class="bg-white p-5 rounded shadow-lg form-container">
        <?php if ($formStatus === 'open') { ?>
            <h2 class="text-center text-4xl font-bold mb-4">Doctor Apply Form</h2>
            <form id="appointmentForm" method="post" action="appointmentdoctor.php">
                <div class="mb-3"><label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="first_name" required>
                </div>
                <div class="mb-3"><label class="form-label">Surname</label>
                    <input type="text" class="form-control" name="surname" required>
                </div>
                <div class="mb-3"><label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3"><label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3"><label class="form-label">Gender</label>
                    <select class="form-select" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" name="phone" required>
                </div>
                <div class="mb-3"><label class="form-label">Country</label>
                    <select class="form-select" name="country" required>
                        <option value="">Select Country</option>
                        <option value="india">India</option>
                        <option value="usa">USA</option>
                        <option value="uk">UK</option>
                        <option value="canada">Canada</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3"><label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </form>
            <p class="text-center mt-3">Already have an account? <a href="login.php" class="text-primary">Login</a></p>
        <?php } else { ?>
            <h2 class="text-center text-3xl font-bold text-danger">🚫 Applications are currently closed!</h2>
            <p class="text-center mt-3">Please check back later or contact the admin for more information.</p>
        <?php } ?>
    </div>
</div>

<script>
document.getElementById('appointmentForm')?.addEventListener('submit', function(event) {
    const pass = document.querySelector('input[name="password"]').value;
    const confirm = document.querySelector('input[name="confirmPassword"]').value;
    if (pass !== confirm) {
        alert("Passwords do not match!");
        event.preventDefault();
    }
});
</script>
</body>
</html>
