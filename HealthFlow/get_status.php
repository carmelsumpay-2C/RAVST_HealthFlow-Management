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

$current = null;
$res = $conn->query("
    SELECT id, code, service, UNIX_TIMESTAMP(started_at) started_at
    FROM tokens
    WHERE status='serving'
    LIMIT 1
");
if ($res && $res->num_rows) $current = $res->fetch_assoc();

$next=[];
$res=$conn->query("
    SELECT code FROM tokens
    WHERE status='waiting'
    ORDER BY 
      (service='EMERGENCY ASSISTANCE') DESC,
      created_at ASC
    LIMIT 2
");
while($r=$res->fetch_assoc()) $next[]=$r;

$res=$conn->query("
    SELECT 
      SUM(status='waiting') waiting,
      SUM(status='done') served
    FROM tokens
");
$row=$res->fetch_assoc();

echo json_encode([
    'current'=>$current,
    'next'=>$next,
    'waiting_count'=>(int)$row['waiting'],
    'served_count'=>(int)$row['served']
]);
