<?php
require_once __DIR__ . '/php/config.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: index.html');
    exit();
}
$subjects = defaultSubjects();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Our Education Mentor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-crown me-2"></i>Admin Panel</h4>
            <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#dashboard" class="active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li><a href="#students"><i class="fas fa-users-cog me-2"></i>Manage Students</a></li>
            <li><a href="#content"><i class="fas fa-upload me-2"></i>Upload Content</a></li>
            <li><a href="#tests"><i class="fas fa-clipboard-list me-2"></i>Manage Tests</a></li>
            <li><a href="#notices"><i class="fas fa-bullhorn me-2"></i>Notice Manage</a></li>
            <li><a href="#messages"><i class="fas fa-envelope me-2"></i>Messages</a></li>
            <li><a href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div id="dashboard" class="content-section active">
            <div class="page-header"><h2><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard</h2></div>
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6"><div class="stat-card primary"><div class="stat-icon"><i class="fas fa-users"></i></div><div><h3 id="totalStudents">0</h3><p>Total Students</p></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card success"><div class="stat-icon"><i class="fas fa-video"></i></div><div><h3 id="totalVideos">0</h3><p>Total Videos</p></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card warning"><div class="stat-icon"><i class="fas fa-file-pdf"></i></div><div><h3 id="totalPdfs">0</h3><p>Total PDFs</p></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card info"><div class="stat-icon"><i class="fas fa-chart-line"></i></div><div><h3 id="avgProgress">0%</h3><p>Avg Progress</p></div></div></div>
            </div>
            <div class="row">
                <div class="col-lg-8"><div class="card"><div class="card-header"><h5><i class="fas fa-clock me-2"></i>Recent Activity</h5></div><div class="card-body" id="recentActivity"></div></div></div>
                <div class="col-lg-4"><div class="card"><div class="card-header"><h5><i class="fas fa-chart-pie me-2"></i>Content Stats</h5></div><canvas id="courseChart" height="200"></canvas></div></div>
            </div>
        </div>

        <div id="students" class="content-section">
            <div class="page-header"><h2><i class="fas fa-users-cog me-2 text-success"></i>Manage Students</h2></div>
            <div class="card">
                <div class="card-header d-flex justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">All Students</h5>
                    <input type="text" id="searchStudents" class="form-control" style="max-width:260px;" placeholder="Search by name, email, ID...">
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover" id="studentsTable">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Progress</th>
                                <th>Videos</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="content" class="content-section">
            <div class="page-header"><h2><i class="fas fa-upload me-2 text-warning"></i>Upload Content</h2></div>
            <div id="uploadChooser" class="upload-chooser">
                <div class="upload-type-card" onclick="showUploadForm('video')">
                    <i class="fas fa-video"></i>
                    <h5>Upload Video</h5>
                    <p>YouTube video for students</p>
                </div>
                <div class="upload-type-card" onclick="showUploadForm('pdf')">
                    <i class="fas fa-file-pdf"></i>
                    <h5>Upload PDF</h5>
                    <p>PDF notes for download</p>
                </div>
            </div>

            <div id="videoUploadBox" class="card upload-form-box" style="display:none;">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-video me-2"></i>Upload Video</h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="backToUploadChooser()">Back</button>
                </div>
                <div class="card-body">
                    <form id="videoForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Batch / Course Name</label>
                                <input type="text" class="form-control" name="course" placeholder="e.g. JAC Class 10 - Batch A" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <select class="form-select" name="subject" required>
                                    <option value="">Select subject</option>
                                    <?php foreach ($subjects as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Video Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">YouTube URL</label>
                                <input type="url" class="form-control" name="youtube_url" placeholder="https://youtube.com/embed/..." required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3">Upload Video</button>
                    </form>
                </div>
            </div>

            <div id="pdfUploadBox" class="card upload-form-box" style="display:none;">
                <div class="card-header bg-success text-white d-flex justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-file-pdf me-2"></i>Upload PDF</h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="backToUploadChooser()">Back</button>
                </div>
                <div class="card-body">
                    <form id="pdfForm" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Batch / Course Name</label>
                                <input type="text" class="form-control" name="course" placeholder="e.g. JAC Class 10 - Batch A" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <select class="form-select" name="subject" required>
                                    <option value="">Select subject</option>
                                    <?php foreach ($subjects as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PDF Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Select PDF File</label>
                                <input type="file" class="form-control" name="pdf_file" accept=".pdf" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-3">Upload PDF</button>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Uploaded Content List</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover" id="contentTable">
                        <thead><tr><th>Type</th><th>Batch</th><th>Subject</th><th>Title</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tests" class="content-section">
            <div class="page-header"><h2><i class="fas fa-clipboard-list me-2 text-info"></i>Manage Tests</h2></div>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">Add / Edit Test</h5></div>
                        <div class="card-body">
                            <form id="testForm">
                                <input type="hidden" name="id" id="testId">
                                <div class="mb-2"><label class="form-label">Batch / Course</label><input type="text" class="form-control" name="course" required></div>
                                <div class="mb-2"><label class="form-label">Subject</label>
                                    <select class="form-select" name="subject" required>
                                        <option value="">Select</option>
                                        <?php foreach ($subjects as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-2"><label class="form-label">Test Title</label><input type="text" class="form-control" name="title" required></div>
                                <div class="mb-2"><label class="form-label">Test Link (optional)</label><input type="url" class="form-control" name="test_url" placeholder="Google Form / Quiz URL"></div>
                                <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" id="testActive" value="1" checked><label class="form-check-label" for="testActive">Active</label></div>
                                <button type="submit" class="btn btn-primary w-100">Save Test</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">All Tests</h5></div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover" id="testsTable">
                                <thead><tr><th>Batch</th><th>Subject</th><th>Title</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="notices" class="content-section">
            <div class="page-header"><h2><i class="fas fa-bullhorn me-2 text-warning"></i>Notice Management</h2></div>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">Create Notice</h5></div>
                        <div class="card-body">
                            <form id="noticeForm">
                                <input type="hidden" name="id" id="noticeId">
                                <div class="mb-2"><label class="form-label">Title</label><input type="text" class="form-control" name="title" required></div>
                                <div class="mb-2"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="4" required></textarea></div>
                                <div class="mb-2"><label class="form-label">Show On</label>
                                    <select class="form-select" name="target" required>
                                        <option value="website">Whole Website (not on student dashboard)</option>
                                        <option value="dashboard">Student Dashboard only (not on website)</option>
                                    </select>
                                </div>
                                <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" id="noticeActive" value="1" checked><label class="form-check-label" for="noticeActive">Active</label></div>
                                <button type="submit" class="btn btn-warning w-100">Save Notice</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">All Notices</h5></div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover" id="noticesTable">
                                <thead><tr><th>Title</th><th>Target</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="messages" class="content-section">
            <div class="page-header"><h2><i class="fas fa-envelope me-2 text-danger"></i>Student Messages</h2></div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Contact Messages</h5>
                    <span class="badge bg-primary" id="totalMessages">0</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover" id="messagesTable">
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th><th>Action</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <p id="noMessages" class="text-muted mb-0" style="display:none;">No messages yet.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentModalTitle">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="studentEditForm">
                    <input type="hidden" name="id" id="editStudentId">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Student ID</label><input type="text" class="form-control" id="editStudentCode" readonly></div>
                        <div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="name" id="editName" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="editEmail" required></div>
                        <div class="col-md-6"><label class="form-label">Mobile</label><input type="text" class="form-control" name="mobile" id="editMobile"></div>
                        <div class="col-md-6"><label class="form-label">Progress %</label><input type="number" class="form-control" name="progress" id="editProgress" min="0" max="100"></div>
                        <div class="col-md-6"><label class="form-label">Videos Watched</label><input type="number" class="form-control" name="videos_watched" id="editVideos" min="0"></div>
                        <div class="col-12"><hr><h6 class="text-primary">ID Card Details (editable)</h6></div>
                        <div class="col-md-4"><label class="form-label">Blood Group</label><input type="text" class="form-control" name="blood_group" id="editBlood"></div>
                        <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="text" class="form-control" name="dob" id="editDob" placeholder="DD/MM/YYYY"></div>
                        <div class="col-md-4"><label class="form-label">Class</label><input type="text" class="form-control" name="class_name" id="editClass"></div>
                        <div class="col-md-6"><label class="form-label">Father Name</label><input type="text" class="form-control" name="father_name" id="editFather"></div>
                        <div class="col-md-6"><label class="form-label">Mother Name</label><input type="text" class="form-control" name="mother_name" id="editMother"></div>
                        <div class="col-md-6"><label class="form-label">School Name</label><input type="text" class="form-control" name="school_name" id="editSchool"></div>
                        <div class="col-md-6"><label class="form-label">Address</label><input type="text" class="form-control" name="address" id="editAddress"></div>
                        <div class="col-12"><label class="form-label">Other Details</label><textarea class="form-control" name="extra_note" id="editExtra" rows="2"></textarea></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-dark" id="viewIdCardBtn" style="display:none;">View ID Card</button>
                <button type="button" class="btn btn-success" id="downloadIdCardBtn" style="display:none;">Download ID Card</button>
                <button type="button" class="btn btn-primary" id="saveStudentBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/admin.js"></script>
</body>
</html>
