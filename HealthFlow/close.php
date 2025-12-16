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
header('Content-Type: application/json');
require 'db.php';

$conn->query("
    UPDATE tokens
    SET 
        status = 'done',
        ended_at = NOW(),
        serving_seconds = IF(
            started_at IS NOT NULL,
            TIMESTAMPDIFF(SECOND, started_at, NOW()),
            0
        )
    WHERE status = 'serving'
");

echo json_encode(['success' => true]);
