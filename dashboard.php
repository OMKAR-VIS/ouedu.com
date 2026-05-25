<?php
require_once __DIR__ . '/php/config.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: index.html');
    exit();
}

$stmt = $conn->prepare("SELECT student_id, name FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$studentCode = $student['student_id'] ?? ('UEM' . str_pad((string) $_SESSION['user_id'], 9, '0', STR_PAD_LEFT));
$studentName = $student['name'] ?? $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Our Education Mentor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="dashboard-page">
<header class="dash-topbar">
    <div class="topbar-inner">
        <a class="brand" href="index.html">
            <img src="image/OUR_EDUCATION_MENTOR'S_png.png" alt="logo">
            <span>OUR EDUCATION MENTOR</span>
        </a>
        <div class="topbar-right">
            <span class="user-pill"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($studentName) ?></span>
            <button type="button" class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt me-1"></i> Logout</button>
        </div>
    </div>
</header>

<div class="dash-layout">
    <aside class="dash-sidebar">
        <div class="profile-card">
            <div class="avatar"><i class="fas fa-graduation-cap"></i></div>
            <h6><?= htmlspecialchars($studentName) ?></h6>
            <p class="student-id"><i class="fas fa-id-card me-1"></i><?= htmlspecialchars($studentCode) ?></p>
        </div>
        <nav class="dash-nav">
            <a href="#" data-panel="videos" class="active"><i class="fas fa-play-circle"></i> Videos</a>
            <a href="#" data-panel="pdfs"><i class="fas fa-file-pdf"></i> PDF Notes</a>
            <a href="#" data-panel="progress"><i class="fas fa-chart-line"></i> My Progress</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div id="dashNotices" class="dash-notices" style="display:none;"></div>

        <div class="welcome-banner">
            <div>
                <h2>Welcome back, <?= htmlspecialchars($studentName) ?>!</h2>
                <p>Continue learning with videos, PDF notes and track your progress.</p>
            </div>
            <div class="id-badge"><?= htmlspecialchars($studentCode) ?></div>
        </div>

        <section id="panel-videos" class="dash-panel active">
            <div class="panel-head">
                <h4><i class="fas fa-video me-2"></i>Video Lectures</h4>
                <button type="button" class="btn-back-sm" id="videoBack" style="display:none;"><i class="fas fa-arrow-left"></i> Back</button>
            </div>
            <div id="videoSteps">
                <p class="step-label">Step 1 — Select Batch</p>
                <div class="card-grid" id="videoBatchGrid"></div>
                <div id="videoSubjectWrap" style="display:none;">
                    <p class="step-label">Step 2 — Select Subject</p>
                    <div class="card-grid" id="videoSubjectGrid"></div>
                </div>
                <div id="videoPlayerWrap" style="display:none;">
                    <p class="step-label">Step 3 — Watch Videos</p>
                    <div class="player-layout">
                        <div class="player-box"><iframe id="videoFrame" src="" allowfullscreen title="Video"></iframe></div>
                        <div class="playlist-box" id="videoList"></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="panel-pdfs" class="dash-panel">
            <div class="panel-head">
                <h4><i class="fas fa-file-pdf me-2"></i>PDF Notes</h4>
                <button type="button" class="btn-back-sm" id="pdfBack" style="display:none;"><i class="fas fa-arrow-left"></i> Back</button>
            </div>
            <div id="pdfSteps">
                <p class="step-label">Step 1 — Select Batch</p>
                <div class="card-grid" id="pdfBatchGrid"></div>
                <div id="pdfSubjectWrap" style="display:none;">
                    <p class="step-label">Step 2 — Select Subject</p>
                    <div class="card-grid" id="pdfSubjectGrid"></div>
                </div>
                <div id="pdfDownloadWrap" style="display:none;">
                    <p class="step-label">Step 3 — Download PDF</p>
                    <div id="pdfList" class="pdf-list"></div>
                </div>
            </div>
        </section>

        <section id="panel-progress" class="dash-panel">
            <div class="panel-head"><h4><i class="fas fa-chart-line me-2"></i>My Progress</h4></div>
            <div class="progress-grid" id="progressChart"></div>
        </section>
    </main>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>
