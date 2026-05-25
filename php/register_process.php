<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Invalid method"]);
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$reservedAdminEmail = 'admin@oureducationmentor.com';
$mobile = trim($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $mobile === '' || $password === '') {
    echo json_encode(["ok" => false, "message" => "All fields are required"]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["ok" => false, "message" => "Invalid email"]);
    exit();
}

if ($email === $reservedAdminEmail) {
    echo json_encode(["ok" => false, "message" => "This email cannot be used for registration"]);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(["ok" => false, "message" => "Password must be at least 6 characters"]);
    exit();
}

if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(["ok" => false, "message" => "Enter valid 10 digit mobile number"]);
    exit();
}

$check = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1");
$check->bind_param("s", $email);
$check->execute();
$existing = $check->get_result();
if ($existing->num_rows > 0) {
    echo json_encode(["ok" => false, "message" => "Email already registered"]);
    exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$studentId = generateUniqueStudentId($conn);
$insert = $conn->prepare("INSERT INTO users (student_id, name, email, mobile, password, role) VALUES (?, ?, ?, ?, ?, 'student')");
$insert->bind_param("sssss", $studentId, $name, $email, $mobile, $hash);

if (!$insert->execute()) {
    echo json_encode(["ok" => false, "message" => "Registration failed"]);
    exit();
}

$userId = $insert->insert_id;
$createProfile = $conn->prepare("INSERT INTO student_profiles (user_id, progress, videos_watched) VALUES (?, 0, 0)");
$createProfile->bind_param("i", $userId);
$createProfile->execute();

createStudentIdCard($conn, $userId, $name, $email, $mobile);

echo json_encode(["ok" => true, "message" => "Registration successful"]);
