/*
# =============================================
# Project: Hospital Queue Management System
# Authors:  [ Jusminne Alipio    ], [2410050]
            [ Angel Charm Rabino ], [2411057]
            [ Carmel Sumpay      ], [2411111]
            [Alby Avery Tolentino], [2411125]
            [ Benjie Villarin    ], [2411173]
# Date: [December 16, 2025]
# Description:
    The HealthFlow Management System is based on the web-based GUI developed in HTML,
CSS, and JavaScript. Patients (kiosk and queue display) and staff (admin and front desk)
have separate interfaces, thus making the interface straightforward and easy to use 
depending on the user roles.
# =============================================
*/

<?php
require 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";

// ---------- HANDLE DELETES (GET) ----------
if (isset($_GET['delete_patient'])) {
    $id = (int)$_GET['delete_patient'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Patient and related records deleted.";
    }
}

if (isset($_GET['delete_appointment'])) {
    $id = (int)$_GET['delete_appointment'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Appointment deleted.";
    }
}

// ---------- LOAD EDIT DATA (GET) ----------
$editPatient = null;
$editAppointment = null;

if (isset($_GET['edit_patient'])) {
    $id = (int)$_GET['edit_patient'];
    if ($id > 0) {
        $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $editPatient = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

if (isset($_GET['edit_appointment'])) {
    $id = (int)$_GET['edit_appointment'];
    if ($id > 0) {
        $stmt = $conn->prepare("
            SELECT a.*, p.first_name, p.last_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $editAppointment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// ---------- HANDLE FORMS (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD PATIENT
    if ($action === 'add_patient') {
        $first   = trim($_POST['first_name'] ?? '');
        $last    = trim($_POST['last_name'] ?? '');
        $birth   = $_POST['birthdate'] ?? null;
        $gender  = $_POST['gender'] ?? null;
        $contact = trim($_POST['contact'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($first !== '' && $last !== '') {
            $stmt = $conn->prepare("
                INSERT INTO patients (first_name, last_name, birthdate, gender, contact, address)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssss", $first, $last, $birth, $gender, $contact, $address);
            $stmt->execute();
            $stmt->close();
            $message = "Patient added successfully.";
        } else {
            $message = "First and last name are required.";
        }
    }

    // UPDATE PATIENT
    if ($action === 'update_patient') {
        $id      = (int)($_POST['patient_id'] ?? 0);
        $first   = trim($_POST['first_name'] ?? '');
        $last    = trim($_POST['last_name'] ?? '');
        $birth   = $_POST['birthdate'] ?? null;
        $gender  = $_POST['gender'] ?? null;
        $contact = trim($_POST['contact'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($id > 0 && $first !== '' && $last !== '') {
            $stmt = $conn->prepare("
                UPDATE patients
                SET first_name = ?, last_name = ?, birthdate = ?, gender = ?, contact = ?, address = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssi", $first, $last, $birth, $gender, $contact, $address, $id);
            $stmt->execute();
            $stmt->close();
            $message = "Patient updated.";
        } else {
            $message = "First and last name are required.";
        }
    }

    // ADD APPOINTMENT
    if ($action === 'add_appointment') {
        $patientId = (int)($_POST['appt_patient_id'] ?? 0);
        $service   = $_POST['appt_service'] ?? '';
        $date      = $_POST['appt_date'] ?? '';
        $time      = $_POST['appt_time'] ?? '';
        $notes     = $_POST['appt_notes'] ?? '';

        if ($patientId && $service !== '' && $date !== '' && $time !== '') {
            $stmt = $conn->prepare("
                INSERT INTO appointments (patient_id, service, appointment_date, appointment_time, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issss", $patientId, $service, $date, $time, $notes);
            $stmt->execute();
            $stmt->close();
            $message = "Appointment added.";
        } else {
            $message = "Please complete appointment details.";
        }
    }

    // UPDATE APPOINTMENT
    if ($action === 'update_appointment') {
        $apptId    = (int)($_POST['appt_id'] ?? 0);
        $patientId = (int)($_POST['appt_patient_id'] ?? 0);
        $service   = $_POST['appt_service'] ?? '';
        $date      = $_POST['appt_date'] ?? '';
        $time      = $_POST['appt_time'] ?? '';
        $notes     = $_POST['appt_notes'] ?? '';

        if ($apptId && $patientId && $service !== '' && $date !== '' && $time !== '') {
            $stmt = $conn->prepare("
                UPDATE appointments
                SET patient_id = ?, service = ?, appointment_date = ?, appointment_time = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->bind_param("issssi", $patientId, $service, $date, $time, $notes, $apptId);
            $stmt->execute();
            $stmt->close();
            $message = "Appointment updated.";
        } else {
            $message = "Please complete appointment details.";
        }
    }

    // ADD BILL
    if ($action === 'add_bill') {
        $patientId     = (int)($_POST['bill_patient_id'] ?? 0);
        $appointmentId = trim($_POST['bill_appointment_id'] ?? '');
        $amount        = (float)($_POST['bill_amount'] ?? 0);

        if ($patientId && $amount > 0) {
            $success = false;
            
            // Validate appointment ID if provided
            if ($appointmentId !== '') {
                $appointmentId = (int)$appointmentId;
                // Check if appointment exists and belongs to the patient
                $checkStmt = $conn->prepare("
                    SELECT id FROM appointments 
                    WHERE id = ? AND patient_id = ?
                ");
                $checkStmt->bind_param("ii", $appointmentId, $patientId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                if ($checkResult->num_rows === 0) {
                    $message = "Invalid appointment ID or appointment doesn't belong to selected patient.";
                } else {
                    // Insert with appointment
                    $stmt = $conn->prepare("
                        INSERT INTO bills (patient_id, appointment_id, amount)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->bind_param("iid", $patientId, $appointmentId, $amount);
                    
                    if ($stmt->execute()) {
                        $message = "Bill added successfully.";
                        $success = true;
                    } else {
                        $message = "Error adding bill: " . $conn->error;
                    }
                    $stmt->close();
                }
                $checkStmt->close();
            } else {
                // Insert without appointment (walk-in)
                $stmt = $conn->prepare("
                    INSERT INTO bills (patient_id, amount)
                    VALUES (?, ?)
                ");
                $stmt->bind_param("id", $patientId, $amount);
                
                if ($stmt->execute()) {
                    $message = "Bill added successfully.";
                    $success = true;
                } else {
                    $message = "Error adding bill: " . $conn->error;
                }
                $stmt->close();
            }
        } else {
            $message = "Select a patient and enter a valid amount.";
        }
    }
}

// ---------- SEARCH / FILTER INPUTS ----------
$patientSearch = trim($_GET['patient_search'] ?? '');
$apptSearch    = trim($_GET['appt_search'] ?? '');
$apptDate      = trim($_GET['appt_date'] ?? '');

// ---------- PATIENT QUERY WITH SEARCH ----------
$patientWhere = "";
if ($patientSearch !== '') {
    $esc = $conn->real_escape_string($patientSearch);
    $patientWhere = "WHERE first_name LIKE '%$esc%' OR last_name LIKE '%$esc%' OR contact LIKE '%$esc%'";
}
$patients = $conn->query("SELECT * FROM patients $patientWhere ORDER BY id ASC LIMIT 100");

// ---------- APPOINTMENT QUERY WITH SEARCH / DATE FILTER ----------
$apptWhereParts = [];
if ($apptSearch !== '') {
    $esc = $conn->real_escape_string($apptSearch);
    $apptWhereParts[] = "(p.first_name LIKE '%$esc%' OR p.last_name LIKE '%$esc%' OR a.service LIKE '%$esc%')";
}
if ($apptDate !== '') {
    $escD = $conn->real_escape_string($apptDate);
    $apptWhereParts[] = "a.appointment_date = '$escD'";
}
$apptWhere = $apptWhereParts ? ('WHERE '.implode(' AND ', $apptWhereParts)) : '';

$appointments = $conn->query("
    SELECT a.*, p.first_name, p.last_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    $apptWhere
    ORDER BY a.id ASC
    LIMIT 100
");

// ---------- BILLS ----------
$bills = $conn->query("
    SELECT b.*, p.first_name, p.last_name, 
           a.service as appointment_service, a.appointment_date
    FROM bills b
    JOIN patients p ON b.patient_id = p.id
    LEFT JOIN appointments a ON b.appointment_id = a.id
    ORDER BY b.id DESC
    LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Front Desk - HealthFlow Management System</title>

<style>
:root {
    --maroon: #4b0000;
    --header-bg: #4c4854;
    --panel-border: #9e6d6d;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #222222;
}

/* Layout */
.layout {
    display: flex;
    height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 80px;
    background: var(--maroon);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 16px;
    gap: 24px;
}
.sidebar .icon {
    width: 36px;
    height: 36px;
    object-fit: contain;
    cursor: pointer;
    transition: transform 0.2s;
}
.sidebar .icon:hover {
    transform: scale(1.12);
}
.sidebar .bottom-icon {
    margin-top: auto;
    margin-bottom: 16px;
}

/* Main content */
.main-content {
    flex: 1;
    position: relative;
    overflow-y: auto;
    background: #ffffff;
}

/* Right big cross background */
.bg-right {
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    height: 150%;
    opacity: 1;
    pointer-events: none;
    z-index: 0;
}

/* Header bar */
.header {
    background: var(--header-bg);
    color: white;
    padding: 8px 24px;
    font-size: 20px;
    font-weight: bold;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 2;
}

/* Screens */
.screen {
    display: none;
    padding: 22px 34px;
    position: relative;
    z-index: 1;
}
.screen.active {
    display: block;
}

.title {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 10px;
}

/* Panels */
.panel {
    border: 2px solid var(--panel-border);
    border-radius: 10px;
    padding: 14px;
    background: white;
    margin-bottom: 18px;
}

/* Forms */
.form-row {
    display: flex;
    gap: 10px;
    margin-bottom: 8px;
}
.form-row input,
.form-row select,
.form-row textarea {
    width: 100%;
    padding: 5px;
    font-size: 12px;
}

/* Buttons */
.btn {
    padding: 6px 10px;
    background: var(--maroon);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}
.btn:hover { opacity: 0.9; }

.btn-outline {
    padding: 3px 6px;
    border-radius: 4px;
    border: 1px solid var(--maroon);
    background: #fff;
    color: var(--maroon);
    font-size: 11px;
    text-decoration: none;
    margin-right: 4px;
}
.btn-outline:hover {
    background: var(--maroon);
    color: #fff;
}

/* Search bar */
.search-bar {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
    align-items: center;
}
.search-bar input {
    padding: 4px 6px;
    font-size: 12px;
}
.search-label {
    font-size: 12px;
    font-weight: bold;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 12px;
    background: #fafafa;
}
th, td {
    border: 1px solid #ddd;
    padding: 4px 6px;
    text-align: left;
}
th { background: #f4f4f4; }

.message {
    color: green;
    font-size: 13px;
    margin-bottom: 8px;
}
.text-right { text-align: right; }
</style>
</head>
<body>

<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
    <!-- 1: PATIENT RECORDING -->
    <img src="patient.png" class="icon" onclick="showPanel('patient')" alt="Patient Recording">

    <!-- 2: PATIENT MEDICAL RECORDS -->
    <img src="medical.png" class="icon" onclick="showPanel('service')" alt="Medical Records">

    <!-- 3: QUEUE ADMIN PANEL -->
    <img src="queueadmin.png" class="icon" onclick="window.location.href='index.php'" alt="Health Flow Management System">

    <!-- 4: ADMIN PANEL ICON (BOTTOM) -->
    <img src="adminpanel.png" class="icon bottom-icon" onclick="window.location.href='admin.php'" alt="Admin Panel">
</aside>

    <!-- Main content -->
    <div class="main-content">
        <img src="bc.png" class="bg-right" alt="">

        <header class="header">
            HealthFlow Management System
        </header>

        <!-- SCREEN 1: PATIENT RECORDING / FRONT DESK -->
        <section id="patient" class="screen active">
            <h2 class="title">PATIENT PROFILE</h2>

            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- PATIENT FORM -->
            <div class="panel">
                <form method="post">
                    <input type="hidden" name="action" value="<?= $editPatient ? 'update_patient' : 'add_patient' ?>">
                    <?php if ($editPatient): ?>
                        <input type="hidden" name="patient_id" value="<?= $editPatient['id'] ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <input type="text" name="first_name" placeholder="First Name"
                               value="<?= $editPatient ? htmlspecialchars($editPatient['first_name']) : '' ?>" required>
                        <input type="text" name="last_name" placeholder="Last Name"
                               value="<?= $editPatient ? htmlspecialchars($editPatient['last_name']) : '' ?>" required>
                        <input type="date" name="birthdate"
                               value="<?= $editPatient ? htmlspecialchars($editPatient['birthdate']) : '' ?>">
                    </div>
                    <div class="form-row">
                        <select name="gender">
                            <option value="">--</option>
                            <option <?= ($editPatient && $editPatient['gender']=='Male')?'selected':''; ?>>Male</option>
                            <option <?= ($editPatient && $editPatient['gender']=='Female')?'selected':''; ?>>Female</option>
                            <option <?= ($editPatient && $editPatient['gender']=='Other')?'selected':''; ?>>Other</option>
                        </select>
                        <input type="text" name="contact" placeholder="Contact"
                               value="<?= $editPatient ? htmlspecialchars($editPatient['contact']) : '' ?>">
                        <input type="text" name="address" placeholder="Address"
                               value="<?= $editPatient ? htmlspecialchars($editPatient['address']) : '' ?>">
                    </div>
                    <button class="btn"><?= $editPatient ? 'Update Patient' : 'Save Patient' ?></button>
                    <?php if ($editPatient): ?>
                        <a href="frontdesk.php" class="btn-outline">Cancel</a>
                    <?php endif; ?>
                </form>

                <!-- PATIENT SEARCH -->
                <form method="get" class="search-bar" style="margin-top:12px;">
                    <span class="search-label">Search Patients:</span>
                    <input type="text" name="patient_search" placeholder="Name or contact"
                           value="<?= htmlspecialchars($patientSearch) ?>">
                    <button class="btn">Search</button>
                    <a href="frontdesk.php" class="btn-outline">Clear</a>
                </form>

                <h4>Recent Patients</h4>
                <table>
                    <tr>
                        <th>ID</th><th>Name</th><th>Birthdate</th><th>Gender</th><th>Contact</th><th class="text-right">Actions</th>
                    </tr>
                    <?php $rowNo = 1; ?>
                    <?php while($p = $patients->fetch_assoc()): ?>
                        <tr>
                            <td><?= $rowNo++ ?></td>
                            <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
                            <td><?= htmlspecialchars($p['birthdate']) ?></td>
                            <td><?= htmlspecialchars($p['gender']) ?></td>
                            <td><?= htmlspecialchars($p['contact']) ?></td>
                            <td class="text-right">
                                <a href="frontdesk.php?edit_patient=<?= $p['id'] ?>" class="btn-outline">Edit</a>
                                <a href="frontdesk.php?delete_patient=<?= $p['id'] ?>"
                                   class="btn-outline"
                                   onclick="return confirm('Delete this patient and all related appointments/bills?');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- APPOINTMENTS -->
            <h2 class="title">APPOINTMENTS</h2>
            <div class="panel">
                <form method="post">
                    <input type="hidden" name="action" value="<?= $editAppointment ? 'update_appointment' : 'add_appointment' ?>">
                    <?php if ($editAppointment): ?>
                        <input type="hidden" name="appt_id" value="<?= $editAppointment['id'] ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <select name="appt_patient_id" required>
                            <option value="">Select patient</option>
                            <?php
                            $ps = $conn->query("SELECT id, first_name, last_name FROM patients ORDER BY id ASC");
                            while ($row = $ps->fetch_assoc()):
                                $selected = ($editAppointment && $editAppointment['patient_id'] == $row['id']) ? 'selected' : '';
                            ?>
                            <option value="<?= $row['id'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>

                        <?php $currentService = $editAppointment ? $editAppointment['service'] : ''; ?>
                        <select name="appt_service" required>
                            <option value="">Select Service</option>
                            <option <?= $currentService=='CHECK-UP'?'selected':''; ?>>CHECK-UP</option>
                            <option <?= $currentService=='FIRST AID'?'selected':''; ?>>FIRST AID</option>
                            <option <?= $currentService=='REQUEST MEDICINE'?'selected':''; ?>>REQUEST MEDICINE</option>
                            <option <?= $currentService=='EMERGENCY ASSISTANCE'?'selected':''; ?>>EMERGENCY ASSISTANCE</option>
                        </select>

                        <input type="date" name="appt_date" required
                               value="<?= $editAppointment ? htmlspecialchars($editAppointment['appointment_date']) : '' ?>">
                        <input type="time" name="appt_time" required
                               value="<?= $editAppointment ? htmlspecialchars($editAppointment['appointment_time']) : '' ?>">
                    </div>

                    <textarea name="appt_notes" rows="2" placeholder="Notes"><?= $editAppointment ? htmlspecialchars($editAppointment['notes']) : '' ?></textarea>
                    <br>
                    <button class="btn"><?= $editAppointment ? 'Update Appointment' : 'Save Appointment' ?></button>
                    <?php if ($editAppointment): ?>
                        <a href="frontdesk.php" class="btn-outline">Cancel</a>
                    <?php endif; ?>
                </form>

                <!-- APPOINTMENT SEARCH -->
                <form method="get" class="search-bar" style="margin-top:12px;">
                    <span class="search-label">Search Appointments:</span>
                    <input type="text" name="appt_search" placeholder="Name or service"
                           value="<?= htmlspecialchars($apptSearch) ?>">
                    <span class="search-label">Date:</span>
                    <input type="date" name="appt_date" value="<?= htmlspecialchars($apptDate) ?>">
                    <button class="btn">Filter</button>
                    <a href="frontdesk.php" class="btn-outline">Clear</a>
                </form>

                <h4>Recent Appointments</h4>
                <table>
                    <tr>
                        <th>ID</th><th>Patient</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th class="text-right">Actions</th>
                    </tr>
                    <?php $rowNo = 1; ?>
                    <?php while($a = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $rowNo++ ?></td>
                            <td><?= htmlspecialchars($a['first_name'].' '.$a['last_name']) ?></td>
                            <td><?= htmlspecialchars($a['service']) ?></td>
                            <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($a['appointment_time']) ?></td>
                            <td><?= htmlspecialchars($a['status']) ?></td>
                            <td class="text-right">
                                <a href="frontdesk.php?edit_appointment=<?= $a['id'] ?>" class="btn-outline">Edit</a>
                                <a href="frontdesk.php?delete_appointment=<?= $a['id'] ?>"
                                   class="btn-outline"
                                   onclick="return confirm('Delete this appointment?');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- BILLING -->
            <h2 class="title">BILLING</h2>
            <div class="panel">
                <form method="post">
                    <input type="hidden" name="action" value="add_bill">
                    <div class="form-row">
                        <select name="bill_patient_id" id="billPatientSelect" required onchange="loadAppointments(this.value)">
                            <option value="">Select patient</option>
                            <?php
                            $ps2 = $conn->query("SELECT id, first_name, last_name FROM patients ORDER BY first_name ASC");
                            while ($row = $ps2->fetch_assoc()):
                            ?>
                            <option value="<?= $row['id'] ?>">
                                <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <select name="bill_appointment_id" id="appointmentSelect">
                            <option value="">No appointment (walk-in)</option>
                            <!-- Appointments will be loaded via AJAX -->
                        </select>
                        
                        <input type="number" step="0.01" name="bill_amount" placeholder="Amount" required min="0">
                    </div>
                    <button class="btn">Save Bill</button>
                </form>

                <h4>Recent Bills</h4>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Appointment</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                    <?php 
                    $rowNo = 1;
                    while($b = $bills->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?= $rowNo++ ?></td>
                            <td><?= htmlspecialchars($b['first_name'].' '.$b['last_name']) ?></td>
                            <td>
                                <?php if($b['appointment_id']): ?>
                                    #<?= $b['appointment_id'] ?> 
                                    (<?= htmlspecialchars($b['appointment_date'] ?? '') ?>)
                                <?php else: ?>
                                    Walk-in
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['appointment_service'] ?? 'N/A') ?></td>
                            <td>₱<?= number_format($b['amount'], 2) ?></td>
                            <td>
                                <?php 
                                $status = $b['status'];
                                $color = 'black';
                                if($status == 'paid') $color = 'green';
                                if($status == 'pending') $color = 'orange';
                                if($status == 'cancelled') $color = 'red';
                                ?>
                                <span style="color: <?= $color ?>; font-weight: bold;">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </section>

        <!-- SCREEN 2: MEDICAL SERVICE RECORDS -->
        <section id="service" class="screen">
            <h2 class="title">MEDICAL SERVICE RECORDS</h2>
            <?php
            function renderServiceTable($conn, $serviceName) {
                $stmt = $conn->prepare("
                    SELECT a.*, p.first_name, p.last_name
                    FROM appointments a
                    JOIN patients p ON a.patient_id = p.id
                    WHERE a.service = ?
                    ORDER BY a.id ASC
                ");
                $stmt->bind_param("s", $serviceName);
                $stmt->execute();
                $res = $stmt->get_result();

                echo "<div class='panel'><h3>".htmlspecialchars($serviceName)."</h3>";
                echo "<table><tr>
                        <th>ID</th><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Notes</th>
                      </tr>";

                $i = 1;
                while ($row = $res->fetch_assoc()) {
                    echo "<tr>
                        <td>{$i}</td>
                        <td>".htmlspecialchars($row['first_name'].' '.$row['last_name'])."</td>
                        <td>{$row['appointment_date']}</td>
                        <td>{$row['appointment_time']}</td>
                        <td>{$row['status']}</td>
                        <td>".htmlspecialchars($row['notes'])."</td>
                    </tr>";
                    $i++;
                }

                echo "</table></div>";
                $stmt->close();
            }

            renderServiceTable($conn, 'CHECK-UP');
            renderServiceTable($conn, 'FIRST AID');
            renderServiceTable($conn, 'REQUEST MEDICINE');
            renderServiceTable($conn, 'EMERGENCY ASSISTANCE');
            ?>
        </section>

    </div>
</div>

<script>
function showPanel(panelName) {
    document.querySelectorAll(".screen").forEach(s => s.classList.remove("active"));
    document.getElementById(panelName).classList.add("active");
}

// NEW FUNCTION for loading appointments
function loadAppointments(patientId) {
    if (!patientId) {
        document.getElementById('appointmentSelect').innerHTML = '<option value="">No appointment (walk-in)</option>';
        return;
    }
    
    fetch('get_appointments.php?patient_id=' + patientId)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('appointmentSelect');
            select.innerHTML = '<option value="">No appointment (walk-in)</option>';
            
            data.forEach(appointment => {
                const option = document.createElement('option');
                option.value = appointment.id;
                option.textContent = `Appt #${appointment.id} - ${appointment.service} (${appointment.date})`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error:', error));
}
</script>

</body>
</html>