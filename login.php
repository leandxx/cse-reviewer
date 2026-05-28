<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In — CSEReviewer</title>
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

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
            <h1 class="text-white text-2xl font-bold mt-2">Welcome back</h1>
            <p class="text-slate-400 text-sm mt-1">Log in to continue your review</p>
        </div>

        <!-- Card -->
        <div class="glass-form rounded-3xl p-8">

            <div id="alert" class="alert mb-5"></div>

            <form id="loginForm" novalidate>
                <!-- Email -->
                <div class="mb-5">
                    <label class="text-slate-300 text-sm font-medium block mb-2">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="email" name="email" id="email"
                            class="input-field w-full pl-11 pr-4 py-3.5 rounded-xl text-sm"
                            placeholder="you@email.com" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <div class="flex justify-between mb-2">
                        <label class="text-slate-300 text-sm font-medium">Password</label>
                        <a href="#" class="text-indigo-400 text-xs hover:text-indigo-300 transition-colors">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="password" name="password" id="password"
                            class="input-field w-full pl-11 pr-12 py-3.5 rounded-xl text-sm"
                            placeholder="••••••••" required>
                        <button type="button" id="togglePw" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" id="submitBtn" class="btn-submit w-full text-white font-bold py-3.5 rounded-xl text-sm flex items-center justify-center gap-2">
                    <span id="btnText"><i class="fas fa-sign-in-alt mr-1"></i> Log In</span>
                    <span id="btnLoader" class="hidden"><i class="fas fa-spinner fa-spin mr-1"></i> Logging in...</span>
                </button>
            </form>

            <div class="divider my-6 text-slate-500 text-xs">or</div>

            <p class="text-center text-slate-400 text-sm">
                Don't have an account?
                <a href="register.php" class="text-indigo-400 font-semibold hover:text-indigo-300 transition-colors ml-1">Create one free</a>
            </p>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            <a href="index.php" class="hover:text-slate-400 transition-colors"><i class="fas fa-arrow-left mr-1"></i> Back to Home</a>
        </p>
    </div>

    <script>
        // Toggle password visibility
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

            const formData = new FormData(this);

            try {
                const res = await fetch('auth/login.php', { method: 'POST', body: formData });
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
    </script>
</body>
</html>
