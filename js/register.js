const registerForm = document.getElementById('registerForm');
const alertMsg = document.getElementById('alertMsg');

function setAlert(msg, type) {
    alertMsg.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
}

registerForm?.addEventListener('submit', function(e) {
    e.preventDefault();
    const pass = document.getElementById('passField').value;
    const confirm = document.getElementById('confirmPass').value;
    if (pass !== confirm) {
        setAlert('Passwords do not match', 'danger');
        return;
    }

    const btn = document.getElementById('registerBtn');
    btn.disabled = true;
    btn.textContent = 'Creating Account...';

    fetch('php/register_process.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.ok) {
            setAlert('Registration successful! You can login now.', 'success');
            this.reset();
        } else {
            setAlert(resp.message || 'Registration failed', 'danger');
        }
    })
    .catch(() => setAlert('Server error', 'danger'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Create Account';
    });
});
