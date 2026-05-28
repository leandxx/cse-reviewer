document.getElementById('togglePw').addEventListener('click', function () {
    const pw = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

function showAlert(msg, type) {
    const el = document.getElementById('alert');
    el.className = `alert alert-${type}`;
    el.innerHTML = `<i class="fas fa-${type === 'error' ? 'circle-exclamation' : 'circle-check'} mr-2"></i>${msg}`;
    el.style.display = 'block';
}

document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');

    btnText.classList.add('hidden');
    btnLoader.classList.remove('hidden');
    btn.disabled = true;

    try {
        const res = await fetch('../auth/login.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();

        if (data.success) {
            showAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => window.location.href = data.redirect, 800);
        } else {
            showAlert(data.message, 'error');
            btnText.classList.remove('hidden');
            btnLoader.classList.add('hidden');
            btn.disabled = false;
        }
    } catch {
        showAlert('Something went wrong. Please try again.', 'error');
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
        btn.disabled = false;
    }
});
