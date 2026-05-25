<?php

function generateUniqueStudentId(mysqli $conn): string
{
    do {
        $id = 'UEM' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare('SELECT id FROM users WHERE student_id = ? LIMIT 1');
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $id;
}

function defaultSubjects(): array
{
    return ['English', 'Mathematics', 'Science', 'Social Science', 'Hindi', 'Computer'];
}

function normalizeYoutubeUrl(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return $url;
}

function createStudentIdCard(mysqli $conn, int $userId, string $name, string $email, string $mobile): void
{
    $stmt = $conn->prepare("INSERT IGNORE INTO student_id_cards (user_id, father_name, class_name, school_name, address) VALUES (?, '', 'JAC Class 10', 'Our Education Mentor', '')");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
}
