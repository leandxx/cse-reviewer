<?php
ini_set('session.cookie_path', '/');
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
$pageTitle = 'Mock Exam — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css', 'assets/css/practice.css', 'assets/css/mock.css'];
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
            <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <?php include '../includes/left-sidebar.php'; ?>

    <div class="dashboard-main">
        <div class="max-w-2xl mx-auto px-6 py-12">

            <!-- Start Screen -->
            <div id="startScreen">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-black text-white mb-3">Mock <span class="gradient-text">Exam</span></h1>
                    <p class="text-slate-400">Simulate the real Civil Service Exam experience.</p>
                </div>

                <div class="glass-card rounded-2xl p-8 mb-6 space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-list-ol text-indigo-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold">100 Questions</div>
                            <div class="text-slate-500 text-sm">Mixed from all subject areas</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-purple-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold">2 Hours Time Limit</div>
                            <div class="text-slate-500 text-sm">Auto-submits when time runs out</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lightbulb text-emerald-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold">Hints & Explanations</div>
                            <div class="text-slate-500 text-sm">Available after each answer</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-flag text-yellow-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold">Flag & Review</div>
                            <div class="text-slate-500 text-sm">Mark questions to revisit before submitting</div>
                        </div>
                    </div>
                </div>

                <button id="startExamBtn" class="btn-primary w-full text-white font-bold py-4 rounded-2xl text-base">
                    <i class="fas fa-play mr-2"></i> Start Mock Exam
                </button>
            </div>

            <!-- Exam Screen -->
            <div id="examScreen" class="hidden">
                <!-- Top bar: timer + progress -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-sm" id="examProgress">1 / 100</span>
                        <span id="flaggedBadge" class="hidden text-xs text-yellow-400 bg-yellow-500/10 px-2 py-0.5 rounded-full">
                            <i class="fas fa-flag mr-1"></i><span id="flagCount">0</span> flagged
                        </span>
                    </div>
                    <div id="timerBox" class="flex items-center gap-2 bg-slate-800 px-4 py-2 rounded-xl">
                        <i class="fas fa-clock text-indigo-400 text-sm"></i>
                        <span id="timerDisplay" class="text-white font-bold text-sm font-mono">2:00:00</span>
                    </div>
                </div>

                <!-- Progress bar -->
                <div class="h-1.5 bg-slate-800 rounded-full mb-6 overflow-hidden">
                    <div id="examProgressBar" class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-300" style="width:0%"></div>
                </div>

                <!-- Question card -->
                <div class="glass-card rounded-2xl p-8 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span id="examQSubject" class="text-xs font-semibold px-2 py-1 rounded-full"></span>
                            <span id="examQDiff" class="text-xs font-semibold"></span>
                        </div>
                        <button id="flagBtn" class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-yellow-400 transition-colors px-3 py-1.5 rounded-lg hover:bg-yellow-500/10">
                            <i class="fas fa-flag"></i> Flag
                        </button>
                    </div>

                    <p id="examQText" class="text-white text-lg font-medium leading-relaxed mb-6"></p>

                    <!-- Hint -->
                    <div id="examHintWrap" class="mb-4 hidden">
                        <button id="examHintBtn" class="flex items-center gap-2 text-yellow-400 text-sm font-medium hover:text-yellow-300 transition-colors">
                            <i class="fas fa-lightbulb"></i> Show Hint
                        </button>
                        <div id="examHintText" class="hidden mt-2 bg-yellow-500/10 border border-yellow-500/20 rounded-xl px-4 py-3 text-yellow-300 text-sm"></div>
                    </div>

                    <div id="examChoicesWrap" class="space-y-3"></div>
                </div>

                <!-- Explanation -->
                <div id="examExplanationBox" class="hidden glass-card rounded-2xl p-6 mb-4 border border-blue-500/20">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-info-circle text-blue-400"></i>
                        <span class="text-blue-400 font-semibold text-sm">Explanation</span>
                    </div>
                    <p id="examExplanationText" class="text-slate-300 text-sm leading-relaxed"></p>
                </div>

                <!-- Nav buttons -->
                <div class="flex gap-3">
                    <button id="prevBtn" class="glass-card text-slate-400 font-semibold px-6 py-3 rounded-2xl hover:text-white hover:bg-white/10 transition-all hidden">
                        <i class="fas fa-arrow-left mr-2"></i> Prev
                    </button>
                    <button id="examNextBtn" class="btn-primary flex-1 text-white font-bold py-3 rounded-2xl hidden">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <button id="submitExamBtn" class="btn-primary flex-1 text-white font-bold py-3 rounded-2xl hidden bg-gradient-to-r from-emerald-500 to-teal-500">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Exam
                    </button>
                </div>

                <!-- Question navigator -->
                <div class="glass-card rounded-2xl p-5 mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-slate-400 text-xs font-semibold">Question Navigator</span>
                        <div class="flex items-center gap-3 text-xs text-slate-500">
                            <span><span class="inline-block w-3 h-3 rounded bg-emerald-500 mr-1"></span>Answered</span>
                            <span><span class="inline-block w-3 h-3 rounded bg-yellow-500 mr-1"></span>Flagged</span>
                            <span><span class="inline-block w-3 h-3 rounded bg-slate-700 mr-1"></span>Unanswered</span>
                        </div>
                    </div>
                    <div id="qNavGrid" class="grid grid-cols-10 gap-1.5"></div>
                </div>
            </div>

            <!-- Results Screen -->
            <div id="resultsScreen" class="hidden">
                <div class="glass-card rounded-3xl p-10 mb-6 text-center">
                    <div id="mockResultIcon" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl"></div>
                    <h2 class="text-3xl font-black text-white mb-2">Exam Complete!</h2>
                    <p class="text-slate-400 mb-2">Mock Civil Service Exam</p>
                    <span id="mockXpEarned" class="hidden inline-block text-yellow-400 font-bold text-sm bg-yellow-500/10 px-3 py-1 rounded-full mb-6">
                        <i class="fas fa-star mr-1"></i>
                    </span>

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-slate-800/50 rounded-2xl p-4">
                            <div class="text-3xl font-black gradient-text" id="mockScore"></div>
                            <div class="text-slate-500 text-xs mt-1">Score</div>
                        </div>
                        <div class="bg-slate-800/50 rounded-2xl p-4">
                            <div class="text-3xl font-black text-emerald-400" id="mockCorrect"></div>
                            <div class="text-slate-500 text-xs mt-1">Correct</div>
                        </div>
                        <div class="bg-slate-800/50 rounded-2xl p-4">
                            <div class="text-3xl font-black text-red-400" id="mockWrong"></div>
                            <div class="text-slate-500 text-xs mt-1">Wrong</div>
                        </div>
                    </div>

                    <div id="mockVerdict" class="rounded-2xl p-4 mb-6 text-sm font-semibold"></div>

                    <div class="flex gap-3 justify-center flex-wrap">
                        <button id="retakeBtn" class="btn-primary text-white font-bold px-6 py-3 rounded-2xl">
                            <i class="fas fa-redo mr-2"></i> Retake
                        </button>
                        <a id="mockReviewBtn" href="#" class="glass-card text-white font-semibold px-6 py-3 rounded-2xl flex items-center hover:bg-white/10 transition-all">
                            <i class="fas fa-eye mr-2"></i> Review Mistakes
                        </a>
                        <a href="dashboard.php" class="glass-card text-white font-semibold px-6 py-3 rounded-2xl flex items-center hover:bg-white/10 transition-all">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>const ROOT = '../';</script>
    <script src="../assets/js/left-sidebar.js"></script>
    <script src="../assets/js/mock.js"></script>
</body>
</html>
