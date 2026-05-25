<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=UTF-8');

$target = $_GET['target'] ?? 'website';
if (!in_array($target, ['website', 'dashboard'], true)) {
    echo json_encode(['ok' => false, 'data' => []]);
    exit();
}

$stmt = $conn->prepare("SELECT id, title, message FROM notices WHERE target = ? AND is_active = 1 ORDER BY id DESC LIMIT 5");
$stmt->bind_param('s', $target);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['ok' => true, 'data' => $rows]);
