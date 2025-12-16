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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST only']);
    exit;
}

$service = $_POST['service'] ?? '';
if ($service === '') {
    echo json_encode(['success' => false, 'message' => 'Service required']);
    exit;
}

// Prefix
$prefix = 'C';
if (stripos($service,'CHECK')===0) $prefix='C';
elseif (stripos($service,'FIRST')===0) $prefix='F';
elseif (stripos($service,'REQUEST')===0) $prefix='R';
elseif (stripos($service,'EMERGENCY')===0) $prefix='E';

// Next number
$stmt = $conn->prepare("
    SELECT code FROM tokens
    WHERE code LIKE CONCAT(?, '-%')
    ORDER BY id DESC LIMIT 1
");
$stmt->bind_param("s",$prefix);
$stmt->execute();
$stmt->bind_result($last);
$stmt->fetch();
$stmt->close();

$num = $last ? intval(substr($last,2)) + 1 : 1;
$code = sprintf("%s-%04d",$prefix,$num);

// Insert token
$stmt = $conn->prepare("
    INSERT INTO tokens (code, service, status, linked)
    VALUES (?, ?, 'waiting', 0)
");
$stmt->bind_param("ss",$code,$service);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success'=>true,
    'code'=>$code,
    'service'=>$service
]);
