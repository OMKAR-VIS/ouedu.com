(function () {
    if (document.body.classList.contains('dashboard-page')) return;

    fetch('php/notices_api.php?target=website')
        .then(r => r.json())
        .then(res => {
            if (!res.ok || !res.data.length) return;
            const bar = document.createElement('div');
            bar.className = 'site-notice-bar';
            bar.innerHTML = res.data.map(n => `
                <div class="site-notice-item">
                    <strong>${escapeHtml(n.title)}:</strong> ${escapeHtml(n.message)}
                </div>
            `).join('');
            document.body.insertBefore(bar, document.body.firstChild);
        })
        .catch(() => {});

    function escapeHtml(t) {
        const d = document.createElement('div');
        d.textContent = t ?? '';
        return d.innerHTML;
    }
})();
