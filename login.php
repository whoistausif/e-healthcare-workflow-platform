<?php
ob_start();
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; 

    // Validate role
    if ($role == "patient") {
        $table = "pateint"; // Note: Check spelling in your DB
    } elseif ($role == "doctor") {
        $table = "doctor";
    } elseif ($role == "admin") {
        $table = "admin";
    } else {
        die("Invalid role selected.");
    }

    // Prepare and execute SELECT query
    $query = "SELECT * FROM $table WHERE username=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($stmt->error) {
        die("Query Error: " . $stmt->error);
    }

    // Check if user exists
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashedPasswordFromDB = $row['password'];

        // ✅ Use password_verify to compare plain and hashed password
        if (password_verify($password, $hashedPasswordFromDB)) {
            // Login success
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            if ($role == "patient") {
                header("Location: pateint.php");
            } elseif ($role == "doctor") {
                header("Location: doctor.php");
            } elseif ($role == "admin") {
                header("Location: admin.php");
            }
            exit();
        } else {
            echo "<script>alert('❌ Incorrect Password!');</script>";
        }
    } else {
        echo "<script>alert('❌ User Not Found!');</script>";
    }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E- Healthcare Workflow platform </title>

    <script src="https://kit.fontawesome.com/c1df782baf.js"></script>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.1.0/uicons-thin-rounded/css/uicons-thin-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.1.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel="stylesheet" href="style.css">
<style>
    body {
    font-family: 'IBM Plex Sans', sans-serif;
    margin: 0;
    padding: 0;
    background-image: url('images/bg blur2.jpg');
    background-size: cover;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

/* Header */
header {
    width: 100%;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 60px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    position: absolute;
    top: 0;
}

.logo img {
   
    width: 200px;
}

.navbar a {
    text-decoration: none;
    color: #333;
    margin: 0 15px;
    font-weight: bold;
}

.right-icons .btn {
    background: #0062cc;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 10px;
}

/* Bigger Login Box */
.login-container {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
    text-align: center;
    width: 450px; /* Increased width */
    margin-top: 80px;
}

.login-container h2 {
    margin-bottom: 25px;
    font-size: 24px;
    color: #333;
}

/* Tab Styling */
.tab {
    display: flex;
    justify-content: space-around;
    border-radius: 12px;
    padding: 12px ;
    margin-bottom: 20px;
    gap: 10px;
}

.tab button {
    background: #e3e6ea;
    border: none;
    padding: 14px 30px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    border-radius: 10px;
    transition: all 0.3s ease-in-out;
    outline: none;
    color: #333;
    position: relative;
    overflow: hidden;
}

.tab button.active {
    background: #0062cc;
    color: white;
}

/* Tab Content */
.tabcontent {
    display: none;
}

/* Bigger Input Fields */
.login-container input {
    width: 100%;
    padding: 14px;
    margin: 12px 0;
    border: 2px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
}

/* Bigger Login Button */
.login-container button {
    width: 100%;
    padding: 14px;
    background: #0062cc;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    transition: 0.3s;
}

.login-container button:hover {
    background:rgb(1, 251, 255);
    color: #0f1010;
}

.login-container p {
    margin-top: 15px;
    font-size: 14px;
}

.login-container a {
    text-decoration: none;
    color:#0f1010;
    font-weight: bold;
    font-size: 14px;
}

</style>

</head>


<body> 

    <header>
        
        <div class="logo"><img src="images/logo3.png" alt=""></div>

        <nav class="navbar">
            <a href="index.php">Go to Home</a>
            
          
        </nav>
    </header>


    <div class="login-container">
        <h2>Login</h2>
        <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'patient')">Patient</button>
            <button class="tablinks" onclick="openTab(event, 'doctor')">Doctor</button>
            <button class="tablinks" onclick="openTab(event, 'admin')">Admin</button>
        </div>

        <!-- Patient Login -->
        <div id="patient" class="tabcontent active">
            <form action="login.php" method="post">
            <input type="hidden" name="role" value="patient">
                <input type="text" name="username" placeholder="Patient Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>

        <!-- Doctor Login -->
        <div id="doctor" class="tabcontent">
            <form action="login.php" method="post">
            <input type="hidden" name="role" value="doctor">
                <input type="text" name="username" placeholder="Doctor Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>

        <!-- Admin Login -->
        <div id="admin" class="tabcontent">
            <form action="login.php" method="post">
            <input type="hidden" name="role" value="admin">
                <input type="text" name="username"  placeholder="Admin Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>

       
    </div>

    <script>
        function openTab(evt, tabName) {
            let i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active");
        }

        // Show Patient Login by default
        document.getElementById("patient").style.display = "block";

    </script>
</body>
</html>
