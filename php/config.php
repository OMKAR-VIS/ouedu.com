<?php
session_start();

$dbHost = "127.0.0.1";
$dbUser = "root";
$dbPass = "";
$dbName = "oureducationmentor";

$conn = @new mysqli($dbHost, $dbUser, $dbPass);
if ($conn->connect_error) {
    http_response_code(500);
    die("Database connection failed.");
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbName);

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    mobile VARCHAR(15) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$mobileColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'mobile'");
if ($mobileColumn && $mobileColumn->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN mobile VARCHAR(15) DEFAULT NULL AFTER email");
}

$studentIdColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'student_id'");
if ($studentIdColumn && $studentIdColumn->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN student_id VARCHAR(12) UNIQUE NULL AFTER id");
}

$subjectColumn = $conn->query("SHOW COLUMNS FROM contents LIKE 'subject'");
if ($subjectColumn && $subjectColumn->num_rows === 0) {
    $conn->query("ALTER TABLE contents ADD COLUMN subject VARCHAR(80) DEFAULT NULL AFTER course");
}

require_once __DIR__ . '/helpers.php';

$backfill = $conn->query("SELECT id FROM users WHERE role='student' AND (student_id IS NULL OR student_id = '')");
if ($backfill) {
    while ($row = $backfill->fetch_assoc()) {
        $sid = generateUniqueStudentId($conn);
        $upd = $conn->prepare("UPDATE users SET student_id = ? WHERE id = ?");
        $upd->bind_param('si', $sid, $row['id']);
        $upd->execute();
        $upd->close();
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    progress INT NOT NULL DEFAULT 0,
    videos_watched INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('video','pdf') NOT NULL,
    course VARCHAR(80) NOT NULL,
    title VARCHAR(180) NOT NULL,
    youtube_url VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(40) DEFAULT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS student_id_cards (
    user_id INT PRIMARY KEY,
    blood_group VARCHAR(10) DEFAULT NULL,
    address TEXT,
    father_name VARCHAR(120) DEFAULT NULL,
    mother_name VARCHAR(120) DEFAULT NULL,
    dob VARCHAR(20) DEFAULT NULL,
    class_name VARCHAR(80) DEFAULT 'JAC Class 10',
    school_name VARCHAR(180) DEFAULT 'Our Education Mentor',
    extra_note TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    target ENUM('website','dashboard') NOT NULL DEFAULT 'website',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course VARCHAR(80) NOT NULL,
    subject VARCHAR(80) NOT NULL,
    title VARCHAR(180) NOT NULL,
    test_url VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$adminEmail = "admin@oureducationmentor.com";
$adminPassword = "Admin@123";
$adminName = "Super Admin";

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $adminEmail);
$stmt->execute();
$result = $stmt->get_result();
$adminRow = $result ? $result->fetch_assoc() : null;
$stmt->close();

$backfillCards = $conn->query("SELECT u.id, u.name, u.email, u.mobile FROM users u LEFT JOIN student_id_cards c ON c.user_id = u.id WHERE u.role='student' AND c.user_id IS NULL");
if ($backfillCards) {
    while ($row = $backfillCards->fetch_assoc()) {
        createStudentIdCard($conn, (int) $row['id'], $row['name'], $row['email'], $row['mobile'] ?? '');
    }
}

if (!$adminRow) {
    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $adminMobile = '9999999999';
    $insertAdmin = $conn->prepare("INSERT INTO users (name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'admin')");
    $insertAdmin->bind_param('ssss', $adminName, $adminEmail, $adminMobile, $hash);
    $insertAdmin->execute();
    $insertAdmin->close();
} elseif ($adminRow['role'] === 'admin' && !password_verify($adminPassword, $adminRow['password'])) {
    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $updateAdmin = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    $updateAdmin->bind_param('si', $hash, $adminRow['id']);
    $updateAdmin->execute();
    $updateAdmin->close();
}
