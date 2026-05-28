<?php
$pageTitle = 'Create Account — CSEReviewer';
$extraCss  = ['assets/css/auth.css'];
$root      = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body class="min-h-screen flex items-center justify-center p-4 py-10">

    <div class="auth-bg"></div>

    <div class="auth-content w-full max-w-md">

        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center gap-3 mb-4">
                <img src="assets/img/logo.png" alt="CSE Reviewer Logo" class="w-11 h-11 rounded-2xl object-contain">
                <span class="text-white font-black text-2xl">CSE<span class="gradient-text">Reviewer</span></span>
            </a>
            <h1 class="text-white text-2xl font-bold mt-2">Create your account</h1>
            <p class="text-slate-400 text-sm mt-1">Start your CSE journey today — it's free</p>
        </div>

        <div class="glass-form rounded-3xl p-8">

            <div id="alert" class="alert mb-5"></div>

            <form id="registerForm" novalidate>

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
                    <div class="mt-2 flex gap-1">
                        <div class="pw-strength flex-1 bg-slate-700" id="s1"></div>
                        <div class="pw-strength flex-1 bg-slate-700" id="s2"></div>
                        <div class="pw-strength flex-1 bg-slate-700" id="s3"></div>
                        <div class="pw-strength flex-1 bg-slate-700" id="s4"></div>
                    </div>
                    <div class="field-error" id="err_pw"><i class="fas fa-circle-exclamation mr-1"></i><span></span></div>
                </div>

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

    <script src="assets/js/register.js"></script>
</body>
</html>
