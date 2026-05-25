<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'invalid';
    exit();
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo 'invalid';
    exit();
}

$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE LOWER(email) = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo 'invalid';
    exit();
}

$adminEmail = 'admin@oureducationmentor.com';
if ($user['role'] === 'admin' && strtolower($user['email']) !== $adminEmail) {
    echo 'invalid';
    exit();
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

echo $user['role'] === 'admin' ? 'admin' : 'student';
