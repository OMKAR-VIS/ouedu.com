const menuToggle = document.getElementById('menu-toggle');
const menu = document.getElementById('menu');

if (menuToggle && menu) {
    menuToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('active');
    });

    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && !menuToggle.contains(e.target)) {
            menu.classList.remove('active');
        }
    });
}

const loginModal = document.getElementById('loginModal');
const registerModal = document.getElementById('registerModal');

function openModal() {
    if (!loginModal) return;
    if (registerModal) registerModal.classList.remove('open');
    loginModal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModalFn() {
    if (!loginModal) return;
    loginModal.classList.remove('open');
    if (!registerModal || !registerModal.classList.contains('open')) {
        document.body.style.overflow = '';
    }
}

function openRegisterModal() {
    if (!registerModal) return;
    if (loginModal) loginModal.classList.remove('open');
    registerModal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeRegisterModal() {
    if (!registerModal) return;
    registerModal.classList.remove('open');
    if (!loginModal || !loginModal.classList.contains('open')) {
        document.body.style.overflow = '';
    }
}

['openLogin', 'openLoginMobile'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            if (menu) menu.classList.remove('active');
            openModal();
        });
    }
});

const closeModal = document.getElementById('closeModal');
if (closeModal) closeModal.addEventListener('click', closeModalFn);

const closeRegister = document.getElementById('closeRegisterModal');
if (closeRegister) closeRegister.addEventListener('click', closeRegisterModal);

['openRegister', 'openRegisterHero', 'openRegisterMobile', 'openRegisterFromLogin', 'openRegisterFromFooter'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            if (menu) menu.classList.remove('active');
            openRegisterModal();
        });
    }
});

const openLoginFromRegister = document.getElementById('openLoginFromRegister');
if (openLoginFromRegister) {
    openLoginFromRegister.addEventListener('click', function (e) {
        e.preventDefault();
        closeRegisterModal();
        openModal();
    });
}

if (new URLSearchParams(window.location.search).get('register') === '1') {
    openRegisterModal();
    history.replaceState(null, '', window.location.pathname);
}

const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('loginBtn');
        if (!btn) return;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging in...';
        btn.disabled = true;

        fetch('php/login_process.php', { method: 'POST', body: new FormData(this) })
            .then(r => r.text())
            .then(resp => {
                resp = resp.trim();
                if (resp === 'admin') {
                    showAlert('✅ Admin login successful. Redirecting...', 'success');
                    setTimeout(() => { window.location.href = 'admin.php'; }, 1500);
                } else if (resp === 'student') {
                    showAlert('✅ Login successful. Redirecting...', 'success');
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
                } else {
                    showAlert('❌ Invalid email or password.', 'danger');
                    btn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
                    btn.disabled = false;
                }
            })
            .catch(() => {
                showAlert('❌ Server error. Please try again.', 'danger');
                btn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
                btn.disabled = false;
            });
    });
}

function showAlert(msg, type) {
    const box = document.getElementById('alertBox');
    if (!box) return;
    box.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert" style="border-radius:12px; min-width:280px;">
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    setTimeout(() => { box.innerHTML = ''; }, 4000);
}

const registerModalForm = document.getElementById('registerModalForm');
if (registerModalForm) {
    registerModalForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('registerModalBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registering...';
        btn.disabled = true;

        fetch('php/register_process.php', { method: 'POST', body: new FormData(this) })
            .then(r => r.json())
            .then(resp => {
                if (resp.ok) {
                    showAlert('✅ Registration successful. Please login.', 'success');
                    closeRegisterModal();
                    this.reset();
                    openModal();
                } else {
                    showAlert(`❌ ${resp.message || 'Registration failed'}`, 'danger');
                }
                btn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Register';
                btn.disabled = false;
            })
            .catch(() => {
                showAlert('❌ Server error. Please try again.', 'danger');
                btn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Register';
                btn.disabled = false;
            });
    });
}
