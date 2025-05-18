<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$successMessage = "";
$errorMessage = "";
$reportSuccess = "";
$reportError = "";
$msg = "";

// Get Patient info
$stmt = $conn->prepare("SELECT username, profile FROM pateint WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$patientName = $patient['username'];
$patientPic = $patient['profile'] ?: 'default.jpg';

// Get Patient ID
$idQuery = $conn->prepare("SELECT id, profile FROM pateint WHERE username = ?");
$idQuery->bind_param("s", $username);
$idQuery->execute();
$patientData = $idQuery->get_result()->fetch_assoc();
$patientId = $patientData['id'];
$patientPic = $patientData['profile'] ?: 'default.jpg';

// Handle profile update
if (isset($_POST['update'])) {
    $newUsername = trim($_POST['username']);
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $profile = $_FILES['profile'];

    if (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $errorMessage = "❌ Passwords do not match.";
    } else {
        $profileName = $patientPic;
        if (!empty($profile['name']) && $profile['error'] === 0) {
            $ext = pathinfo($profile['name'], PATHINFO_EXTENSION);
            $profileName = uniqid("profile_") . "." . $ext;
            move_uploaded_file($profile['tmp_name'], "images/" . $profileName);
        }

        $query = "UPDATE pateint SET username = ?, profile = ? WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $newUsername, $profileName, $username);

        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $stmt->bind_param("ssss", $newUsername, $profileName, $hashedPassword, $username);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $newUsername;
            $successMessage = "✅ Profile updated successfully!";
        } else {
            $errorMessage = "❌ Failed to update profile.";
        }
    }
}

// Handle report submission
if (isset($_POST['send_report'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $date_send = date("Y-m-d");

    if (!empty($title) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO report (username, title, message, date_send) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $title, $message, $date_send);
        if ($stmt->execute()) {
            $reportSuccess = "✅ Report sent successfully!";
        } else {
            $reportError = "❌ Failed to send report.";
        }
    } else {
        $reportError = "❌ Title and message are required.";
    }
}
// Handle appointment booking
if (isset($_POST['submit'])) {
  $first = $_POST['first_name'];
  $surname = $_POST['surname'];
  $gender = $_POST['gender'];
  $phone = $_POST['phone'];
  $date = $_POST['appointment_date'];
  $symptoms = $_POST['symptoms'];
  $doctor_id = $_POST['doctor_name']; // Get the selected doctor
  $status = "Pending";
  $date_booked = date("Y-m-d");

  if (isset($_POST['consent'])) {
      // Insert appointment data
      $sql = "INSERT INTO appointment (first_name, surname, gender, phone, appointment_date, symptoms, status, date_booked, doctor_id)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssssssi", $first, $surname, $gender, $phone, $date, $symptoms, $status, $date_booked, $doctor_id);

      if ($stmt->execute()) {
          $msg = "Appointment booked successfully!";
          echo "<script>alert('$msg'); window.location='pateint.php';</script>";
          exit();
      } else {
          $msg = "Error: " . $stmt->error;
      }
  } else {
      $msg = "You must agree to the consent terms.";
  }
}

// Fetch doctors (must happen BEFORE closing the connection!)
$doctors = [];
$sql = "SELECT id, first_name, surname FROM doctor";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
      $doctors[] = $row;
  }
}







$query = "
    SELECT 
        appointment.id AS appointment_id,
        appointment.appointment_date,
        doctor.first_name AS doctor_first_name,
        doctor.surname AS doctor_surname,
        income.amount_paid
    FROM appointment
    LEFT JOIN doctor ON appointment.doctor_id = doctor.id
    LEFT JOIN income ON income.patient = CONCAT(appointment.first_name, ' ', appointment.surname)
    WHERE appointment.first_name = ? 
    AND appointment.status = 'Checked'
    ORDER BY appointment.appointment_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username); // Assuming $username holds the current user's first name
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Dashboard</title>
  <script src="https://kit.fontawesome.com/c1df782baf.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-nLjIyyX1OPRiVg6vOOrAjMxN7D3+IBx5G8b5Oxu7RStTNoiAqgJc3JodO1K2jQ2tMlRnQqE0XRFrf6I1eWz02w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="pateint.css">

</head>
<body>

<!-- Header -->
<header>
  <div class="logo">
    <img src="images/logo3.png" alt="Logo">
  </div>
  <div class="patient-info">
    <img src="images/<?php echo htmlspecialchars($patientPic); ?>" alt="Profile" />
    <div class="welcome-text">Welcome, <?php echo htmlspecialchars($patientName); ?></div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</header>

<!-- Sidebar -->
<div class="sidebar">
  <a href="#" onclick="event.preventDefault(); showSection('dashboard')">
    <i class="fas fa-yin-yang"></i> Dashboard
  </a>
  <a href="#" onclick="event.preventDefault(); showSection('profile')">
    <i class="fa-solid fa-user"></i> Profile
  </a>
  <a href="#" onclick="event.preventDefault(); showSection('book-appointment')">
    <i class="fa-solid fa-calendar-check"></i> Book Appointment
  </a>
  <a href="#" onclick="event.preventDefault(); showSection('send-report')">
    <i class="fa fa-file-medical-alt"></i> Send Report
  </a>
  <a href="#" onclick="event.preventDefault(); showSection('invoice')">
    <i class="fa-solid fa-file-invoice-dollar"></i> Invoice
  </a>
</div>

<div id="dashboard" class="section main-content grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-4">
<div class=" card col-span-2" style="pointer-events: none; background-color: white; ">
  <h3 class="text-xl font-semibold mb-3"><i class="fa-solid fa-heart-pulse mr-2 text-blue-600"></i>Health Trend: Blood Pressure</h3>
  <canvas id="bpChart" width="0" height="200"></canvas>
</div>
<script>
const ctx = document.getElementById('bpChart').getContext('2d');

// Gradient for Systolic
const gradientSys = ctx.createLinearGradient(0, 0, 0, 400);
gradientSys.addColorStop(0, 'rgba(54, 162, 235, 0.3)');
gradientSys.addColorStop(1, 'rgba(54, 162, 235, 0)');

// Gradient for Diastolic
const gradientDia = ctx.createLinearGradient(0, 0, 0, 400);
gradientDia.addColorStop(0, 'rgba(255, 99, 132, 0.3)');
gradientDia.addColorStop(1, 'rgba(255, 99, 132, 0)');

const bpChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
          '7 AM', '8 AM', '9 AM', '10 AM', '11 AM', '12 PM',
          '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM',
          '7 PM', '8 PM', '9 PM'
        ],
        datasets: [
            {
                label: 'Systolic BP',
                data: [120, 122, 118, 121, 119, 117, 116, 118, 120, 121, 119, 117, 116, 118, 120],
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: gradientSys,
                tension: 0.4,
                paddings: 10,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true
            },
            {
                label: 'Diastolic BP',
                data: [80, 79, 77, 78, 76, 74, 73, 75, 77, 78, 76, 74, 73, 75, 77],
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: gradientDia,
                tension: 0.4,
                paddings: 10,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            tooltip: {
                backgroundColor: '#fff',
                titleColor: '#333',
                bodyColor: '#555',
                borderColor: '#ddd',
                borderWidth: 1,
                titleFont: { weight: 'bold' },
                padding: 10
            },
            legend: {
                display: true,
                labels: {
                    color: '#333',
                    boxWidth: 12,
                    usePointStyle: true
                }
            }
        },
        hover: {
            mode: null // disables blue hover line
        },
        scales: {
            x: {
                ticks: {
                    color: '#555'
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                }
            },
            y: {
                beginAtZero: false,
                ticks: {
                    color: '#555',
                    stepSize: 10
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                },
                title: {
                    display: true,
                    text: 'mmHg'
                }
            }
        }
    }
});

</script>

  <!-- 1. My Profile -->
  <div class="card">
    <i class="fa-solid fa-user"></i>
    <h3>My Profile</h3>
    <p>View your profile picture, name, and basic info.</p>
    <a href="#" onclick="showSection('profile')" class="btn btn-sm btn-primary mt-2">Edit Profile</a>
  </div>

  <!-- 2. Upcoming Appointments -->
  <div class="card">
    <i class="fa-solid fa-calendar-check"></i>
    <h3>Upcoming Appointments</h3>
    <p>Next appointment on April 12, 2025 with Dr. Smith.</p>
    <a href="#" onclick="showSection('book-appointment')" class="btn btn-sm btn-primary mt-2">View All / reschedule</a>
  </div>

  <!-- 3. Health Overview -->
  <div class="card">
    <i class="fa-solid fa-heart-pulse"></i>
    <h3>Health Overview</h3>
    <p>BP: 120/80 | HR: 75 BPM | BMI: 23.4</p>
    <small>Latest checkup data</small>
  </div>

  <!-- 4. Reports -->
  <div class="card">
    <i class="fa-solid fa-file-medical"></i>
    <h3>Reports</h3>
    <p>Send report.</p>
    <a href="#" onclick="showSection('send-report')" class="btn btn-sm btn-primary mt-2"> Send reports</a>
  </div>



  <!-- 6. Invoices / Bills -->
  <div class="card">
    <i class="fa-solid fa-file-invoice-dollar"></i>
    <h3>Invoices </h3>
    <p>See Your Invoice </p>
    <a href="#" onclick="showSection('invoice')" class="btn btn-sm btn-primary mt-2">Check Invoice</a>
  </div>

  <!-- 7. Book Appointment -->
  <div class="card">
    <i class="fa-solid fa-calendar-plus"></i>
    <h3>Book Appointment</h3>
    <p>Quickly schedule a visit with your doctor.</p>
    <a href="#" onclick="showSection('book-appointment')" class="btn btn-sm btn-primary mt-2">Book Now</a>
  </div>

  <!-- 12. Emergency Contact -->
  <div class="card">
    <i class="fa-solid fa-phone-volume"></i>
    <h3>Emergency Contact</h3>
    <p>Call: 123-456-7890</p>
    <button class="btn btn-sm btn-danger mt-2">Call Now</button>
  </div>

</div>


<!-- Profile Update Form -->
<div id="profile" class="section profile-section">
  <?php if (!empty($successMessage)) : ?>
    <script>alert("<?= $successMessage ?>");</script>
  <?php endif; ?>

  <?php if (!empty($errorMessage)) : ?>
    <script>alert("<?= $errorMessage ?>");</script>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="profile-form">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($patient['username']); ?>" required>
    </div>

    <div class="form-group">
      <label>Profile Picture</label>
      <div class="profile-pic-preview">
        <img src="images/<?php echo htmlspecialchars($patientPic); ?>" alt="Profile" style="width:100px;height:100px;">
      </div>
      <input type="file" name="profile" accept="image/*">
    </div>

    <div class="form-group">
      <label>New Password</label>
      <input type="password" name="password" placeholder="Enter new password">
    </div>

    <div class="form-group">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" placeholder="Confirm new password">
    </div>

    <button type="submit" name="update">Update Profile</button>
  </form>
</div>


<section id="book-appointment" class="section" style="display: flex; justify-content: center; align-items: center; padding: 50px 15px; min-height: 100vh;margin-left: 300px; margin-top: 50px;">
    <div style="background: #fff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.08); width: 100%; max-width: 900px;">

      <img src="images/logo3.png" alt="Clinic Logo" style="display: block; margin: 0 auto 20px; height: 100px;">

      <h2 style="text-align: center; font-size: 22px; font-weight: bold;    color: #333; margin-bottom: 30px;">Book a Appointment</h2>

      <form method="post" action="pateint.php">

        <label for="first_name" style="display: block; font-weight: 600; margin-bottom: 6px; color: #333;">Username:</label>
        <input type="text" name="first_name" required style="width: 100%; padding: 12px; border: 1px solid #8bc34a; border-radius: 6px; margin-bottom: 20px;">

        <label for="surname" style="display: block; font-weight: 600; margin-bottom: 6px; color: #333;"> Name:</label>
        <input type="text" name="surname" required style="width: 100%; padding: 12px; border: 1px solid #8bc34a; border-radius: 6px; margin-bottom: 20px;">

        <label for="appointment_date" style="display: block; font-weight: 600; margin-bottom: 6px; color: #333;">Appointment Date:</label>
        <input type="date" name="appointment_date" required style="width: 100%; padding: 12px; border: 1px solid #8bc34a; border-radius: 6px; margin-bottom: 20px;">

      
        <label for="doctor_name" style="display: block; font-weight: 600; margin-bottom: 6px; color: #333;">Doctor:</label>
<select name="doctor_name" required style="width: 100%; padding: 12px; border: 1px solid #8bc34a; border-radius: 6px; margin-bottom: 20px;">
    <option value="">Select Doctor</option>
    <?php foreach ($doctors as $doctor): ?>
        <option value="<?= htmlspecialchars($doctor['id']) ?>">
            <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['surname']) ?>
        </option>
    <?php endforeach; ?>
</select>




        <label for="gender" style="display: block; font-weight: 600; margin-bottom: 6px; color: #333;">Gender:</label>
        <select name="gender" required style="width: 100%; padding: 12px; border: 1px solid #8bc34a; border-radius: 6px; margin-bottom: 20px;">
          <option value="">Select gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>

        <label for="phone" style="display: block; font-weight: 600; margin-bottom: 6px; color: #333;">Phone Number:</label>
        <input type="text" name="phone" required pattern="[0-9]{10}" style="width: 100%; padding: 12px; border: 1px solid #8bc34a; border-radius: 6px; margin-bottom: 20px;">

        <label for="symptoms" style="display: block; font-weight: 600; margin-bottom: 6px; color: #333;">Symptoms:</label>
        <textarea name="symptoms" rows="3" required style="width: 100%; padding: 12px; border: 1px solid #8bc34a; border-radius: 6px; margin-bottom: 20px;"></textarea>

        <label style="display: flex; align-items: center; font-size: 14px; color: #444; margin-bottom: 20px;">
          <input type="checkbox" name="consent" required style="margin-right: 10px; transform: scale(1.2);">
          I agree to the Terms, Privacy Policy, and Telehealth Consent.
        </label>

        <button type="submit" name="submit" style="width: 100%; padding: 12px; background-color:#009dff; color: #fff; font-size: 16px; border: none; border-radius: 6px; cursor: pointer;">
          Book Appointment
        </button>

      </form>
    </div>
  </section>











<!-- report section -->
<div id="send-report" class="section send-report-section">
  <h2 class="text-2xl font-semibold mb-4"><i class="fa fa-file-medical-alt text-blue-600"></i> Send a Report</h2>

  <?php if (!empty($reportSuccess)) : ?>
    <div class="alert alert-successalert alert-success"><?php echo $reportSuccess; ?></div>
  <?php endif; ?>

  <?php if (!empty($reportError)) : ?>
    <div class="alert alert-danger"><?php echo $reportError; ?></div>
  <?php endif; ?>

  <form method="POST" class="report-form p-4 bg-white rounded shadow max-w-lg">
    <div class="form-group mb-3">
      <label for="title" class="form-label">Report Title</label>
      <input type="text" name="title" id="title" class="form-control" required>
    </div>

    <div class="form-group mb-3">
      <label for="message" class="form-label">Message</label>
      <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
    </div>

    <button type="submit" name="send_report" class="btn btn-primary">Send Report</button>
  </form>
</div>






<div id="invoice" class="section" style="margin: 40px auto; width: 80%; padding: 20px; background-color: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 10px; margin-left: 270px; margin-top: 100px;">
    <h2 style="text-align: left; color: rgb(0, 0, 0); margin-bottom: 30px; font-weight: bold; font-size: 24px;">Your Checked Appointments</h2>

    <?php if (!empty($appointments)) { ?>
        <ul style="list-style-type: none; padding: 0;">
            <?php foreach ($appointments as $row) { ?>
                <li style="margin-bottom: 20px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #f9f9f9;">
                    <p style="margin: 0; font-size: 18px; color: #333;">
                        <strong>Doctor:</strong> Dr. <?php echo $row['doctor_first_name'] . " " . $row['doctor_surname']; ?>
                    </p>
                    <p style="margin: 5px 0; font-size: 16px; color: #555;">
                        <strong>Date:</strong> <?php echo date("d M Y", strtotime($row['appointment_date'])); ?>
                    </p>
                    <p style="margin: 5px 0; font-size: 16px; color: #555;">
                        <strong>Amount Paid:</strong> Rs <?php echo number_format($row['amount_paid'], 2); ?>
                    </p>

                    <a href="view_invoice.php?appointment_id=<?php echo $row['appointment_id']; ?>" 
                       style="display: inline-block; margin-top: 10px; color: white; background-color: #009dff; padding: 8px 15px; text-decoration: none; border-radius: 4px;">
                       View Invoice
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p style="text-align: center; font-size: 18px; color: #999;">No checked appointments found.</p>
    <?php } ?>
</div>





<script>
function showSection(sectionId) {
    const allSections = document.querySelectorAll('.section');
    allSections.forEach(section => section.style.display = 'none');
    const target = document.getElementById(sectionId);
    if (target) {
      target.style.display = sectionId === 'dashboard' ? 'grid' : 'block';
    }
}
document.addEventListener("DOMContentLoaded", () => {
    showSection('dashboard');
});

</script>

</body>
</html>
