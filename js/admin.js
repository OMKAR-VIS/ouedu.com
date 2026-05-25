let studentModal;
let chartInstance;
let currentStudentId = 0;

function showSection(hash) {
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
    const id = hash.replace('#', '') || 'dashboard';
    document.getElementById(id)?.classList.add('active');
    document.querySelector(`.sidebar-menu a[href="#${id}"]`)?.classList.add('active');
}

document.querySelectorAll('.sidebar-menu a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => { e.preventDefault(); showSection(link.getAttribute('href')); });
});

async function fetchJson(url, options) {
    const res = await fetch(url, options);
    return res.json();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function showUploadForm(type) {
    document.getElementById('uploadChooser').style.display = 'none';
    document.getElementById('videoUploadBox').style.display = type === 'video' ? 'block' : 'none';
    document.getElementById('pdfUploadBox').style.display = type === 'pdf' ? 'block' : 'none';
}

function backToUploadChooser() {
    document.getElementById('uploadChooser').style.display = 'grid';
    document.getElementById('videoUploadBox').style.display = 'none';
    document.getElementById('pdfUploadBox').style.display = 'none';
}

window.showUploadForm = showUploadForm;
window.backToUploadChooser = backToUploadChooser;

async function loadSummary() {
    const res = await fetchJson('php/admin_api.php?action=summary');
    if (!res.ok) return;
    document.getElementById('totalStudents').textContent = res.data.totalStudents;
    document.getElementById('totalVideos').textContent = res.data.totalVideos;
    document.getElementById('totalPdfs').textContent = res.data.totalPdfs;
    document.getElementById('avgProgress').textContent = `${res.data.avgProgress}%`;
    document.getElementById('recentActivity').innerHTML = `
        <p class="mb-2">Students: ${res.data.totalStudents}</p>
        <p class="mb-2">Videos: ${res.data.totalVideos}</p>
        <p class="mb-2">PDFs: ${res.data.totalPdfs}</p>
        <p class="mb-0">Messages: ${res.data.totalMessages ?? 0}</p>`;
    const ctx = document.getElementById('courseChart');
    if (ctx && window.Chart) {
        if (chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: { labels: ['Videos', 'PDFs'], datasets: [{ data: [res.data.totalVideos, res.data.totalPdfs], backgroundColor: ['#2563eb', '#16a34a'] }] }
        });
    }
}

async function loadContents() {
    const res = await fetchJson('php/admin_api.php?action=list_contents');
    if (!res.ok) return;
    const tbody = document.querySelector('#contentTable tbody');
    if (!res.data.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-muted">No content uploaded yet.</td></tr>';
        return;
    }
    tbody.innerHTML = res.data.map(c => `
        <tr>
            <td><span class="badge ${c.content_type === 'video' ? 'bg-primary' : 'bg-success'}">${c.content_type}</span></td>
            <td>${escapeHtml(c.course)}</td>
            <td>${escapeHtml(c.subject || '-')}</td>
            <td>${escapeHtml(c.title)}</td>
            <td>${new Date(c.created_at).toLocaleDateString()}</td>
            <td><button class="btn btn-sm btn-outline-danger" onclick="deleteContent(${c.id})">Delete</button></td>
        </tr>
    `).join('');
}

async function deleteContent(id) {
    if (!confirm('Delete this content?')) return;
    const fd = new FormData();
    fd.append('action', 'delete_content');
    fd.append('id', id);
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) { loadContents(); loadSummary(); }
}

async function loadStudents() {
    const res = await fetchJson('php/admin_api.php?action=students');
    if (!res.ok) return;
    document.querySelector('#studentsTable tbody').innerHTML = res.data.map(st => `
        <tr>
            <td><span class="badge bg-dark">${escapeHtml(st.student_id || '-')}</span></td>
            <td>${escapeHtml(st.name)}</td>
            <td>${escapeHtml(st.email)}</td>
            <td>${escapeHtml(st.mobile || '-')}</td>
            <td>${st.progress}%</td>
            <td>${st.videos_watched}</td>
            <td>${new Date(st.created_at).toLocaleDateString()}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="viewStudent(${st.id}, false)">View</button>
                <button class="btn btn-sm btn-outline-success" onclick="viewStudent(${st.id}, true)">Edit</button>
                <button class="btn btn-sm btn-outline-dark" onclick="openIdCard(${st.id})">ID Card</button>
            </td>
        </tr>
    `).join('');
}

function openIdCard(id) {
    window.open(`php/id_card.php?user_id=${id}`, '_blank');
}

async function viewStudent(id, editable) {
    const res = await fetchJson(`php/admin_api.php?action=get_student&id=${id}`);
    if (!res.ok) return alert(res.message || 'Not found');
    const s = res.data;
    currentStudentId = s.id;

    document.getElementById('studentModalTitle').textContent = editable ? 'Edit Student & ID Card' : 'View Student';
    document.getElementById('editStudentId').value = s.id;
    document.getElementById('editStudentCode').value = s.student_id || '';
    document.getElementById('editName').value = s.name;
    document.getElementById('editEmail').value = s.email;
    document.getElementById('editMobile').value = s.mobile || '';
    document.getElementById('editProgress').value = s.progress;
    document.getElementById('editVideos').value = s.videos_watched;
    document.getElementById('editBlood').value = s.blood_group || '';
    document.getElementById('editDob').value = s.dob || '';
    document.getElementById('editClass').value = s.class_name || 'JAC Class 10';
    document.getElementById('editFather').value = s.father_name || '';
    document.getElementById('editMother').value = s.mother_name || '';
    document.getElementById('editSchool').value = s.school_name || 'Our Education Mentor';
    document.getElementById('editAddress').value = s.address || '';
    document.getElementById('editExtra').value = s.extra_note || '';

    const fields = ['editName','editEmail','editMobile','editProgress','editVideos','editBlood','editDob','editClass','editFather','editMother','editSchool','editAddress','editExtra'];
    fields.forEach(fid => { document.getElementById(fid).readOnly = !editable; });

    document.getElementById('saveStudentBtn').style.display = editable ? 'inline-block' : 'none';
    document.getElementById('viewIdCardBtn').style.display = 'inline-block';
    document.getElementById('downloadIdCardBtn').style.display = 'inline-block';
    studentModal.show();
}

document.getElementById('viewIdCardBtn')?.addEventListener('click', () => openIdCard(currentStudentId));
document.getElementById('downloadIdCardBtn')?.addEventListener('click', () => openIdCard(currentStudentId));

document.getElementById('saveStudentBtn')?.addEventListener('click', async () => {
    const form = document.getElementById('studentEditForm');
    const fd = new FormData(form);
    fd.append('action', 'update_student');
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) { studentModal.hide(); loadStudents(); loadSummary(); }
});

document.getElementById('videoForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action', 'upload_video');
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) { this.reset(); loadSummary(); loadContents(); backToUploadChooser(); }
});

document.getElementById('pdfForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action', 'upload_pdf');
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) { this.reset(); loadSummary(); loadContents(); backToUploadChooser(); }
});

document.getElementById('searchStudents')?.addEventListener('input', function () {
    const v = this.value.toLowerCase();
    document.querySelectorAll('#studentsTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(v) ? '' : 'none';
    });
});

async function loadMessages() {
    const res = await fetchJson('php/admin_api.php?action=contacts');
    if (!res.ok) return;
    const tbody = document.querySelector('#messagesTable tbody');
    const noMsg = document.getElementById('noMessages');
    const rows = res.data || [];
    document.getElementById('totalMessages').textContent = rows.length;
    if (!rows.length) { tbody.innerHTML = ''; noMsg.style.display = 'block'; return; }
    noMsg.style.display = 'none';
    tbody.innerHTML = rows.map(msg => `
        <tr>
            <td>${msg.id}</td><td>${escapeHtml(msg.name)}</td><td>${escapeHtml(msg.email)}</td>
            <td>${escapeHtml(msg.phone || '-')}</td>
            <td style="max-width:200px;white-space:pre-wrap;">${escapeHtml(msg.message)}</td>
            <td>${new Date(msg.created_at).toLocaleString()}</td>
            <td><button class="btn btn-sm btn-outline-danger" onclick="deleteMessage(${msg.id})">Delete</button></td>
        </tr>
    `).join('');
}

async function deleteMessage(id) {
    if (!confirm('Delete?')) return;
    const fd = new FormData();
    fd.append('action', 'delete_contact');
    fd.append('id', id);
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) { loadMessages(); loadSummary(); }
}

async function loadNotices() {
    const res = await fetchJson('php/admin_api.php?action=notices');
    if (!res.ok) return;
    document.querySelector('#noticesTable tbody').innerHTML = res.data.map(n => `
        <tr>
            <td>${escapeHtml(n.title)}</td>
            <td><span class="badge ${n.target === 'website' ? 'bg-info' : 'bg-warning'}">${n.target}</span></td>
            <td>${n.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Off</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick='editNotice(${JSON.stringify(n)})'>Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotice(${n.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

function editNotice(n) {
    document.getElementById('noticeId').value = n.id;
    document.querySelector('#noticeForm [name=title]').value = n.title;
    document.querySelector('#noticeForm [name=message]').value = n.message;
    document.querySelector('#noticeForm [name=target]').value = n.target;
    document.getElementById('noticeActive').checked = n.is_active == 1;
}

document.getElementById('noticeForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action', 'save_notice');
    fd.set('is_active', document.getElementById('noticeActive').checked ? '1' : '0');
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) { this.reset(); document.getElementById('noticeId').value = ''; loadNotices(); }
});

async function deleteNotice(id) {
    if (!confirm('Delete notice?')) return;
    const fd = new FormData();
    fd.append('action', 'delete_notice');
    fd.append('id', id);
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) loadNotices();
}

async function loadTests() {
    const res = await fetchJson('php/admin_api.php?action=tests');
    if (!res.ok) return;
    document.querySelector('#testsTable tbody').innerHTML = res.data.map(t => `
        <tr>
            <td>${escapeHtml(t.course)}</td>
            <td>${escapeHtml(t.subject)}</td>
            <td>${escapeHtml(t.title)}</td>
            <td>${t.is_active == 1 ? 'Active' : 'Off'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick='editTest(${JSON.stringify(t)})'>Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTest(${t.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

function editTest(t) {
    document.getElementById('testId').value = t.id;
    document.querySelector('#testForm [name=course]').value = t.course;
    document.querySelector('#testForm [name=subject]').value = t.subject;
    document.querySelector('#testForm [name=title]').value = t.title;
    document.querySelector('#testForm [name=test_url]').value = t.test_url || '';
    document.getElementById('testActive').checked = t.is_active == 1;
}

document.getElementById('testForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action', 'save_test');
    fd.set('is_active', document.getElementById('testActive').checked ? '1' : '0');
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) { this.reset(); document.getElementById('testId').value = ''; loadTests(); }
});

async function deleteTest(id) {
    if (!confirm('Delete test?')) return;
    const fd = new FormData();
    fd.append('action', 'delete_test');
    fd.append('id', id);
    const res = await fetchJson('php/admin_api.php', { method: 'POST', body: fd });
    alert(res.message || 'Done');
    if (res.ok) loadTests();
}

function logout() {
    fetch('php/logout.php').then(() => { window.location.href = 'index.html'; });
}

document.addEventListener('DOMContentLoaded', () => {
    studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
    loadSummary();
    loadStudents();
    loadMessages();
    loadContents();
    loadNotices();
    loadTests();
});

window.logout = logout;
window.viewStudent = viewStudent;
window.openIdCard = openIdCard;
window.deleteMessage = deleteMessage;
window.deleteContent = deleteContent;
window.editNotice = editNotice;
window.deleteNotice = deleteNotice;
window.editTest = editTest;
window.deleteTest = deleteTest;
