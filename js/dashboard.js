let videoData = { batches: {}, subjects: [] };
let pdfData = { batches: {}, subjects: [] };
let videoState = { batch: null, subject: null };
let pdfState = { batch: null, subject: null };

async function fetchJson(url) {
    const res = await fetch(url);
    return res.json();
}

function logout() {
    if (!confirm('Logout from dashboard?')) return;
    fetch('php/logout.php').then(() => { window.location.href = 'index.html'; });
}

function esc(s) {
    const d = document.createElement('div');
    d.textContent = s ?? '';
    return d.innerHTML;
}

function showPanel(name) {
    document.querySelectorAll('.dash-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.dash-nav a').forEach(a => a.classList.remove('active'));
    document.getElementById(`panel-${name}`)?.classList.add('active');
    document.querySelector(`.dash-nav a[data-panel="${name}"]`)?.classList.add('active');
}

document.querySelectorAll('.dash-nav a').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        showPanel(link.dataset.panel);
    });
});

function toEmbedUrl(url) {
    if (!url) return '';
    if (url.includes('youtube.com/embed/')) return url;
    const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
    return m ? `https://www.youtube.com/embed/${m[1]}` : url;
}

function batchHasContent(batches, batch, type) {
    const subs = batches[batch] || {};
    return Object.keys(subs).some(sk => {
        const items = subs[sk] || [];
        return items.some(i => type === 'video' ? i.youtube_url : i.file_path);
    });
}

function renderBatchGrid(gridId, batches, type, onClick) {
    const grid = document.getElementById(gridId);
    const names = Object.keys(batches).filter(b => batchHasContent(batches, b, type));
    if (!names.length) {
        grid.innerHTML = '<div class="empty-msg">Admin ne abhi koi content upload nahi kiya. Please check later.</div>';
        return;
    }
    grid.innerHTML = names.map(b => `
        <div class="pick-card">
            <i class="fas fa-layer-group"></i>
            <h6>${esc(b)}</h6>
            <small>Click to continue</small>
        </div>
    `).join('');
    grid.querySelectorAll('.pick-card').forEach((card, i) => {
        card.addEventListener('click', () => onClick(names[i]));
    });
}

function renderSubjectGrid(gridId, batchName, batches, subjects, type, onClick) {
    const grid = document.getElementById(gridId);
    const batchSubjects = batches[batchName] || {};
    grid.innerHTML = subjects.map(sub => {
        const key = Object.keys(batchSubjects).find(k => k.toLowerCase() === sub.toLowerCase());
        const items = key ? batchSubjects[key] : [];
        const count = items.filter(i => type === 'video' ? i.youtube_url : i.file_path).length;
        return `
            <div class="pick-card subject-card">
                <i class="fas fa-book"></i>
                <h6>${esc(sub)}</h6>
                <small>${count ? count + ' available' : 'No upload yet'}</small>
            </div>
        `;
    }).join('');
    grid.querySelectorAll('.pick-card').forEach((card, i) => {
        card.addEventListener('click', () => onClick(subjects[i]));
    });
}

function findSubjectItems(batchName, subjectName, batches) {
    const batchSubjects = batches[batchName] || {};
    const key = Object.keys(batchSubjects).find(k => k.toLowerCase() === subjectName.toLowerCase());
    return key ? batchSubjects[key] : [];
}

// ——— VIDEOS ———
function resetVideoView() {
    videoState = { batch: null, subject: null };
    document.getElementById('videoBatchGrid').parentElement.style.display = 'block';
    document.getElementById('videoSubjectWrap').style.display = 'none';
    document.getElementById('videoPlayerWrap').style.display = 'none';
    document.getElementById('videoBack').style.display = 'none';
    renderBatchGrid('videoBatchGrid', videoData.batches, 'video', selectVideoBatch);
}

function selectVideoBatch(batch) {
    videoState.batch = batch;
    document.getElementById('videoSubjectWrap').style.display = 'block';
    document.getElementById('videoBack').style.display = 'inline-flex';
    renderSubjectGrid('videoSubjectGrid', batch, videoData.batches, videoData.subjects, 'video', selectVideoSubject);
}

function selectVideoSubject(subject) {
    videoState.subject = subject;
    const items = findSubjectItems(videoState.batch, subject, videoData.batches).filter(i => i.youtube_url);
    document.getElementById('videoPlayerWrap').style.display = 'block';

    const list = document.getElementById('videoList');
    const frame = document.getElementById('videoFrame');

    if (!items.length) {
        list.innerHTML = '<div class="empty-msg">Not uploaded any video for this subject.</div>';
        frame.src = '';
        return;
    }

    list.innerHTML = items.map((v, i) => `
        <div class="playlist-item ${i === 0 ? 'active' : ''}" data-url="${esc(v.youtube_url)}">
            <i class="fas fa-play-circle"></i> ${esc(v.title)}
        </div>
    `).join('');
    frame.src = toEmbedUrl(items[0].youtube_url);

    list.querySelectorAll('.playlist-item').forEach(el => {
        el.addEventListener('click', () => {
            list.querySelectorAll('.playlist-item').forEach(x => x.classList.remove('active'));
            el.classList.add('active');
            frame.src = toEmbedUrl(el.dataset.url);
        });
    });
}

document.getElementById('videoBack')?.addEventListener('click', () => {
    if (videoState.subject) {
        videoState.subject = null;
        document.getElementById('videoPlayerWrap').style.display = 'none';
        if (!videoState.batch) resetVideoView();
    } else if (videoState.batch) {
        resetVideoView();
    }
});

// ——— PDFs ———
function resetPdfView() {
    pdfState = { batch: null, subject: null };
    document.getElementById('pdfBatchGrid').parentElement.style.display = 'block';
    document.getElementById('pdfSubjectWrap').style.display = 'none';
    document.getElementById('pdfDownloadWrap').style.display = 'none';
    document.getElementById('pdfBack').style.display = 'none';
    renderBatchGrid('pdfBatchGrid', pdfData.batches, 'pdf', selectPdfBatch);
}

function selectPdfBatch(batch) {
    pdfState.batch = batch;
    document.getElementById('pdfSubjectWrap').style.display = 'block';
    document.getElementById('pdfBack').style.display = 'inline-flex';
    renderSubjectGrid('pdfSubjectGrid', batch, pdfData.batches, pdfData.subjects, 'pdf', selectPdfSubject);
}

function selectPdfSubject(subject) {
    pdfState.subject = subject;
    document.getElementById('pdfDownloadWrap').style.display = 'block';
    const wrap = document.getElementById('pdfList');
    const items = findSubjectItems(pdfState.batch, subject, pdfData.batches).filter(i => i.file_path);

    if (!items.length) {
        wrap.innerHTML = `
            <div class="not-uploaded">
                <i class="fas fa-file-circle-xmark"></i>
                <h6>Not uploaded any PDF</h6>
                <p>No PDF available for <strong>${esc(subject)}</strong> in batch <strong>${esc(pdfState.batch)}</strong>.</p>
            </div>`;
        return;
    }

    wrap.innerHTML = items.map(p => `
        <a class="pdf-item" href="${esc(p.file_path)}" target="_blank" download>
            <i class="fas fa-file-pdf"></i>
            <div><strong>${esc(p.title)}</strong><small>${esc(subject)}</small></div>
            <span class="dl-btn">Download</span>
        </a>
    `).join('');
}

document.getElementById('pdfBack')?.addEventListener('click', () => {
    if (pdfState.subject) {
        pdfState.subject = null;
        document.getElementById('pdfDownloadWrap').style.display = 'none';
    } else if (pdfState.batch) {
        resetPdfView();
    }
});

async function loadContent() {
    const [vRes, pRes] = await Promise.all([
        fetchJson('php/dashboard_api.php?action=content&type=video'),
        fetchJson('php/dashboard_api.php?action=content&type=pdf')
    ]);
    if (vRes.ok) {
        videoData = vRes.data;
        resetVideoView();
    }
    if (pRes.ok) {
        pdfData = pRes.data;
        resetPdfView();
    }
}

async function loadProgress() {
    const res = await fetchJson('php/dashboard_api.php?action=progress');
    if (!res.ok) return;
    const d = res.data;
    document.getElementById('progressChart').innerHTML = `
        <div class="prog-card">
            <i class="fas fa-percent"></i>
            <h3>${d.progress}%</h3>
            <p>Overall Progress</p>
            <div class="bar"><span style="width:${d.progress}%"></span></div>
        </div>
        <div class="prog-card green">
            <i class="fas fa-play-circle"></i>
            <h3>${d.videos_watched}</h3>
            <p>Videos Watched</p>
        </div>
        <div class="prog-card blue">
            <i class="fas fa-video"></i>
            <h3>${d.total_videos}</h3>
            <p>Total Videos Available</p>
        </div>
        <div class="prog-card red">
            <i class="fas fa-file-pdf"></i>
            <h3>${d.total_pdfs}</h3>
            <p>Total PDFs Available</p>
        </div>
    `;
}

async function loadNotices() {
    const res = await fetchJson('php/dashboard_api.php?action=notices');
    const box = document.getElementById('dashNotices');
    if (!box || !res.ok || !res.data.length) return;
    box.innerHTML = res.data.map(n => `
        <div class="notice-item">
            <strong><i class="fas fa-bullhorn me-1"></i>${esc(n.title)}</strong>
            <p>${esc(n.message)}</p>
        </div>
    `).join('');
    box.style.display = 'block';
}

window.logout = logout;
loadContent();
loadProgress();
loadNotices();
