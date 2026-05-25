const contactForm = document.getElementById('contactForm');

contactForm?.addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = this.querySelector('.submit-btn');
    const btnSpan = btn.querySelector('span');
    btn.disabled = true;
    if (btnSpan) btnSpan.textContent = 'Sending...';

    const formData = new FormData();
    formData.append('name', document.getElementById('name').value.trim());
    formData.append('email', document.getElementById('email').value.trim());
    formData.append('phone', document.getElementById('phone').value.trim());
    formData.append('message', document.getElementById('message').value.trim());

    fetch('php/contact_process.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(resp => {
            if (resp.ok) {
                showToast(resp.message || 'Message sent successfully!', 'success');
                this.reset();
            } else {
                showToast(resp.message || 'Failed to send message', 'danger');
            }
        })
        .catch(() => showToast('Server error. Please try again.', 'danger'))
        .finally(() => {
            btn.disabled = false;
            if (btnSpan) btnSpan.textContent = 'Send Message';
        });
});

document.querySelectorAll('.faq-question').forEach(q => {
    q.addEventListener('click', () => {
        const ans = q.nextElementSibling;
        const open = ans.classList.contains('active');
        document.querySelectorAll('.faq-answer').forEach(x => x.classList.remove('active'));
        if (!open) ans.classList.add('active');
    });
});

function showToast(msg, type) {
    let box = document.getElementById('toastBox');
    if (!box) {
        box = document.createElement('div');
        box.id = 'toastBox';
        box.style.cssText = 'position:fixed;top:80px;right:20px;z-index:99999;min-width:280px;';
        document.body.appendChild(box);
    }
    box.innerHTML = `<div class="alert alert-${type} shadow">${msg}</div>`;
    setTimeout(() => { box.innerHTML = ''; }, 4000);
}
