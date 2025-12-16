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
// get_appointments.php
require 'db.php';

header('Content-Type: application/json');

if (isset($_GET['patient_id'])) {
    $patientId = (int)$_GET['patient_id'];
    
    $stmt = $conn->prepare("
        SELECT id, service, appointment_date as date 
        FROM appointments 
        WHERE patient_id = ? AND status != 'cancelled'
        ORDER BY appointment_date DESC
    ");
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode($appointments);
    $stmt->close();
}
?>