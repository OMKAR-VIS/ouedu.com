<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Invalid request method']);
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    echo json_encode(['ok' => false, 'message' => 'Name, email and message are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => 'Invalid email address']);
    exit();
}

$stmt = $conn->prepare('INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $name, $email, $phone, $message);

if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'message' => 'Could not send message. Please try again.']);
    exit();
}

$stmt->close();
echo json_encode(['ok' => true, 'message' => 'Message sent successfully! Admin will reply soon.']);
