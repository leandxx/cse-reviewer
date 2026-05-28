<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — CSEReviewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }

        .auth-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
        }
        .auth-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('assets/img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            filter: brightness(0.25) saturate(1.3);
        }
        .auth-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(15, 23, 42, 0.9) 0%,
                rgba(30, 58, 138, 0.8) 50%,
                rgba(109, 40, 217, 0.7) 100%
            );
        }

        .auth-content { position: relative; z-index: 1; }

        .glass-form {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-field {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(129, 140, 248, 0.6);
            background: rgba(255, 255, 255, 0.09);
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.15);
        }
        .input-field::placeholder { color: rgba(148, 163, 184, 0.6); }
        .input-field.error { border-color: rgba(239,68,68,0.5); }

        .btn-submit {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 8px 30px rgba(79, 70, 229, 0.6);
        }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

        .gradient-text {
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .alert { border-radius: 12px; padding: 12px 16px; font-size: 14px; display: none; }
        .alert-error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .alert-success { background: rgba(74,222,128,0.15); border: 1px solid rgba(74,222,128,0.3); color: #86efac; }

        .divider { display: flex; align-items: center; gap: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.1); }

        .pw-strength { height: 4px; border-radius: 2px; transition: all 0.3s; }

        .field-error { color: #fca5a5; font-size: 12px; margin-top: 4px; display: none; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 py-10">

    <div class="auth-bg"></div>

    <div class="auth-content w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-white"></i>
                </div>
                <span class="text-white font-black text-2xl">CSE<span class="gradient-text">Reviewer</span></span>
            </a>
            <h1 class="text-white text-2xl font-bold mt-2">Create your account</h1>
            <p class="text-slate-400 text-sm mt-1">Start your CSE journey today — it's free</p>
        </div>

        <!-- Card -->
        <div class="glass-form rounded-3xl p-8">

            <div id="alert" class="alert mb-5"></div>

            <form id="registerForm" novalidate>

                <!-- Full Name -->
                <div class="mb-4">
                    <label class="text-slate-300 text-sm font-medium block mb-2">Full Name</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="text" name="full_name" id="full_name"
                            class="input-field w-full pl-11 pr-4 py-3.5 rounded-xl text-sm"
                            placeholder="Juan dela Cruz" required>
                    </div>
                    <div class="field-error" id="err_name"><i class="fas fa-circle-exclamation mr-1"></i><span></span></div>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label class="text-slate-300 text-sm font-medium block mb-2">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="email" name="email" id="email"
                            class="input-field w-full pl-11 pr-4 py-3.5 rounded-xl text-sm"
                            placeholder="you@email.com" required>
                    </div>
                    <div class="field-error" id="err_email"><i class="fas fa-circle-exclamation mr-1"></i><span></span></div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="text-slate-300 text-sm font-medium block mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="password" name="password" id="password"
                            class="input-field w-full pl-11 pr-12 py-3.5 rounded-xl text-sm"
                            placeholder="Min. 8 characters" required>
                        <button type="button" id="togglePw1" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    <!-- Strength bar -->
                    <div class="mt-2 flex gap-1">
                        <div class="pw-strength flex-1 bg-slate-700" id="s1"></div>
                        <div class="pw-strength flex-1 bg-slate-700" id="s2"></div>
                        <div class="pw-strength flex-1 bg-slate-700" id="s3"></div>
                        <div class="pw-strength flex-1 bg-slate-700" id="s4"></div>
                    </div>
                    <div class="field-error" id="err_pw"><i class="fas fa-circle-exclamation mr-1"></i><span></span></div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label class="text-slate-300 text-sm font-medium block mb-2">Confirm Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="password" name="confirm_password" id="confirm_password"
                            class="input-field w-full pl-11 pr-12 py-3.5 rounded-xl text-sm"
                            placeholder="Re-enter password" required>
                        <button type="button" id="togglePw2" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    <div class="field-error" id="err_confirm"><i class="fas fa-circle-exclamation mr-1"></i><span></span></div>
                </div>

                <!-- Submit -->
                <button type="submit" id="submitBtn" class="btn-submit w-full text-white font-bold py-3.5 rounded-xl text-sm flex items-center justify-center gap-2">
                    <span id="btnText"><i class="fas fa-user-plus mr-1"></i> Create Account</span>
                    <span id="btnLoader" class="hidden"><i class="fas fa-spinner fa-spin mr-1"></i> Creating account...</span>
                </button>
            </form>

            <div class="divider my-6 text-slate-500 text-xs">or</div>

            <p class="text-center text-slate-400 text-sm">
                Already have an account?
                <a href="login.php" class="text-indigo-400 font-semibold hover:text-indigo-300 transition-colors ml-1">Log in</a>
            </p>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            <a href="index.php" class="hover:text-slate-400 transition-colors"><i class="fas fa-arrow-left mr-1"></i> Back to Home</a>
        </p>
    </div>

    <script>
        // Toggle password visibility
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

        // Password strength
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

            const formData = new FormData(this);

            try {
                const res = await fetch('auth/register.php', { method: 'POST', body: formData });
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
    </script>
</body>
</html>
