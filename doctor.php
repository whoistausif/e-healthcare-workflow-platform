<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$successMessage = "";
$errorMessage = "";

// Get Doctor info
$stmt = $conn->prepare("SELECT username, profile FROM doctor WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$doctorName = $doctor['username'];
$doctorPic = $doctor['profile'] ?: 'default.jpg';


// Fetch logged-in doctor ID
$doctorQuery = $conn->prepare("SELECT id, profile FROM doctor WHERE username = ?");
$doctorQuery->bind_param("s", $username);
$doctorQuery->execute();
$doctorData = $doctorQuery->get_result()->fetch_assoc();
$doctorId = $doctorData['id'];
$doctorPic = $doctorData['profile'] ?: 'default.jpg';

// Fetch dashboard stats for doctor
$stats = [
    "profilePic" => "<img src='images/{$doctorPic}' class='rounded-full w-16 h-16 object-cover' alt='Profile'>",
    
    "totalPatients" => $conn->query("SELECT COUNT(*) as count FROM pateint")->fetch_assoc()['count'],


    "totalAppointments" => $conn->query("SELECT COUNT(*) as count FROM appointment")->fetch_assoc()['count'],

    // Optional: Add more doctor-specific stats here
];


// Handle profile update
if (isset($_POST['update'])) {
    $newUsername = trim($_POST['username']);
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $profile = $_FILES['profile'];

    // Validate password match
    if (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $errorMessage = "❌ Passwords do not match.";
    } else {
        // Handle profile image upload
        $profileName = $doctorPic;
        if (!empty($profile['name']) && $profile['error'] === 0) {
            $ext = pathinfo($profile['name'], PATHINFO_EXTENSION);
            $profileName = uniqid("profile_") . "." . $ext;
            move_uploaded_file($profile['tmp_name'], "images/" . $profileName);
        }

        // Prepare update query
        $query = "UPDATE doctor SET username = ?, profile = ?";
        $params = [$newUsername, $profileName];
        $types = "ss";

        // If password is being updated
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $hashedPassword;
            $types .= "s";
        }

        $query .= " WHERE username = ?";
        $params[] = $username;
        $types .= "s";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $_SESSION['username'] = $newUsername;
            $successMessage = "✅ Profile updated successfully!";
        } else {
            $errorMessage = "❌ Failed to update profile.";
        }
    }
}


// Fetch doctor info
$stmt = $conn->prepare("SELECT id,username, first_name, surname, phone, profile FROM doctor WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

$doctorId = $doctor['id'];
$doctorPic = $doctor['profile'] ?: 'default.jpg';
$fullName = $doctor['first_name'] . ' ' . $doctor['surname'];

// Fetch statistics
$today = date('Y-m-d');
$appointmentQuery = $conn->query("SELECT COUNT(*) as total FROM appointment WHERE id = $doctorId AND date_booked = '$today'");
$totalAppointments = $appointmentQuery->fetch_assoc()['total'];

$patientQuery = $conn->query("SELECT COUNT(DISTINCT id) as pateint FROM appointment WHERE id = $doctorId");
$totalPatients = $patientQuery->fetch_assoc()['pateint'];

$reportQuery = $conn->query("SELECT COUNT(*) as report FROM report WHERE id = $doctorId");
$totalReports = $reportQuery->fetch_assoc()['report'];

$prescriptionQuery = $conn->query("SELECT COUNT(*) as prescriptions FROM report WHERE id = $doctorId");
$totalPrescriptions = $prescriptionQuery->fetch_assoc()['prescriptions'];




// Fetch pending appointments from the appointment table
$query = "SELECT * FROM appointment WHERE status = 'Pending'";
$appointments = $conn->query($query);

// Handle "Reject" action - Delete appointment
if (isset($_GET['reject'])) {
  $appointment_id = $_GET['reject'];

  // Prepare and execute the delete query
  $stmt = $conn->prepare("DELETE FROM appointment WHERE id = ?");
  $stmt->bind_param("i", $appointment_id);

  if ($stmt->execute()) {
      // Redirect to the main doctor appointments page without the reject query parameter
      header("Location: doctor.php"); // Redirect back to the appointments page
      exit(); // Make sure the script stops here after redirect
  } else {
      echo "Error: " . $stmt->error;
  }
}

/// Handle "Check" action - View appointment details and finalize it
if (isset($_GET['check'])) {
  $appointment_id = $_GET['check'];

  // Fetch appointment details based on the ID
  $fetch_query = "SELECT * FROM appointment WHERE id = $appointment_id";
  $appointment_result = $conn->query($fetch_query);

  if ($appointment_result->num_rows > 0) {
      $appointment = $appointment_result->fetch_assoc(); // Get the appointment details

      // Return appointment details as a JSON response for AJAX
      echo json_encode([
          'success' => true,
          'id' => $appointment['id'],
          'doctor_name' => $appointment['first_name'] . " " . $appointment['surname'],
          'patient_name' => $appointment['first_name'] . " " . $appointment['surname'],
          'appointment_date' => $appointment['appointment_date']
      ]);
  } else {
      echo json_encode(['success' => false]);
  }
  exit; // Stop further script execution after AJAX response
}





// Handle Finalize Appointment - Save data to income, update status to 'Checked', and remove the appointment
if (isset($_POST['finalize'])) {
  // Sanitize input to avoid SQL injection
  $appointment_id = intval($_POST['appointment_id']);
  $doctor_name = $conn->real_escape_string($_POST['doctor_name']);
  $patient_name = $conn->real_escape_string($_POST['patient_name']);
  $amount_paid = floatval($_POST['amount_paid']);
  $description = $conn->real_escape_string($_POST['description']);
  $timestamp = date('Y-m-d H:i:s');

  // Insert into the income table
  $insert_income = "INSERT INTO income (doctor, patient, date_discharge, amount_paid, description, date_check)
                    VALUES ('$doctor_name', '$patient_name', '$timestamp', '$amount_paid', '$description', '$timestamp')";

  if ($conn->query($insert_income)) {
      // Update the appointment status to 'Checked'
      $update_status = "UPDATE appointment SET status = 'Checked' WHERE id = $appointment_id";

      if ($conn->query($update_status)) {
          // Optionally: Remove the appointment from the appointment table (optional if you only want to mark it 'Checked')
          // $delete_appointment = "DELETE FROM appointment WHERE id = $appointment_id";
          // if ($conn->query($delete_appointment)) {
          //     echo json_encode(['success' => true, 'message' => 'Appointment finalized and removed.']);
          // } else {
          //     echo json_encode(['success' => false, 'message' => 'Error deleting appointment: ' . $conn->error]);
          // }

          echo json_encode(['success' => true, 'message' => 'Appointment finalized successfully.']);
      } else {
          echo json_encode(['success' => false, 'message' => 'Error updating appointment status: ' . $conn->error]);
      }
  } else {
      echo json_encode(['success' => false, 'message' => 'Error inserting income: ' . $conn->error]);
  }
  exit;
}


// Fetch appointments with doctor's name
$appointmentsQuery = "SELECT a.id, a.first_name AS patient_first_name, a.surname AS patient_surname, a.appointment_date, d.first_name AS doctor_first_name, d.surname AS doctor_surname 
                      FROM appointment a
                      JOIN doctor d ON a.doctor_id = d.id";
$appointmentsResult = $conn->query($appointmentsQuery);

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Dashboard</title>
  <script src="https://kit.fontawesome.com/c1df782baf.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Your CSS should come after Bootstrap & Tailwind -->
  <link rel="stylesheet" href="doctor.css">
</head>
<body>

<!-- Header -->
<header>
  <div class="logo">
    <img src="images/logo3.png" alt="Logo">
  </div>
  <div class="doctor-info">
    <img src="images/<?php echo htmlspecialchars($doctorPic); ?>" alt="Profile" />
    <div class="welcome-text">Welcome, Dr. <?php echo htmlspecialchars($doctorName); ?></div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</header>


<!-- Sidebar -->
<div class="sidebar">
<a href="#" onclick="event.preventDefault(); showSection('dashboard')"><i class="fa-solid fa-table-columns"></i> Dashboard</a>
<a href="#" onclick="event.preventDefault(); showSection('profile')"><i class="fa-solid fa-user"></i> Profile</a>
<a href="#" onclick="event.preventDefault(); showSection('patient')"><i class="fa-solid fa-gear"></i> Patients</a>
<a href="#" onclick="event.preventDefault(); showSection('appoint-manage')"><i class="fa-solid fa-envelope"></i> Appointment</a>
<a href="#" onclick="event.preventDefault(); showSection('doc-manage')"><i class="fa-solid fa-user-doctor"></i> Report</a>
</div>


<!-- main content -->
 
<div id="dashboard" class="section main-content dashboard-container">

<!-- Analytics Graph Card -->

<div class=" col-span-2  shadow p-4 rounded-2xl bg-white " >
  <h3 class="text-xl font-semibold mb-4 text-gray-700">
    <i class="fa-solid fa-calendar-check mr-2 text-blue-700"></i>Appointment: Scheduled 
  </h3>
  <canvas id="bpChart" width="800"  height="300"></canvas>
</div>


<div class="card">
    <img src="images/<?php echo $doctorPic; ?>" class="profile-img" alt="Doctor Profile">
    <h3><?php echo htmlspecialchars($fullName); ?></h3>
    <p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($doctor['phone']); ?></p>
    <a href="#" onclick="showSection('profile')" class="btn">Edit Profile</a>
  </div>

  <!-- Today's Appointments -->
  <div class="card">
    <i class="fa fa-calendar-check"></i>
    <h3>Today's Appointments</h3>
    <p><?php echo $totalAppointments; ?> Scheduled</p>
    <a href="#" class="btn">View Schedule</a>
  </div>

  <!-- Total Patients -->
  <div class="card">
    <i class="fa fa-users"></i>
    <h3>Total Patients</h3>
    <p><?php echo $totalPatients; ?> Unique</p>
  </div>

  <!-- Prescriptions -->
  <div class="card">
    <i class="fa fa-pills"></i>
    <h3>Prescriptions</h3>
    <p><?php echo $totalPrescriptions; ?> Issued</p>
    <a href="#" class="btn">View History</a>
  </div>

  <!-- Reports -->
  <div class="card">
    <i class="fa fa-file-medical-alt"></i>
    <h3>Reports</h3>
    <p><?php echo $totalReports; ?> Uploaded</p>
    <a href="#" class="btn">View Reports</a>
  </div>

  <!-- Messages -->
  <div class="card">
    <i class="fa fa-envelope"></i>
    <h3>Messages</h3>
    <p>Check for any updates or alerts.</p>
    <a href="#" class="btn">View Inbox</a>
  </div>
</div>

<!-- Profile Section -->
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
      <input type="text" name="username" value="<?php echo htmlspecialchars($doctor['username']); ?>" required>

    </div>

    <div class="form-group">
      <label>Profile Picture</label>
      <div class="profile-pic-preview">
        <img src="images/<?php echo htmlspecialchars($doctorPic); ?>" alt="Profile" style="width:100px;height:100px;">
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


<?php if (!empty($successMessage)) : ?>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      alert("<?= $successMessage ?>");
    });
  </script>
<?php endif; ?>

<?php if (!empty($errorMessage)) : ?>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      alert("<?= $errorMessage ?>");
    });
  </script>
<?php endif; ?>


<!-- Report Section (Visible only to Doctor) -->
<div id="doc-manage" class="section" style="margin: 100px 20px 20px 280px;">
  <h2 class="text-2xl font-bold mb-4">📄 Patient Reports</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover bg-white shadow rounded-2xl">
    <thead>
  <tr class="bg-blue-600 text-white text-center">
    <th class="p-3">ID</th>
    <th class="p-3">Title</th>
    <th class="p-3">Message</th>
    <th class="p-3">Username</th>
    <th class="p-3">Date Sent</th>
  </tr>
</thead>


      <tbody>
        <?php
        $reportQuery = $conn->query("SELECT * FROM report ORDER BY date_send DESC");
        if ($reportQuery->num_rows > 0) {
          while ($row = $reportQuery->fetch_assoc()) {
            echo "<tr class='text-center'>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['message']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_send']) . "</td>";
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='5' class='text-center text-muted'>No reports found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>


<!-- Pateint Section -->

<section id="patient" class="section doctor-manage-wrapper">
    <h2 class="doctor-manage-title">My Patients</h2>

    <div class="doctor-manage-table-container">
        <table class="doctor-manage-table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Profile</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Country</th>
                    <th>Date Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Step 2: Get all patients from the "pateint" table
                $query = "SELECT * FROM pateint";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td><img src='images/{$row['profile']}' class='doctor-avatar' alt='Patient' /></td>";
                        echo "<td>{$row['first_name']} {$row['surname']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['phone']}</td>";
                        echo "<td>{$row['gender']}</td>";
                        echo "<td>{$row['country']}</td>";
                        echo "<td>{$row['date_reg']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No patients found.</td></tr>";
                }

                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</section>


<!-- APPOINTMENT MANAGE -->

<div id="appoint-manage" class="section" style="background: #fff; padding: 20px; border-radius: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: 100px auto; max-width: 1000px;">
    <h2 style="font-size: 24px; font-weight: bold;">Manage Appointments</h2>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; border-radius: 10px;">
        <thead>
            <tr>
                <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">ID</th>
                <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">Doctor</th>
                <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">Patient</th>
                <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">Appointment Date</th>
                <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $appointmentsResult->fetch_assoc()) { ?>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px;"><?= $row['id'] ?></td>
                <td style="padding: 12px;"><?= $row['doctor_first_name'] ?> <?= $row['doctor_surname'] ?></td> <!-- Doctor Name -->
                <td style="padding: 12px;"><?= $row['patient_first_name'] ?> <?= $row['patient_surname'] ?></td> <!-- Patient Name -->
                <td style="padding: 12px;"><?= $row['appointment_date'] ?></td>
                <td style="padding: 12px;">
                    <a href="javascript:void(0);" onclick="showAppointmentDetails(<?= $row['id'] ?>)" style="padding: 8px 16px; text-decoration: none; background-color: #4CAF50; color: white; border-radius: 5px;">Check</a> |
                    <a href="doctor.php?reject=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to reject this appointment?')" style="padding: 8px 16px; text-decoration: none; background-color: red; color: white; border-radius: 5px;">Reject</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Appointment Details Section (Initially Hidden) -->
    <div id="appointment-details" class="container" style="display: none; flex: 1; gap: 20px; margin-top: 40px;">
        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <h3 style="font-size: 20px; font-weight: bold;">Appointment Details</h3>
            <p id="doctor-name"></p>
            <p id="patient-name"></p>
            <p id="appointment-date"></p>
        </div>

        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <h3 style="font-size: 20px; font-weight: bold;">Finalize Appointment</h3>
            
            <form id="finalize-form" method="POST">
                <input type="hidden" name="appointment_id" id="appointment-id">
                <input type="hidden" name="doctor_name" id="doctor-name-input">
                <input type="hidden" name="patient_name" id="patient-name-input">

                <label for="amount_paid" style="font-weight: bold; margin-bottom: 5px;">Amount Paid:</label>
                <input type="number" name="amount_paid" required style="width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ccc;">

                <label for="description" style="font-weight: bold; margin-bottom: 5px;">Description:</label>
                <textarea name="description" required style="width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ccc;"></textarea>

                <button type="button" id="finalize-btn" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">Finalize Appointment</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Function to show the appointment details when "Check" is clicked
    function showAppointmentDetails(appointmentId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "doctor.php?check=" + appointmentId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    document.getElementById("doctor-name").innerHTML = "<strong>Doctor:</strong> " + data.doctor_name;
                    document.getElementById("patient-name").innerHTML = "<strong>Patient:</strong> " + data.patient_name;
                    document.getElementById("appointment-date").innerHTML = "<strong>Appointment Date:</strong> " + data.appointment_date;

                    document.getElementById("appointment-id").value = data.id;
                    document.getElementById("doctor-name-input").value = data.doctor_name;
                    document.getElementById("patient-name-input").value = data.patient_name;

                    // Show the appointment details and finalize form
                    document.getElementById("appointment-details").style.display = "flex";
                } else {
                    alert("Appointment not found.");
                }
            }
        };
        xhr.send();
    }

    // Finalize the appointment via AJAX when the button is clicked
document.getElementById("finalize-btn").addEventListener("click", function() {
    var form = document.getElementById("finalize-form");
    var formData = new FormData(form);

    formData.append('finalize', true);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "doctor.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert(response.message);
                document.getElementById("appointment-details").style.display = "none";
                location.reload();
            } else {
                alert(response.message);
            }
        }
    };
    xhr.send(formData);
});
</script>















<script>
function showSection(sectionId) {
    const allSections = document.querySelectorAll('.section');
    allSections.forEach(section => {
      section.style.display = 'none';
    });

    const target = document.getElementById(sectionId);
    if (target) {
      // You can customize how each section should display
      target.style.display = sectionId === 'dashboard' ? 'grid' : 'block';
      console.log("Switched to:", sectionId);
    } else {
      console.error("Section not found:", sectionId);
    }
  }

  // Show dashboard by default
  document.addEventListener("DOMContentLoaded", () => {
    showSection('dashboard');
  });

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
                label: 'Appointment Pending',
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
                label: 'Appointment Complete',
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
</body>
</html>