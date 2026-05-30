<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
$pageTitle = 'Practice — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css', 'assets/css/practice.css'];
$root      = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-slate-950 min-h-screen">

    <nav class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="../assets/img/logo.png" alt="CSE Reviewer Logo" class="w-9 h-9 rounded-xl object-contain">
            <span class="text-white font-bold text-lg">CSE<span class="gradient-text">Reviewer</span></span>
        </div>
        <div class="flex items-center gap-4">
            <a href="dashboard.php" class="text-slate-400 hover:text-white text-sm transition-colors flex items-center gap-1">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <span class="text-slate-400 text-sm">Hello, <span class="text-white font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span></span>
            <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <?php include '../includes/left-sidebar.php'; ?>

    <div class="dashboard-main">
        <div class="max-w-2xl mx-auto px-6 py-12">

            <!-- Subject Selection -->
            <div id="subjectScreen">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-black text-white mb-3">Practice <span class="gradient-text">Mode</span></h1>
                    <p class="text-slate-400">Choose a subject and number of questions to begin.</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <?php
                    $subjects = [
                        ['verbal',     'fas fa-comment-dots', 'from-indigo-600 to-purple-600', 'Verbal Ability'],
                        ['numerical',  'fas fa-calculator',   'from-blue-500 to-cyan-500',     'Numerical Ability'],
                        ['analytical', 'fas fa-brain',        'from-purple-500 to-pink-500',   'Analytical Ability'],
                        ['general',    'fas fa-globe',        'from-emerald-500 to-teal-500',  'General Info'],
                    ];
                    foreach ($subjects as $s): ?>
                    <button class="subject-card glass-card rounded-2xl p-6 text-center hover:scale-105 transition-all border-2 border-transparent"
                            data-subject="<?= $s[0] ?>">
                        <div class="w-12 h-12 bg-gradient-to-br <?= $s[2] ?> rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="<?= $s[0] ?> text-white"></i>
                        </div>
                        <div class="text-white font-semibold text-sm"><?= $s[3] ?></div>
                    </button>
                    <?php endforeach; ?>
                </div>

                <div class="glass-card rounded-2xl p-6 mb-6">
                    <label class="text-slate-300 text-sm font-medium block mb-3">Number of Questions</label>
                    <div class="flex gap-3">
                        <?php foreach ([5, 10, 20, 30] as $n): ?>
                        <button class="qty-btn flex-1 py-2 rounded-xl text-sm font-semibold border border-slate-700 text-slate-400 hover:border-indigo-500 hover:text-white transition-all"
                                data-qty="<?= $n ?>"><?= $n ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button id="startBtn" class="btn-primary w-full text-white font-bold py-4 rounded-2xl text-base opacity-50 cursor-not-allowed" disabled>
                    <i class="fas fa-play mr-2"></i> Start Practice
                </button>
            </div>

            <!-- Quiz Screen -->
            <div id="quizScreen" class="hidden">
                <!-- Progress -->
                <div class="flex items-center justify-between mb-4">
                    <span class="text-slate-400 text-sm" id="qProgress">Question 1 of 10</span>
                    <span class="text-slate-400 text-sm" id="qSubjectLabel"></span>
                </div>
                <div class="h-2 bg-slate-800 rounded-full mb-8 overflow-hidden">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500" style="width:0%"></div>
                </div>

                <!-- Question card -->
                <div class="glass-card rounded-2xl p-8 mb-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span id="qDifficulty" class="text-xs font-semibold px-2 py-1 rounded-full"></span>
                    </div>
                    <p id="qText" class="text-white text-lg font-medium leading-relaxed mb-6"></p>

                    <!-- Hint -->
                    <div id="hintWrap" class="mb-4 hidden">
                        <button id="hintBtn" class="flex items-center gap-2 text-yellow-400 text-sm font-medium hover:text-yellow-300 transition-colors">
                            <i class="fas fa-lightbulb"></i> Show Hint
                        </button>
                        <div id="hintText" class="hidden mt-2 bg-yellow-500/10 border border-yellow-500/20 rounded-xl px-4 py-3 text-yellow-300 text-sm"></div>
                    </div>

                    <!-- Choices -->
                    <div id="choicesWrap" class="space-y-3"></div>
                </div>

                <!-- Explanation (shown after wrong answer) -->
                <div id="explanationBox" class="hidden glass-card rounded-2xl p-6 mb-4 border border-red-500/20">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-info-circle text-blue-400"></i>
                        <span class="text-blue-400 font-semibold text-sm">Explanation</span>
                    </div>
                    <p id="explanationText" class="text-slate-300 text-sm leading-relaxed"></p>
                </div>

                <button id="nextBtn" class="btn-primary w-full text-white font-bold py-4 rounded-2xl text-base hidden">
                    Next Question <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>

            <!-- Results Screen -->
            <div id="resultsScreen" class="hidden text-center">
                <div class="glass-card rounded-3xl p-10 mb-6">
                    <div id="resultIcon" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl"></div>
                    <h2 class="text-3xl font-black text-white mb-2">Quiz Complete!</h2>
                    <p id="resultSubject" class="text-slate-400 mb-6"></p>
                    <div class="text-6xl font-black gradient-text mb-2" id="resultScore"></div>
                    <div class="text-slate-400 text-sm mb-8" id="resultDetail"></div>
                    <div class="flex gap-4 justify-center">
                        <button id="retryBtn" class="btn-primary text-white font-bold px-8 py-3 rounded-2xl">
                            <i class="fas fa-redo mr-2"></i> Try Again
                        </button>
                        <a href="dashboard.php" class="glass-card text-white font-semibold px-8 py-3 rounded-2xl flex items-center hover:bg-white/10 transition-all">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>const ROOT = '../';</script>
    <script src="../assets/js/left-sidebar.js"></script>
    <script src="../assets/js/practice.js"></script>
</body>
</html>
