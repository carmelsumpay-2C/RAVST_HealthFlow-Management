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

/*
 * CLOSE CURRENT — record time ONLY if started_at exists
 */
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

/*
 * EMERGENCY FIRST — YOUR LOGIC (UNCHANGED)
 */
$res = $conn->query("
    SELECT id
    FROM tokens
    WHERE status = 'waiting'
    ORDER BY 
        (service = 'EMERGENCY ASSISTANCE') DESC,
        created_at ASC
    LIMIT 1
");

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();

    $stmt = $conn->prepare("
        UPDATE tokens
        SET status = 'serving', started_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true]);
