<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../index.html');
    exit();
}

$uid = (int) ($_GET['user_id'] ?? 0);
if ($uid <= 0) {
    die('Invalid student');
}

$stmt = $conn->prepare("SELECT u.student_id, u.name, u.email, u.mobile, u.created_at,
    c.blood_group, c.address, c.father_name, c.mother_name, c.dob, c.class_name, c.school_name, c.extra_note
    FROM users u
    LEFT JOIN student_id_cards c ON c.user_id = u.id
    WHERE u.id = ? AND u.role = 'student' LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
if (!$s) {
    die('Student not found');
}

$qrData = urlencode($s['student_id'] . '|' . $s['name'] . '|' . $s['email']);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . $qrData;
$joined = date('d M Y', strtotime($s['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ID Card - <?= htmlspecialchars($s['student_id']) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', sans-serif; background: #e2e8f0; padding: 24px; }
.toolbar { text-align: center; margin-bottom: 20px; }
.toolbar button { padding: 12px 28px; background: #1e40af; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin: 0 8px; }
.cards-wrap { display: flex; flex-wrap: wrap; gap: 24px; justify-content: center; }
.id-card {
    width: 340px; height: 214px; border-radius: 14px; overflow: hidden;
    box-shadow: 0 12px 32px rgba(0,0,0,0.2); position: relative;
}
.id-front { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; padding: 16px; }
.id-back { background: #fff; color: #0f172a; padding: 16px; text-align: center; border: 2px solid #1e40af; }
.brand { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.brand img { width: 36px; height: 36px; object-fit: contain; background: #fff; border-radius: 8px; padding: 2px; }
.brand h1 { font-size: 11px; line-height: 1.2; }
.student-name { font-size: 18px; font-weight: 700; margin: 8px 0 4px; }
.sid { background: #fbbf24; color: #1e293b; display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 8px; }
.details { font-size: 10px; line-height: 1.5; opacity: 0.95; }
.details div { margin-bottom: 2px; }
.id-back h3 { font-size: 12px; color: #1e40af; margin-bottom: 8px; }
.id-back img { width: 100px; height: 100px; }
.id-back p { font-size: 9px; margin-top: 6px; color: #64748b; }
@media print {
    body { background: #fff; padding: 0; }
    .toolbar { display: none; }
    .cards-wrap { gap: 16px; }
    .id-card { break-inside: avoid; page-break-inside: avoid; }
}
</style>
</head>
<body>
<div class="toolbar">
    <button onclick="window.print()">Download / Print ID Card</button>
    <button onclick="window.close()">Close</button>
</div>
<div class="cards-wrap">
    <div class="id-card id-front">
        <div class="brand">
            <img src="../image/OUR_EDUCATION_MENTOR'S_png.png" alt="Logo">
            <h1>OUR EDUCATION MENTOR<br><small style="font-weight:400;opacity:.9">JAC Board Class 10</small></h1>
        </div>
        <div class="sid"><?= htmlspecialchars($s['student_id']) ?></div>
        <div class="student-name"><?= htmlspecialchars($s['name']) ?></div>
        <div class="details">
            <div><strong>Class:</strong> <?= htmlspecialchars($s['class_name'] ?: 'JAC Class 10') ?></div>
            <div><strong>School:</strong> <?= htmlspecialchars($s['school_name'] ?: 'Our Education Mentor') ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($s['email']) ?></div>
            <div><strong>Mobile:</strong> <?= htmlspecialchars($s['mobile'] ?: '-') ?></div>
            <div><strong>Blood:</strong> <?= htmlspecialchars($s['blood_group'] ?: 'N/A') ?> | <strong>DOB:</strong> <?= htmlspecialchars($s['dob'] ?: 'N/A') ?></div>
            <div><strong>Father:</strong> <?= htmlspecialchars($s['father_name'] ?: 'N/A') ?></div>
            <div><strong>Address:</strong> <?= htmlspecialchars($s['address'] ?: 'N/A') ?></div>
            <?php if (!empty($s['extra_note'])): ?>
            <div><strong>Note:</strong> <?= htmlspecialchars($s['extra_note']) ?></div>
            <?php endif; ?>
            <div style="margin-top:4px;opacity:.8">Joined: <?= $joined ?></div>
        </div>
    </div>
    <div class="id-card id-back">
        <h3>SCAN QR CODE</h3>
        <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code">
        <p><?= htmlspecialchars($s['student_id']) ?></p>
        <p>Our Education Mentor — Student Verification</p>
    </div>
</div>
</body>
</html>
