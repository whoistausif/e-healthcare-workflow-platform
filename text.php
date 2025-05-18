<?php
include 'connection.php'; // Include the database connection file
// Query to fetch patient, appointment, and doctor details
$query = "SELECT 
            pateint.first_name AS patient_name,
            pateint.surname AS patient_surname,
            appointment.appointment_date,
            doctor.first_name AS doctor_first_name,
            doctor.surname AS doctor_surname
          FROM appointment
          JOIN pateint ON appointment.id = pateint.id
          LEFT JOIN doctor ON appointment.doctor_id = doctor.id"; // LEFT JOIN to ensure we fetch doctor details if available

// Execute the query
$result = mysqli_query($conn, $query);

// Check if there are results
if (mysqli_num_rows($result) > 0) {
    // Fetch the first row
    $row = mysqli_fetch_assoc($result);

    // Store the values in variables
    $patient_name = $row['patient_name'] . ' ' . $row['patient_surname'];
    $appointment_date = $row['appointment_date'];
    $doctor_name = $row['doctor_first_name'] . ' ' . $row['doctor_surname']; // If there's a doctor assigned
} else {
    echo "No appointments found.";
    exit; // Exit the script if no data is found
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Receipt - E-Healthcare Workflow</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .invoice-container {
            width: 70%;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hospital-logo {
            width: 200px;
        }
        .hospital-details {
            text-align: right;
        }
        .hospital-details h2 {
            margin: 0;
            font-size: 24px;
            color: #009dff;
        }
        .hospital-details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .invoice-title {
            text-align: center;
            margin: 40px 0;
            font-size: 28px;
            color: #333;
            font-weight: bold;
        }
        .invoice-info {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .invoice-info th, .invoice-info td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .invoice-info th {
            background-color: #009dff;
            color: white;
        }
        .invoice-info td {
            background-color: #f9f9f9;
        }
        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 0;
            border-top: 2px solid #333;
        }
        .payment-method {
            font-size: 14px;
            margin-top: 30px;
            text-align: right;
        }
        .thank-you {
            font-size: 16px;
            margin-top: 50px;
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <!-- Header Section -->
    <div class="invoice-header">
        <img src="images/logo3.png" alt="Hospital Logo" class="hospital-logo">
        <div class="hospital-details">
            <h2>E-Healthcare Hospital</h2>
            <p>123 Medical St, Health City</p>
            <p>Phone: (123) 456-7890</p>
            <p>Email: info@healthcarehospital.com</p>
        </div>
    </div>

    <!-- Invoice Title -->
    <div class="invoice-title">
        <h3>Invoice Receipt</h3>
        <p>Thank you for choosing E-Healthcare Workflow Platform</p>
    </div>

    <!-- Patient and Appointment Information -->
    <table class="invoice-info" style="width: 100%; margin-bottom: 30px; border-collapse: collapse;">
        <tr>
            <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">Patient Name</th>
            <td style="padding: 12px; text-align: left; background-color: #f9f9f9;"><?php echo $patient_name; ?></td>
        </tr>
        <tr>
            <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">Appointment Date</th>
            <td style="padding: 12px; text-align: left; background-color: #f9f9f9;"><?php echo $appointment_date; ?></td>
        </tr>
        <tr>
            <th style="padding: 12px; text-align: left; background-color: #009dff; color: white;">Doctor</th>
            <td style="padding: 12px; text-align: left; background-color: #f9f9f9;"><?php echo $doctor_name ? $doctor_name : 'No doctor assigned'; ?></td>
        </tr>
    </table>

    <!-- Total Price -->
    <div class="total">
        <p>Total: $175.00</p>
    </div>

    <!-- Payment Method -->
    <div class="payment-method">
        <p>Payment Method: Cash</p>
    </div>

    <!-- Thank You Note -->
    <div class="thank-you">
        <p>Thank you for visiting Healthcare Hospital! We appreciate your trust in our services.</p>
    </div>
</div>

</body>
</html>
