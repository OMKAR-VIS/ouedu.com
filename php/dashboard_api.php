<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit();
}

$uid = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? 'profile';

if ($action === 'profile') {
    $stmt = $conn->prepare("SELECT u.student_id, u.name, u.email, u.mobile, IFNULL(sp.progress,0) AS progress, IFNULL(sp.videos_watched,0) AS videos_watched FROM users u LEFT JOIN student_profiles sp ON sp.user_id = u.id WHERE u.id = ? LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo json_encode(['ok' => true, 'data' => $row]);
    exit();
}

if ($action === 'content') {
    $type = $_GET['type'] ?? 'video';
    if (!in_array($type, ['video', 'pdf'], true)) {
        echo json_encode(['ok' => false, 'message' => 'Invalid type']);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, course, subject, title, youtube_url, file_path FROM contents WHERE content_type = ? ORDER BY course, subject, id DESC");
    $stmt->bind_param('s', $type);
    $stmt->execute();
    $res = $stmt->get_result();

    $batches = [];
    while ($row = $res->fetch_assoc()) {
        $batch = trim($row['course']) ?: 'JAC Class 10';
        $subject = trim($row['subject'] ?? '') ?: 'General';
        if (!isset($batches[$batch])) {
            $batches[$batch] = [];
        }
        if (!isset($batches[$batch][$subject])) {
            $batches[$batch][$subject] = [];
        }
        $batches[$batch][$subject][] = $row;
    }

    echo json_encode([
        'ok' => true,
        'data' => [
            'batches' => $batches,
            'subjects' => defaultSubjects()
        ]
    ]);
    exit();
}

if ($action === 'progress') {
    $stmt = $conn->prepare("SELECT progress, videos_watched FROM student_profiles WHERE user_id = ? LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        $row = ['progress' => 0, 'videos_watched' => 0];
    }

    $videos = $conn->query("SELECT COUNT(*) AS c FROM contents WHERE content_type='video'")->fetch_assoc()['c'] ?? 0;
    $pdfs = $conn->query("SELECT COUNT(*) AS c FROM contents WHERE content_type='pdf'")->fetch_assoc()['c'] ?? 0;

    echo json_encode([
        'ok' => true,
        'data' => array_merge($row, [
            'total_videos' => (int) $videos,
            'total_pdfs' => (int) $pdfs
        ])
    ]);
    exit();
}

if ($action === 'notices') {
    $stmt = $conn->prepare("SELECT id, title, message FROM notices WHERE target = 'dashboard' AND is_active = 1 ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit();
}

echo json_encode(['ok' => false, 'message' => 'Invalid action']);
