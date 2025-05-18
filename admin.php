<?php
session_start();
include 'connection.php';

// Redirect to login if not logged in or not an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch admin info
$stmt = $conn->prepare("SELECT username, profile FROM admin WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$adminName = $admin['username'];
$adminPic = $admin['profile'] ?: 'default.jpg';

$successMessage = "";
$errorMessage = "";

// Handle profile update
if (isset($_POST['update'])) {
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $profile = $_FILES['profile'];
    $errors = [];

    if (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $errorMessage = "❌ Passwords do not match.";
    } else {
        $profileName = $adminPic;
        if ($profile && $profile['error'] === 0) {
            $ext = pathinfo($profile['name'], PATHINFO_EXTENSION);
            $profileName = uniqid() . "." . $ext;
            move_uploaded_file($profile['tmp_name'], "images/" . $profileName);
        }

        $query = "UPDATE admin SET username = ?, ";
        $params = [$newUsername];
        $types = "s";

        if (!empty($newPassword)) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $query .= "password = ?, ";
            $params[] = $hashed;
            $types .= "s";
        }

        if (!empty($profileName)) {
            $query .= "profile = ?, ";
            $params[] = $profileName;
            $types .= "s";
        }

        $query = rtrim($query, ", ") . " WHERE username = ?";
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

// Fetch dashboard stats
$stats = [
    "totalAdmins" => $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'],
    "totalDoctors" => $conn->query("SELECT COUNT(*) as count FROM doctor WHERE status = 'Approved'")->fetch_assoc()['count'],
    "totalPatients" => $conn->query("SELECT COUNT(*) as count FROM pateint")->fetch_assoc()['count'],
    "totalReports" => $conn->query("SELECT COUNT(*) as count FROM report")->fetch_assoc()['count'],
    "doctorApplications" => $conn->query("SELECT COUNT(*) as count FROM doctor WHERE status = 'pending'")->fetch_assoc()['count'],
    "totalIncome" => $conn->query("SELECT SUM(amount_paid) as total FROM income")->fetch_assoc()['total'] ?? 0,
];

$result = $conn->query("SELECT SUM(amount_paid) as total FROM income");
$row = $result ? $result->fetch_assoc() : ['total' => 0];
$totalIncome = $row['total'] ?? 0;

// Add Admin Logic
if (isset($_POST['addAdmin'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $profile = 'default.png'; // Default profile if no image is uploaded
    
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === 0) {
        $ext = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
        $profile = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['profile']['tmp_name'], "images/" . $profile);
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO admin (username, password, profile) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $profile);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Remove Admin Logic
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete admin from database
    $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch all admins
$result = $conn->query("SELECT * FROM admin");

// Job request handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctor_id'])) {
    $doctorId = $_POST['doctor_id'];

    if (isset($_POST['approve'])) {
        $stmt = $conn->prepare("UPDATE doctor SET status = 'Approved' WHERE id = ?");
    } elseif (isset($_POST['reject'])) {
        $stmt = $conn->prepare("DELETE FROM doctor WHERE id = ?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $doctorId);
        $stmt->execute();
        header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
        exit();
    }
}

// Handle Doctor Update
if (isset($_POST['update_doctor'])) {
    $id = $_POST['edit_id'];
    $first_name = $_POST['edit_first_name'];
    $surname = $_POST['edit_surname'];
    $email = $_POST['edit_email'];
    $phone = $_POST['edit_phone'];
    $gender = $_POST['edit_gender'];
    $country = $_POST['edit_country'];
    $salary = $_POST['edit_salary'];

    $stmt = $conn->prepare("UPDATE doctor SET first_name=?, surname=?, email=?, phone=?, gender=?, country=?, salary=? WHERE id=?");
    $stmt->bind_param("ssssssdi", $first_name, $surname, $email, $phone, $gender, $country, $salary, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Doctor updated successfully');</script>";
    } else {
        echo "<script>alert('Failed to update doctor');</script>";
    }
}

// Handle Patient Update
if (isset($_POST['update_patient'])) {
    $id = $_POST['edit_patient_id'];
    $first_name = $_POST['edit_patient_first_name'];
    $surname = $_POST['edit_patient_surname'];
    $email = $_POST['edit_patient_email'];
    $phone = $_POST['edit_patient_phone'];
    $gender = $_POST['edit_patient_gender'];
    $country = $_POST['edit_patient_country'];

    $updateQuery = "UPDATE pateint SET 
        first_name='$first_name',
        surname='$surname',
        email='$email',
        phone='$phone',
        gender='$gender',
        country='$country'
        WHERE id='$id'";
    mysqli_query($conn, $updateQuery);
}

// Handle Remove Patient
if (isset($_POST['remove_patient'])) {
    $id = $_POST['patient_id'];
    mysqli_query($conn, "DELETE FROM pateint WHERE id='$id'");
}

// Handle Doctor Form Status Toggle
$statusQuery = $conn->query("SELECT value FROM settings WHERE name = 'doctor_form_status'");
$current_status = ($statusQuery && $statusQuery->num_rows > 0) ? $statusQuery->fetch_assoc()['value'] : 'open';

// Handle Form Status Update
if (isset($_POST['toggle_form_status'])) {
    $new_status = $_POST['apply_doctor_status'];
    $conn->query("UPDATE settings SET value = '$new_status' WHERE name = 'doctor_form_status'");
    $current_status = $new_status;
}

// Handle News/Notice Add
if (isset($_POST['add_news'])) {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $date = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO news_notice (type, title, content) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $type, $title, $content);
    $stmt->execute();

    $_SESSION['news_alert'] = "News/Notice added successfully!";
    header("Location: admin.php");
    exit();
}

// Delete News/Notice
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM news_notice WHERE id = $id");
    $_SESSION['news_alert'] = "Entry deleted.";
    header("Location: admin.php");
    exit();
}

// Edit News/Notice
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM news_notice WHERE id = $id");
    $edit_data = $res->fetch_assoc();
}

// Save Edited News/Notice
if (isset($_POST['update_news'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $conn->query("UPDATE news_notice SET type='$type', title='$title', content='$content' WHERE id=$id");
    $_SESSION['news_alert'] = "News/Notice updated!";
    header("Location: admin.php");
    exit();
}

// Handle Appointment Update
if (isset($_POST['update_appointment'])) {
    $id = $_POST['edit_appointment_id'];
    $appointment_date = $_POST['edit_appointment_date'];
    $status = $_POST['edit_appointment_status'];

    // Update appointment
    $updateQuery = "UPDATE appointment SET appointment_date='$appointment_date', status='$status' WHERE id='$id'";
    mysqli_query($conn, $updateQuery);
}

// Handle Remove Appointment
if (isset($_POST['remove_appointment'])) {
    $id = $_POST['appointment_id'];
    mysqli_query($conn, "DELETE FROM appointment WHERE id='$id'");
}

// Fetch invoice data
$invoiceQuery = "
    SELECT 
        appointment.id AS appointment_id,
        appointment.first_name AS patient_first_name,
        appointment.surname AS patient_surname,
        appointment.appointment_date,
        doctor.first_name AS doctor_first_name,
        doctor.surname AS doctor_surname,
        income.amount_paid
    FROM appointment
    LEFT JOIN doctor ON appointment.doctor_id = doctor.id
    LEFT JOIN income ON income.patient = CONCAT(appointment.first_name, ' ', appointment.surname)
    WHERE appointment.status = 'Checked'
    ORDER BY appointment.appointment_date DESC
";

$invoiceResult = $conn->query($invoiceQuery);
$appointments = [];

if ($invoiceResult && $invoiceResult->num_rows > 0) {
    while ($row = $invoiceResult->fetch_assoc()) {
        $appointments[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://kit.fontawesome.com/c1df782baf.js" crossorigin="anonymous"></script>

      <!-- FontAwesome 6.0.0-beta3 -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-nLjIyyX1OPRiVg6vOOrAjMxN7D3+IBx5G8b5Oxu7RStTNoiAqgJc3JodO1K2jQ2tMlRnQqE0XRFrf6I1eWz02w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- FontAwesome 5.13.0 (Fallback) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" />

    <!-- Other icons libraries -->
    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/2.1.0/uicons-thin-rounded/css/uicons-thin-rounded.css'>
    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/2.1.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="path/to/bootstrap.min.css">
<link rel="stylesheet" href="path/to/your-custom-style.css">

  <!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<!-- Header -->
<header>
  <div class="logo">
    <img src="images/logo3.png" alt="Logo">
  </div>
  <div class="admin-info">
  <img src="images/<?php echo htmlspecialchars($adminPic); ?>" alt="Profile" />
    <div class="welcome-text">Welcome, <?php echo htmlspecialchars($adminName); ?></div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</header>

<!-- Sidebar -->
<div class="sidebar">
<a href="#" onclick="event.preventDefault(); showSection('dashboard')"><i class="fa-solid fa-table-columns"></i> Dashboard</a>
<a href="#" onclick="event.preventDefault(); showSection('profile')"><i class="fa-solid fa-user"></i> Profile</a>
<a href="#" onclick="event.preventDefault(); showSection('administration')"><i class="fa-solid fa-gear"></i> Administration</a>
<a href="#" onclick="event.preventDefault(); showSection('job-request')"><i class="fa-solid fa-envelope"></i> Job's Applications</a>
<a href="#" onclick="event.preventDefault(); showSection('doc-manage')"><i class="fa-solid fa-user-doctor"></i> Doctor Manage</a>
<a href="#" onclick="event.preventDefault(); showSection('patient-manage')"><i class="fa-solid fa-hospital-user"></i> Patient Manage</a>
<a href="#" onclick="event.preventDefault(); showSection('appointment-manage')"><i class="fa-solid fa-user-doctor"></i> Appointment Manage</a>
<a href="#" onclick="event.preventDefault(); showSection('manage-doctor-form')"><i class="fa-solid fa-user-doctor"></i>Manage doctor Form</a>
<a href="#" onclick="event.preventDefault(); showSection('admin')"><i class="fa-solid fa-user-doctor"></i>Manage News notice</a>
<a href="#" onclick="event.preventDefault(); showSection('invoice')"><i class="fa-solid fa-user-doctor"></i>View all invoice</a>
</div>

<!-- Main Content -->

<div id="dashboard" class="section main-content">
    <div class="card">
        <i class="fa-solid fa-user-shield"></i>
        <h3>Total Admins</h3>
        <p><?php echo $stats['totalAdmins']; ?></p>
    </div>
    <div class="card">
        <i class="fa-solid fa-user-doctor"></i>
        <h3>Total Doctors</h3>
        <p><?php echo $stats['totalDoctors']; ?></p>
    </div>
    <div class="card">
        <i class="fa-solid fa-hospital-user"></i>
        <h3>Total Patients</h3>
        <p><?php echo $stats['totalPatients']; ?></p>
    </div>
    <div class="card">
        <i class="fa-solid fa-file-medical"></i>
        <h3>Total Reports</h3>
        <p><?php echo $stats['totalReports']; ?></p>
    </div>
    <div class="card">
        <i class="fa-solid fa-user-plus"></i>
        <h3>Doctor Applications</h3>
        <p><?php echo $stats['doctorApplications']; ?></p>
    </div>
    <div class="card">
        <i class="fa-solid fa-coins"></i>
        <h3>Total Income</h3>
        <p>₹<?php echo number_format($stats['totalIncome'], 2); ?></p>
    </div>
</div>



  

     
<!-- Profile Section -->
<div id="profile" class="section profile-section">

  <?php if (!empty($successMessage)) : ?>
    <script>
        alert("<?= $successMessage ?>");
    </script>
<?php endif; ?>

<?php if (!empty($errorMessage)) : ?>
    <script>
        alert("<?= $errorMessage ?>");
    </script>
<?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="profile-form">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($adminName); ?>" required>
    </div>

    <div class="form-group">
      <label>Profile Picture</label>
      <div class="profile-pic-preview">
        <img src="images/<?php echo htmlspecialchars($adminPic); ?>" alt="Profile">
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

<!-- administration section -->

<section id="administration" class="admin-section section layout-wrapper">

   <div class="container">
   
     <!-- Left side: Admin List -->
     <div class="admin-list">
       <div class="admin-card">
       <h2 class="job-request-title"   margin-left: 250px;>All Admins List</h2>
         <table>
           <thead>
             <tr>
               <th>ID</th>
               <th>Profile</th>
               <th>Username</th>
               <!-- <th>Password</th> -->
               <th>Action</th>
             </tr>
           </thead>
           <tbody>
           
             <?php 
             $result = $conn->query("SELECT * FROM admin"); 
             while ($admin = $result->fetch_assoc()) { ?>
               <tr>
                 <td><?php echo $admin['id']; ?></td>
                 <td><img src="images/<?php echo $admin['profile']; ?>" width="50" height="50"></td>
                 <td><?php echo $admin['username']; ?></td>
                 <!-- <td><?php echo $admin['password']; ?></td> -->
                 <td class="admin-actions">
                   <a href="?id=<?php echo $admin['id']; ?>" class="btn btn-danger">Remove</a>
                 </td>
               </tr>
             <?php } ?>
           </tbody>
         </table>
       </div>
     </div>

     <!-- Right side: Add New Admin Form -->
     <div class="admin-form">
       <div class="admin-card">
       <h2 class="job-request-title">Add Admins</h2>
         <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
           <div class="mb-3">
             <label for="username" class="form-label">Username</label>
             <input type="text" class="form-control" name="username" required>
           </div>
           <div class="mb-3">
             <label for="password" class="form-label">Password</label>
             <input type="password" class="form-control" name="password" required>
     </div>
           <div class="mb-3">
                 <label for="profile" class="form-label">Profile Picture</label>
             <input type="file" class="form-control" name="profile">
           </div>
           <button type="submit" class="btn btn-primary" name="addAdmin">Add Admin</button>
         </form>
       </div>
     </div>

     </div>

   <!-- Bootstrap JS, Popper.js -->
   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

</section>


<!-- Job request Section -->

<section id="job-request" class="section job-request-wrapper">
  <h2 class="job-request-title">Pending Doctor Applications</h2>

  <?php $pendingDoctors = $conn->query("SELECT * FROM doctor WHERE status = 'pending'"); ?>

  <div class="job-request-table-container">
    <table class="job-request-table">
      <thead>
        <tr>
          <th>Id</th>
          <th>Full Name</th>
          <th>Username</th>
          <th>Email</th>
          <th>Gender</th>
          <th>Phone</th>
          <th>Country</th>
          <th>Applied On</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($doc = $pendingDoctors->fetch_assoc()) { ?>
          <tr>
            <td><?= htmlspecialchars($doc['id']) ?></td>
            <td><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['surname']) ?></td>
            <td><?= htmlspecialchars($doc['username']) ?></td>
            <td><?= htmlspecialchars($doc['email']) ?></td>
            <td><?= htmlspecialchars($doc['gender']) ?></td>
            <td><?= htmlspecialchars($doc['phone']) ?></td>
            <td><?= htmlspecialchars($doc['country']) ?></td>
            <td><?= htmlspecialchars($doc['date_reg']) ?></td>
            <td>
              <form method="POST" class="action-form">
                <input type="hidden" name="doctor_id" value="<?= $doc['id']; ?>">
                <button name="approve" class="btn-approve">Approve</button>
                <button name="reject" class="btn-reject">Reject</button>
              </form>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</section>


<!-- doctor manage section -->

<section id="doc-manage" class="section doctor-manage-wrapper">
    <h2 class="doctor-manage-title">Manage Doctors</h2>

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
                    <th>Salary</th>
                    <th>Date Registered</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM doctor WHERE status='Approved'";
                $result = mysqli_query($conn, $query);
                $sn = 1;

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td><img src='images/{$row['profile']}' class='doctor-avatar' alt='Doctor' /></td>";
                    echo "<td>Dr. {$row['first_name']} {$row['surname']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['phone']}</td>";
                    echo "<td>{$row['gender']}</td>";
                    echo "<td>{$row['country']}</td>";
                    echo "<td>{$row['salary']}</td>";
                    echo "<td>{$row['date_reg']}</td>";
                    echo "<td><span class='status approved'>Approved</span></td>";
                    echo "<td>
                                <form method='POST' style='display:inline-block;'>
                                  <input type='hidden' name='doctor_id' value='{$row['id']}'>
                                 <button class='reject btn-remove' name='reject' onclick='return confirm(\"Are you sure?\")'>Remove</button>
        </form>
        <button onclick='openEditForm(" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ")' class='btn-edit'>Edit</button>
      </td>";
                    echo "</tr>";
                    $sn++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Form -->
    <div id="editFormContainer" style="display:none; margin-top:30px;">
    <h2 class="job-request-title">Edit Doctor Info</h2>
        <form method="POST" class="profile-form">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="edit_first_name" id="edit_first_name" required>
            </div>
            <div class="form-group">
                <label>Surname</label>
                <input type="text" name="edit_surname" id="edit_surname" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="edit_email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="edit_phone" id="edit_phone" required>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <input type="text" name="edit_gender" id="edit_gender" required>
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="edit_country" id="edit_country" required>
            </div>
            <div class="form-group">
                <label>Salary</label>
                <input type="text" name="edit_salary" id="edit_salary" required>
            </div>
            <button type="submit" name="update_doctor">Update Doctor</button>
        </form>
    </div>
</section>


<!-- Patient Manage Section -->
<section id="patient-manage" class="section doctor-manage-wrapper">
    <h2 class="doctor-manage-title">Manage Patients</h2>

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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM pateint";
                $result = mysqli_query($conn, $query);
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
                    echo "<td>
                            <form method='POST' style='display:inline-block;'>
                                <input type='hidden' name='patient_id' value='{$row['id']}'>
                                <button class='reject btn-remove' name='remove_patient' onclick='return confirm(\"Are you sure?\")'>Remove</button>
                            </form>
                            <button onclick='openPatientEditForm(" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ")' class='btn-edit'>Edit</button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Form -->
    <div id="editPatientFormContainer" style="display:none; margin-top:30px;">
        <h2 class="job-request-title">Edit Patient Info</h2>
        <form method="POST" class="profile-form">
            <input type="hidden" name="edit_patient_id" id="edit_patient_id">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="edit_patient_first_name" id="edit_patient_first_name" required>
            </div>
            <div class="form-group">
                <label>Surname</label>
                <input type="text" name="edit_patient_surname" id="edit_patient_surname" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="edit_patient_email" id="edit_patient_email" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="edit_patient_phone" id="edit_patient_phone" required>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <input type="text" name="edit_patient_gender" id="edit_patient_gender" required>
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="edit_patient_country" id="edit_patient_country" required>
            </div>
            <button type="submit" name="update_patient">Update Patient</button>
        </form>
    </div>
</section>

<!-- Manage Doctor form Section -->
<section id="manage-doctor-form" class="section job-request-wrapper"  >
  <h2 class="job-request-title">Doctor Recruitment Form Status</h2>

  <div class="job-request-table-container">
    <form method="post" class="action-form" style="display: flex; gap: 20px; justify-content: center;">
      <select name="apply_doctor_status" class="form-select" required>
        <option value="open" <?= $current_status == 'open' ? 'selected' : '' ?>>Open</option>
        <option value="closed" <?= $current_status == 'closed' ? 'selected' : '' ?>>Closed</option>
      </select>
      <button type="submit" name="toggle_form_status" class="btn-approve">Update</button>
    </form>
  </div>
</section>


<!-- Admin News Form -->
<?php
if (isset($_SESSION['news_alert'])) {
    echo "<script>alert('{$_SESSION['news_alert']}');</script>";
    unset($_SESSION['news_alert']); // Clear it so it doesn't show again
}
?>

<section id="admin" class="section" style="margin: 100px 50px; padding: 20px; background: #f9f9f9; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);   font-family: 'IBM Plex Sans', sans-serif;">
  <h2 style="text-align: center; font-size: 25px; font-weight: bold; padding-bottom: 10px; color:rgb(0, 0, 0);"><?= $edit_mode ? "Edit News/Notice" : "Add News or Announcement" ?></h2>

  <form method="POST" style="max-width: 600px; margin: auto; display: flex; flex-direction: column; gap: 15px;">
    <select name="type" required style="padding: 10px;">
      <option value="">-- Select Type --</option>
      <option value="news" <?= $edit_mode && $edit_data['type'] == 'news' ? 'selected' : '' ?>>News & Events</option>
      <option value="announcement" <?= $edit_mode && $edit_data['type'] == 'announcement' ? 'selected' : '' ?>>Announcement</option>
    </select>
    <input type="text" name="title" placeholder="Title" required style="padding: 10px;" value="<?= $edit_mode ? $edit_data['title'] : '' ?>">
    <textarea name="content" rows="4" placeholder="Content or Description" style="padding: 10px;"><?= $edit_mode ? $edit_data['content'] : '' ?></textarea>
    
    <?php if ($edit_mode): ?>
      <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
      <button type="submit" name="update_news" style="padding: 12px; background: orange; color: #fff; border: none; border-radius: 6px;">Update</button>
      <a href="admin.php"  class="btn btn-danger" style="text-align:center";>Cancel Edit</a>
    <?php else: ?>
      <button type="submit" name="add_news" style="padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 6px;">Add</button>
    <?php endif; ?>
  </form>


  <section style="margin: 30px 50px;">
  <h2 style="text-align: center;  margin-bottom: 20px; font-size: 25px; font-weight: bold;">Manage News & Notices</h2>
  <table style="width: 80%; border-collapse: collapse; margin: 0px 0px 0px 250px;">
    <tr style="background: #007bff; color: #fff;">
      <th style="padding: 10px;">ID</th>
      <th>Type</th>
      <th>Title</th>
      <th>Content</th>
      <th>Date</th>
      <th>Action</th>
    </tr>
    <?php
    $res = $conn->query("SELECT * FROM news_notice ORDER BY date_created DESC");
    while ($row = $res->fetch_assoc()):
    ?>
    <tr style='border-bottom: 1px solid #ccc;'>
      <td style='padding: 10px;'><?= $row['id'] ?></td>
      <td><?= $row['type'] ?></td>
      <td><?= $row['title'] ?></td>
      <td><?= $row['content'] ?></td>
      <td><?= $row['date_created'] ?></td>
      <td>
  <a href="?edit=<?= $row['id'] ?>" class="btn btn-success" role="button">Edit</a>
  <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger" role="button" onclick="return confirm('Are you sure to delete?')">Delete</a>
</td>

    </tr>
    <?php endwhile; ?>
  </table>
</section>
</section>

<script>
document.getElementById("newsForm").addEventListener("submit", function() {
  alert("News added!");
});
</script>



<?php
// Handle News/Notice Addition
if (isset($_POST['add_news'])) {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO news_notice (type, title, content) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $type, $title, $content);
    $stmt->execute();

    echo "<script>alert('News/Notice added successfully!');</script>";
}
?>


















<!-- Appointment Manage Section -->
<section id="appointment-manage" class="section container" style="width: 80%; margin: 50px auto; background-color: #fff; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px;margin-top: 80px;margin-left: 260px;">
    <h2 class="section-title" style="font-size: 24px; font-weight: bold; color: #333;">Manage Appointments</h2>

    <!-- Appointment Table -->
    <div class="appointment-table-container" style="overflow-x: auto;">
        <table class="appointment-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #009dff; color: white;">
                    <th style="padding: 12px; text-align: left;">ID</th>
                    <th style="padding: 12px; text-align: left;">Patient Name</th>
                    <th style="padding: 12px; text-align: left;">Doctor Name</th>
                    <th style="padding: 12px; text-align: left;">Appointment Date</th>
                    <th style="padding: 12px; text-align: left;">Status</th>
                    <th style="padding: 12px; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all appointments
                $appointmentsQuery = "SELECT a.id, a.first_name, a.surname, a.appointment_date, a.status, d.first_name AS doctor_first_name, d.surname AS doctor_surname FROM appointment a JOIN doctor d ON a.doctor_id = d.id";
                $result = mysqli_query($conn, $appointmentsQuery);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr style='background-color: #f9f9f9;'>";
                    echo "<td style='padding: 12px;'>{$row['id']}</td>";
                    echo "<td style='padding: 12px;'>{$row['first_name']} {$row['surname']}</td>";
                    echo "<td style='padding: 12px;'>{$row['doctor_first_name']} {$row['doctor_surname']}</td>";
                    echo "<td style='padding: 12px;'>{$row['appointment_date']}</td>";
                    echo "<td style='padding: 12px;'>{$row['status']}</td>";
                    echo "<td style='padding: 12px;'>
                            <form method='POST' style='display:inline-block;'>
                                <input type='hidden' name='appointment_id' value='{$row['id']}'>
                                <button class='btn-remove' style='padding: 8px 16px; background-color: red; color: white; border: none; border-radius: 5px;' name='remove_appointment' onclick='return confirm(\"Are you sure?\")'>Remove</button>
                            </form>
                            <button onclick='openAppointmentEditForm(" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ")' class='btn-edit' style='padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;'>Edit</button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Appointment Form -->
    <div id="editAppointmentFormContainer" style="display:none; margin-top:30px; padding: 20px; background-color: #fff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px;">
        <h2 class="job-request-title" style="font-size: 20px; font-weight: bold; color: #333;">Edit Appointment Info</h2>
        <form method="POST" class="appointment-form" style="max-width: 600px; margin: 0 auto;">
            <input type="hidden" name="edit_appointment_id" id="edit_appointment_id">

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="edit_appointment_date" style="font-weight: bold; margin-bottom: 5px; display: inline-block;">Appointment Date:</label>
                <input type="date" name="edit_appointment_date" id="edit_appointment_date" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="edit_appointment_status" style="font-weight: bold; margin-bottom: 5px; display: inline-block;">Status:</label>
                <select name="edit_appointment_status" id="edit_appointment_status" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;" required>
                    <option value="Pending">Pending</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Canceled</option>
                </select>
            </div>

            <button type="submit" name="update_appointment" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">Update Appointment</button>
        </form>
    </div>
</section>






<!-- Invoice Section -->
<section id="invoice" class="section"   style="margin: 40px auto; width: 90%; padding: 20px; background-color: #fff; margin-top: 100px; margin-left: 260px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 10px;">

    <h2 style="text-align: left; color: rgb(0, 0, 0); margin-bottom: 30px; font-weight: bold; font-size: 24px;">
        All Checked Appointments with Invoices
    </h2>

    <?php if (!empty($appointments)) { ?>
        <ul style="list-style-type: none; padding: 0;">
            <?php foreach ($appointments as $row) { ?>
                <li style="margin-bottom: 20px; padding: 15px; border: 1px solid #e0e0e0; 
                    border-radius: 8px; background-color: #f9f9f9;">
                    
                    <p style="margin: 0; font-size: 18px; color: #333;">
                        <strong>Patient:</strong> <?php echo $row['patient_first_name'] . ' ' . $row['patient_surname']; ?>
                    </p>
                    
                    <p style="margin: 5px 0; font-size: 16px; color: #555;">
                        <strong>Doctor:</strong> Dr. <?php echo $row['doctor_first_name'] . ' ' . $row['doctor_surname']; ?>
                    </p>
                    
                    <p style="margin: 5px 0; font-size: 16px; color: #555;">
                        <strong>Date:</strong> <?php echo date("d M Y", strtotime($row['appointment_date'])); ?>
                    </p>
                    
                    <p style="margin: 5px 0; font-size: 16px; color: #555;">
                        <strong>Amount Paid:</strong> Rs <?php echo number_format($row['amount_paid'], 2); ?>
                    </p>

                    <a href="view_invoice.php?appointment_id=<?php echo $row['appointment_id']; ?>" 
                       style="display: inline-block; margin-top: 10px; color: white; background-color: #009dff; 
                              padding: 8px 15px; text-decoration: none; border-radius: 4px;">
                        View Invoice
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p style="text-align: center; font-size: 18px; color: #999;">No invoices found.</p>
    <?php } ?>

</section>



   

<!-- Script to toggle sections -->
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


  function openEditForm(doctor) {
    document.getElementById('editFormContainer').style.display = 'block';
    document.getElementById('edit_id').value = doctor.id;
    document.getElementById('edit_first_name').value = doctor.first_name;
    document.getElementById('edit_surname').value = doctor.surname;
    document.getElementById('edit_email').value = doctor.email;
    document.getElementById('edit_phone').value = doctor.phone;
    document.getElementById('edit_gender').value = doctor.gender;
    document.getElementById('edit_country').value = doctor.country;
    document.getElementById('edit_salary').value = doctor.salary;
}

function openPatientEditForm(data) {
    document.getElementById('editPatientFormContainer').style.display = 'block';
    document.getElementById('edit_patient_id').value = data.id;
    document.getElementById('edit_patient_first_name').value = data.first_name;
    document.getElementById('edit_patient_surname').value = data.surname;
    document.getElementById('edit_patient_email').value = data.email;
    document.getElementById('edit_patient_phone').value = data.phone;
    document.getElementById('edit_patient_gender').value = data.gender;
    document.getElementById('edit_patient_country').value = data.country;
}


// Function to close the edit form
    // Function to open the appointment edit form and fill the details
    function openAppointmentEditForm(appointmentData) {
        // Fill the form fields with the selected appointment's data
        document.getElementById('edit_appointment_id').value = appointmentData.id;
        document.getElementById('edit_appointment_date').value = appointmentData.appointment_date;
        document.getElementById('edit_appointment_status').value = appointmentData.status;

        // Show the form
        document.getElementById('editAppointmentFormContainer').style.display = 'block';
    }
</script>






</body>
</html>
