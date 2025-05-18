<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$appointment_id = $_GET['appointment_id'] ?? null;

if (!$appointment_id) {
    echo "<p style='color:red; text-align:center;'>Invalid request. Appointment ID missing.</p>";
    exit();
}

$stmt = $conn->prepare("
    SELECT a.first_name AS patient_first_name, a.surname AS patient_surname, a.phone, a.appointment_date,
           d.first_name AS doctor_first_name, d.surname AS doctor_surname
    FROM appointment a
    LEFT JOIN doctor d ON a.doctor_id = d.id
    WHERE a.id = ? AND a.status = 'Checked'
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red; text-align:center;'>Appointment not found or access denied.</p>";
    exit();
}

$row = $result->fetch_assoc();
$patient_first_name = $row['patient_first_name'];
$patient_surname = $row['patient_surname'];
$phone_number = $row['phone'] ?? 'N/A'; // Fetch phone number
$appointment_date = $row['appointment_date'];
$doctor_first_name = $row['doctor_first_name'];
$doctor_surname = $row['doctor_surname'];

// Get amount paid
$stmt = $conn->prepare("
    SELECT i.amount_paid, i.description
    FROM income i
    WHERE i.patient = CONCAT(?, ' ', ?)
    ORDER BY i.id DESC LIMIT 1
");
$stmt->bind_param("ss", $patient_first_name, $patient_surname);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red; text-align:center;'>Amount not found for this patient.</p>";
    exit();
}

$row = $result->fetch_assoc();
$amount_paid = $row['amount_paid'] ?? '0.00';
$description = $row['description'] ?? 'Consultation';

// Count all appointments
$stmt = $conn->prepare("SELECT COUNT(*) AS total_appointments FROM appointment WHERE first_name = ? AND surname = ?");
$stmt->bind_param("ss", $patient_first_name, $patient_surname);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_appointments = $row['total_appointments'] ?? 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <script>
        function printInvoice() {
            window.print();
        }
    </script>
</head>
<body style="font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 50px;">

<div style="max-width: 800px; margin: auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <img src="images/logo3.png" alt="Logo" style="width: 300px;">
        <div style="text-align: right;">
            <h2 style="color: #0d47a1; margin: 0;">E-Healthcare Hospital</h2>
            <p>123 Medical St, Health City</p>
            <p>Phone: (123) 456-7890</p>
            <p>Email: info@healthcarehospital.com</p>
        </div>
    </div>

    <hr style="margin: 30px 0; border: none; border-top: 2px solid #ddd;">

    <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
        <div>
            <strong>BILL TO</strong><br>
            <?php echo $patient_first_name; ?><br>
            <strong>Phone:</strong> <?php echo htmlspecialchars($phone_number); ?> <!-- Replaced Total Appointments with Phone -->
        </div>
        <div>
            <strong>APPOINTMENT DATE</strong><br>
            <?php echo date("d M Y", strtotime($appointment_date)); ?><br><br>
            <strong>DOCTOR</strong><br>
            Dr. <?php echo $doctor_first_name . ' ' . $doctor_surname; ?>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <thead style="background-color: #e3f2fd;">
            <tr>
                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #90caf9;">QTY</th>
                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #90caf9;">DESCRIPTION</th>
                <th style="text-align: right; padding: 12px; border-bottom: 2px solid #90caf9;">UNIT PRICE</th>
                <th style="text-align: right; padding: 12px; border-bottom: 2px solid #90caf9;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 10px;">1</td>
                <td style="padding: 10px;"><?php echo $description; ?></td>
                <td style="padding: 10px; text-align: right;">Rs <?php echo number_format($amount_paid, 2); ?></td>
                <td style="padding: 10px; text-align: right;">Rs <?php echo number_format($amount_paid, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: right; margin-top: 20px;">
        <p style="font-size: 16px;"><strong>Subtotal:</strong> Rs <?php echo number_format($amount_paid, 2); ?></p>
        <p style="font-size: 18px; color: #0d47a1;"><strong>Total Paid:</strong> Rs <?php echo number_format($amount_paid, 2); ?></p>
    </div>

    <div style="margin-top: 50px; text-align: center;">
        <p style="color: #555;">Thank you for choosing E-Healthcare! We appreciate your trust in our care.</p>
        <p style="font-size: 14px; color: #999;">Payment was received in cash.</p>
    </div>

    <!-- Print Button -->
    <div style="text-align: center; margin-top: 30px;">
        <button onclick="printInvoice()" style="padding: 10px 20px; font-size: 16px; background-color: #0d47a1; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            Print Invoice
        </button>
    </div>
</div>

</body>
</html>
