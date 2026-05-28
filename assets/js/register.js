function togglePassword(btnId, inputId) {
    document.getElementById(btnId).addEventListener('click', function () {
        const pw = document.getElementById(inputId);
        const icon = this.querySelector('i');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            pw.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
}
togglePassword('togglePw1', 'password');
togglePassword('togglePw2', 'confirm_password');

document.getElementById('password').addEventListener('input', function () {
    const val = this.value;
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['', '#ef4444', '#f97316', '#eab308', '#22c55e'];
    for (let i = 1; i <= 4; i++) {
        document.getElementById('s' + i).style.background = i <= score ? colors[score] : '#334155';
    }
});

function showFieldError(id, msg) {
    const el = document.getElementById(id);
    el.querySelector('span').textContent = msg;
    el.style.display = 'block';
    const input = el.previousElementSibling.querySelector('input') || el.previousElementSibling;
    if (input.tagName === 'INPUT') input.classList.add('error');
}

function clearErrors() {
    document.querySelectorAll('.field-error').forEach(e => e.style.display = 'none');
    document.querySelectorAll('.input-field').forEach(e => e.classList.remove('error'));
}

function showAlert(msg, type) {
    const el = document.getElementById('alert');
    el.className = `alert alert-${type}`;
    el.innerHTML = `<i class="fas fa-${type === 'error' ? 'circle-exclamation' : 'circle-check'} mr-2"></i>${msg}`;
    el.style.display = 'block';
}

document.getElementById('registerForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    clearErrors();

    const full_name = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    let valid = true;

    if (!full_name) { showFieldError('err_name', 'Full name is required.'); valid = false; }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showFieldError('err_email', 'Enter a valid email.'); valid = false; }
    if (password.length < 8) { showFieldError('err_pw', 'Password must be at least 8 characters.'); valid = false; }
    if (password !== confirm) { showFieldError('err_confirm', 'Passwords do not match.'); valid = false; }
    if (!valid) return;

    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');

    btnText.classList.add('hidden');
    btnLoader.classList.remove('hidden');
    btn.disabled = true;

    try {
        const res = await fetch('auth/register.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();

        if (data.success) {
            showAlert(data.message, 'success');
            this.reset();
            document.querySelectorAll('.pw-strength').forEach(s => s.style.background = '#334155');
            setTimeout(() => window.location.href = 'login.php', 1500);
        } else {
            showAlert(data.message, 'error');
        }
    } catch {
        showAlert('Something went wrong. Please try again.', 'error');
    } finally {
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
        btn.disabled = false;
    }
});
