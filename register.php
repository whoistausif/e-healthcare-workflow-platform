<?php
include 'connection.php'; 
session_start();

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = trim($conn->real_escape_string($_POST['first_name']));
    $surname = trim($conn->real_escape_string($_POST['surname']));
    $username = trim($conn->real_escape_string($_POST['username']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $gender = trim($conn->real_escape_string($_POST['gender']));
    $phone = trim($_POST['phone']); 
    $country = trim($conn->real_escape_string($_POST['country']));
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Validate required fields
    if (empty($first_name) || empty($surname) || empty($username) || empty($email) ||
        empty($gender) || empty($phone) || empty($country) || empty($password) || empty($confirmPassword)) {
        die("Error: All fields are required!");
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format!");
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        die("Error: Invalid phone number! Must be 10 digits.");
    }

    // Check password match
    if ($password !== $confirmPassword) {
        die("Error: Passwords do not match!");
    }

    // Check for existing username or email
    $check_query = "SELECT * FROM pateint WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("Error: Username or Email already exists!");
    }

    $stmt->close();

    $date_reg = date("Y-m-d H:i:s");  
    $default_profile = "default.jpg";
    
    // Hash the password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert patient into database
    $sql = "INSERT INTO pateint (first_name, surname, username, email, phone, gender, country, password, date_reg, profile) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisssss", $first_name, $surname, $username, $email, $phone, $gender, $country, $hashed_password, $date_reg, $default_profile);

    if ($stmt->execute()) {
        echo "<script>alert('Patient registered successfully!'); window.location.href='login.php';</script>";
    } else {
        die("Error: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E- Healthcare Workflow Platform</title>
    <script src="https://kit.fontawesome.com/c1df782baf.js"></script>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.1.0/uicons-thin-rounded/css/uicons-thin-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.1.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            height: 80vh; 
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
<body class="bg-gray-100">
    <header>
        <div class="logo"><img src="images/logo3.png" alt=""></div>
        <nav class="navbar">
            <a href="index.php">Go to Home</a>
        </nav>
    </header>
    <div class="container d-flex justify-content-center align-items-center min-vh-100 px-3">
        <div class="bg-white p-5 rounded shadow-lg form-container">
            <h2 class="text-center text-4xl font-bold mb-4">Create Patient Account</h2>
            <form id="patientAccountForm" method="post" action="register.php">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter your first name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Surname</label>
                    <input type="text" class="form-control" id="surname" name="surname" placeholder="Enter your surname" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Create a username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Country</label>
                    <select class="form-select" id="country" name="country" required>
                        <option value="">Select Country</option>
                        <option value="india">India</option>
                        <option value="usa">USA</option>
                        <option value="uk">UK</option>
                        <option value="canada">Canada</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </form>
            <p class="text-center mt-3">Already have an account? <a href="login.php" class="text-primary">Login</a></p>
        </div>
    </div>
    <script>
    document.getElementById('patientAccountForm').addEventListener('submit', function(event) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        event.preventDefault();
        return;
    }

   
    this.submit();
});


    </script>
</body>
</html>
