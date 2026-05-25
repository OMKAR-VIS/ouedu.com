<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'summary';

if ($action === 'summary') {
    $students = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'] ?? 0;
    $videos = $conn->query("SELECT COUNT(*) AS c FROM contents WHERE content_type='video'")->fetch_assoc()['c'] ?? 0;
    $pdfs = $conn->query("SELECT COUNT(*) AS c FROM contents WHERE content_type='pdf'")->fetch_assoc()['c'] ?? 0;
    $avgProgress = $conn->query("SELECT IFNULL(ROUND(AVG(progress)),0) AS p FROM student_profiles")->fetch_assoc()['p'] ?? 0;
    $messages = $conn->query("SELECT COUNT(*) AS c FROM contacts")->fetch_assoc()['c'] ?? 0;
    echo json_encode(['ok' => true, 'data' => [
        'totalStudents' => (int) $students,
        'totalVideos' => (int) $videos,
        'totalPdfs' => (int) $pdfs,
        'avgProgress' => (int) $avgProgress,
        'totalMessages' => (int) $messages
    ]]);
    exit();
}

if ($action === 'list_contents') {
    $res = $conn->query("SELECT id, content_type, course, subject, title, youtube_url, file_path, created_at FROM contents ORDER BY id DESC LIMIT 100");
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit();
}

if ($action === 'delete_content') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['ok' => false, 'message' => 'Invalid id']);
        exit();
    }
    $get = $conn->prepare("SELECT file_path FROM contents WHERE id = ?");
    $get->bind_param('i', $id);
    $get->execute();
    $row = $get->get_result()->fetch_assoc();
    if ($row && !empty($row['file_path'])) {
        $path = dirname(__DIR__) . '/' . $row['file_path'];
        if (is_file($path)) {
            unlink($path);
        }
    }
    $del = $conn->prepare('DELETE FROM contents WHERE id = ?');
    $del->bind_param('i', $id);
    $del->execute();
    echo json_encode(['ok' => true, 'message' => 'Content deleted']);
    exit();
}

if ($action === 'students') {
    $res = $conn->query("SELECT u.id, u.student_id, u.name, u.email, u.mobile, u.created_at,
        IFNULL(sp.progress,0) AS progress, IFNULL(sp.videos_watched,0) AS videos_watched
        FROM users u LEFT JOIN student_profiles sp ON sp.user_id = u.id
        WHERE u.role='student' ORDER BY u.id DESC");
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit();
}

if ($action === 'get_student') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT u.id, u.student_id, u.name, u.email, u.mobile, u.created_at,
        IFNULL(sp.progress,0) AS progress, IFNULL(sp.videos_watched,0) AS videos_watched,
        c.blood_group, c.address, c.father_name, c.mother_name, c.dob, c.class_name, c.school_name, c.extra_note
        FROM users u
        LEFT JOIN student_profiles sp ON sp.user_id = u.id
        LEFT JOIN student_id_cards c ON c.user_id = u.id
        WHERE u.id = ? AND u.role='student' LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        echo json_encode(['ok' => false, 'message' => 'Student not found']);
        exit();
    }
    if (empty($row['class_name'])) {
        createStudentIdCard($conn, $id, $row['name'], $row['email'], $row['mobile'] ?? '');
    }
    echo json_encode(['ok' => true, 'data' => $row]);
    exit();
}

if ($action === 'update_student') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $mobile = trim($_POST['mobile'] ?? '');
    $progress = max(0, min(100, (int) ($_POST['progress'] ?? 0)));
    $videosWatched = max(0, (int) ($_POST['videos_watched'] ?? 0));
    $bloodGroup = trim($_POST['blood_group'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $motherName = trim($_POST['mother_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $className = trim($_POST['class_name'] ?? 'JAC Class 10');
    $schoolName = trim($_POST['school_name'] ?? 'Our Education Mentor');
    $extraNote = trim($_POST['extra_note'] ?? '');

    if ($id <= 0 || $name === '' || $email === '') {
        echo json_encode(['ok' => false, 'message' => 'Name and email required']);
        exit();
    }

    $upd = $conn->prepare("UPDATE users SET name = ?, email = ?, mobile = ? WHERE id = ? AND role='student'");
    $upd->bind_param('sssi', $name, $email, $mobile, $id);
    $upd->execute();

    $prof = $conn->prepare("INSERT INTO student_profiles (user_id, progress, videos_watched) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE progress = VALUES(progress), videos_watched = VALUES(videos_watched)");
    $prof->bind_param('iii', $id, $progress, $videosWatched);
    $prof->execute();

    $card = $conn->prepare("INSERT INTO student_id_cards (user_id, blood_group, address, father_name, mother_name, dob, class_name, school_name, extra_note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE blood_group=VALUES(blood_group), address=VALUES(address), father_name=VALUES(father_name),
        mother_name=VALUES(mother_name), dob=VALUES(dob), class_name=VALUES(class_name), school_name=VALUES(school_name), extra_note=VALUES(extra_note)");
    $card->bind_param('issssssss', $id, $bloodGroup, $address, $fatherName, $motherName, $dob, $className, $schoolName, $extraNote);
    $card->execute();

    echo json_encode(['ok' => true, 'message' => 'Student & ID card updated']);
    exit();
}

if ($action === 'upload_video') {
    $course = trim($_POST['course'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $youtubeUrl = normalizeYoutubeUrl(trim($_POST['youtube_url'] ?? ''));
    if ($course === '' || $subject === '' || $title === '' || $youtubeUrl === '') {
        echo json_encode(['ok' => false, 'message' => 'All fields required (valid YouTube URL)']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO contents (content_type, course, subject, title, youtube_url) VALUES ('video', ?, ?, ?, ?)");
    $stmt->bind_param('ssss', $course, $subject, $title, $youtubeUrl);
    if (!$stmt->execute()) {
        echo json_encode(['ok' => false, 'message' => 'Upload failed: ' . $conn->error]);
        exit();
    }
    echo json_encode(['ok' => true, 'message' => 'Video uploaded successfully', 'id' => $stmt->insert_id]);
    exit();
}

if ($action === 'upload_pdf') {
    $course = trim($_POST['course'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $title = trim($_POST['title'] ?? '');
    if ($course === '' || $subject === '' || $title === '' || empty($_FILES['pdf_file']['tmp_name'])) {
        echo json_encode(['ok' => false, 'message' => 'All fields required']);
        exit();
    }
    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $name = basename($_FILES['pdf_file']['name']);
    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'pdf') {
        echo json_encode(['ok' => false, 'message' => 'Only PDF allowed']);
        exit();
    }
    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    $dest = $uploadDir . '/' . $safeName;
    if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $dest)) {
        echo json_encode(['ok' => false, 'message' => 'Upload failed']);
        exit();
    }
    $relativePath = 'uploads/' . $safeName;
    $stmt = $conn->prepare("INSERT INTO contents (content_type, course, subject, title, file_path) VALUES ('pdf', ?, ?, ?, ?)");
    $stmt->bind_param('ssss', $course, $subject, $title, $relativePath);
    if (!$stmt->execute()) {
        echo json_encode(['ok' => false, 'message' => 'Save failed: ' . $conn->error]);
        exit();
    }
    echo json_encode(['ok' => true, 'message' => 'PDF uploaded successfully', 'id' => $stmt->insert_id]);
    exit();
}

if ($action === 'contacts') {
    $res = $conn->query("SELECT id, name, email, phone, message, created_at FROM contacts ORDER BY id DESC");
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit();
}

if ($action === 'delete_contact') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM contacts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['ok' => true, 'message' => 'Message deleted']);
    exit();
}

if ($action === 'notices') {
    $res = $conn->query("SELECT id, title, message, target, is_active, created_at FROM notices ORDER BY id DESC");
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit();
}

if ($action === 'save_notice') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $target = $_POST['target'] ?? 'website';
    $isActive = (int) ($_POST['is_active'] ?? 1);
    if ($title === '' || $message === '' || !in_array($target, ['website', 'dashboard'], true)) {
        echo json_encode(['ok' => false, 'message' => 'Fill all fields']);
        exit();
    }
    if ($id > 0) {
        $stmt = $conn->prepare('UPDATE notices SET title=?, message=?, target=?, is_active=? WHERE id=?');
        $stmt->bind_param('sssii', $title, $message, $target, $isActive, $id);
    } else {
        $stmt = $conn->prepare('INSERT INTO notices (title, message, target, is_active) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $title, $message, $target, $isActive);
    }
    $stmt->execute();
    echo json_encode(['ok' => true, 'message' => 'Notice saved']);
    exit();
}

if ($action === 'delete_notice') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM notices WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['ok' => true, 'message' => 'Notice deleted']);
    exit();
}

if ($action === 'tests') {
    $res = $conn->query("SELECT id, course, subject, title, test_url, is_active, created_at FROM tests ORDER BY id DESC");
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit();
}

if ($action === 'save_test') {
    $id = (int) ($_POST['id'] ?? 0);
    $course = trim($_POST['course'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $testUrl = trim($_POST['test_url'] ?? '');
    $isActive = (int) ($_POST['is_active'] ?? 1);
    if ($course === '' || $subject === '' || $title === '') {
        echo json_encode(['ok' => false, 'message' => 'Course, subject and title required']);
        exit();
    }
    if ($id > 0) {
        $stmt = $conn->prepare('UPDATE tests SET course=?, subject=?, title=?, test_url=?, is_active=? WHERE id=?');
        $stmt->bind_param('ssssii', $course, $subject, $title, $testUrl, $isActive, $id);
    } else {
        $stmt = $conn->prepare('INSERT INTO tests (course, subject, title, test_url, is_active) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssi', $course, $subject, $title, $testUrl, $isActive);
    }
    $stmt->execute();
    echo json_encode(['ok' => true, 'message' => 'Test saved']);
    exit();
}

if ($action === 'delete_test') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM tests WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['ok' => true, 'message' => 'Test deleted']);
    exit();
}

echo json_encode(['ok' => false, 'message' => 'Invalid action']);
