const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('anim-visible');
            if (entry.target.classList.contains('stat-item')) {
                animateCounter(entry.target.querySelector('h3'));
            }
        }
    });
}, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.anim-fade-up, .anim-fade-left, .anim-fade-right, .anim-scale, .stat-item, .teacher-card').forEach(el => {
    observer.observe(el);
});

function animateCounter(el) {
    if (!el || el.dataset.done) return;
    const target = parseInt(el.textContent.replace(/\D/g, ''), 10);
    if (!target) return;
    el.dataset.done = '1';
    const suffix = el.textContent.replace(/[0-9]/g, '');
    let current = 0;
    const step = Math.ceil(target / 40);
    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        el.textContent = current + suffix;
    }, 35);
}
