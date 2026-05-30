<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$pageTitle = 'Dashboard — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css'];
$root      = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-slate-950 min-h-screen">

    <nav class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex items-center justify-between" style="position:relative;z-index:1;">
        <div class="flex items-center gap-3">
            <img src="../assets/img/logo.png" alt="CSE Reviewer Logo" class="w-9 h-9 rounded-xl object-contain">
            <span class="text-white font-bold text-lg">CSE<span class="gradient-text">Reviewer</span></span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-slate-400 text-sm">Hello, <span class="text-white font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span></span>
            <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Left sidebar -->
    <?php include '../includes/left-sidebar.php'; ?>

    <div class="dashboard-main">
        <div class="max-w-5xl mx-auto px-6 py-12">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-black text-white mb-3">Your <span class="gradient-text">Dashboard</span></h1>
                <p class="text-slate-400">The full reviewer system is coming soon. You're all set up!</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $cards = [
                    ['fas fa-book-open', 'from-indigo-500 to-purple-500', 'Start Reviewing', 'Practice questions across all CSE subjects.', 'Practice Now', 'practice.php'],
                    ['fas fa-file-import', 'from-blue-500 to-cyan-500', 'Import Questions', 'Upload a PDF reviewer and let AI extract the questions.', 'Import PDF', 'import.php'],
                    ['fas fa-clock', 'from-purple-500 to-pink-500', 'Mock Exams', 'Timed full-length practice exams.', 'Coming Soon', '#'],
                ];
                foreach ($cards as $c): ?>
                <div class="card rounded-2xl p-6 text-center">
                    <div class="w-14 h-14 bg-gradient-to-br <?= $c[1] ?> rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="<?= $c[0] ?> text-white text-xl"></i>
                    </div>
                    <h3 class="text-white font-bold text-lg mb-2"><?= $c[2] ?></h3>
                    <p class="text-slate-400 text-sm mb-4"><?= $c[3] ?></p>
                    <a href="<?= $c[5] ?>" class="text-xs font-semibold text-indigo-400 bg-indigo-500/10 px-3 py-1 rounded-full hover:bg-indigo-500/20 transition-all"><?= $c[4] ?></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>const ROOT = '../';</script>
    <script src="../assets/js/left-sidebar.js"></script>
</body>
</html>
