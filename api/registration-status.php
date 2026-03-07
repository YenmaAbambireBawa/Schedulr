<?php
/**
 * Registration Status API
 * Returns current status of a registration by ?id=
 * Simulates time-based progression: pending → processing → confirmed
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing registration id']);
    exit;
}

$savePath = __DIR__ . '/../user_data/registrations.json';
if (!file_exists($savePath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No registrations found']);
    exit;
}

$all = json_decode(file_get_contents($savePath), true) ?? [];
if (!isset($all[$id])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Registration not found']);
    exit;
}

$reg     = $all[$id];
$elapsed = time() - strtotime($reg['submitted_at']);

if ($elapsed < 10) {
    $status  = 'pending';
    $message = 'Registration received. Waiting to connect to myCAMU...';
} elseif ($elapsed < 35) {
    $status  = 'processing';
    $message = 'Logging into myCAMU and submitting your timetable options...';
} else {
    $status  = 'confirmed';
    $message = 'Your registration has been successfully submitted to myCAMU!';
}

$all[$id]['status'] = $status;
@file_put_contents($savePath, json_encode($all, JSON_PRETTY_PRINT));

echo json_encode([
    'success'          => true,
    'registration_id'  => $id,
    'status'           => $status,
    'message'          => $message,
    'submitted_at'     => $reg['submitted_at'],
    'camu_job_id'      => $reg['camu_job_id'],
    'timetable_options' => $reg['timetable_options'],
]);
