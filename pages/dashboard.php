<?php
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pageTitle = 'Dashboard — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css'];
$root      = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../includes/head.php'; ?></head>
<body class="bg-slate-950 min-h-screen">

<nav class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <button id="sbHamburger" class="md:hidden text-slate-400 hover:text-white mr-1" aria-label="Open sidebar">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <img src="../assets/img/logo.png" alt="CSE Reviewer Logo" class="w-9 h-9 rounded-xl object-contain">
        <span class="text-white font-bold text-lg">CSE<span class="gradient-text">Reviewer</span></span>
    </div>
    <div class="flex items-center gap-3">
        <span class="hidden sm:inline text-slate-400 text-sm">Hello, <span class="text-white font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span></span>
        <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline"> Logout</span>
        </a>
    </div>
</nav>

<?php include '../includes/left-sidebar.php'; ?>

<div class="dashboard-main flex flex-col min-h-screen">
    <div class="max-w-5xl mx-auto px-6 py-12">

        <!-- XP / Level card -->
        <div class="card rounded-2xl p-6 mb-8">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-star text-white text-lg"></i>
                    </div>
                    <div>
                        <div class="text-white font-bold text-lg">Level <span id="userLevel">—</span></div>
                        <div class="text-slate-400 text-xs"><span id="userXP">—</span> XP total</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-slate-400 text-xs mb-1"><span id="xpInLevel">—</span> / 100 XP to next level</div>
                </div>
            </div>
            <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
                <div id="xpBar" class="h-full bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full transition-all duration-700" style="width:0%"></div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-10">
            <?php
            $cards = [
                ['fas fa-book-open', 'from-indigo-500 to-purple-500', 'Start Reviewing',  'Practice questions across all CSE subjects.', 'Practice Now', 'practice.php'],
                ['fas fa-file-import','from-blue-500 to-cyan-500',   'Import Questions', 'Upload a PDF reviewer and let AI extract the questions.', 'Import PDF', 'import.php'],
                ['fas fa-clock',      'from-purple-500 to-pink-500', 'Mock Exams',        'Timed full-length practice exams.', 'Start Exam', 'mock.php'],
                ['fas fa-graduation-cap', 'from-emerald-500 to-teal-500', 'Study Ebook', 'Tips, shortcuts, and strategies to pass the CSE.', 'Read Now', 'ebook.php'],
                ['fas fa-layer-group', 'from-violet-500 to-fuchsia-500', 'Flashcards', 'Flip through key concepts by category.', 'Study Now', 'flashcard.php'],
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

        <!-- Score history -->
        <div class="card rounded-2xl p-6">
            <h2 class="text-white font-bold text-lg mb-5 flex items-center gap-2">
                <i class="fas fa-history text-indigo-400"></i> Recent Sessions
            </h2>
            <div id="historyList">
                <div class="text-slate-500 text-sm text-center py-6">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Loading…
                </div>
            </div>
        </div>

    </div>
</div>

<footer class="relative z-10 text-center py-6 mt-auto border-t border-slate-800 bg-slate-800/30">
    <p class="text-slate-600 text-xs">
        Made by <span class="text-slate-400 font-semibold">Leandro Lojero</span>
        &nbsp;·&nbsp;
        <a href="https://facebook.com/leandro.lojero23" target="_blank" rel="noopener"
           class="text-indigo-400 hover:text-indigo-300 transition-colors">
            <i class="fab fa-facebook mr-1"></i>@leandro.lojero23
        </a>
    </p>
</footer>

<script>const ROOT = '../';</script>
<script src="../assets/js/left-sidebar.js"></script>
<script>
(() => {
    const subjectLabels = {
        verbal:'Verbal', numerical:'Numerical', analytical:'Analytical', general:'General Info', all:'Mock Exam'
    };
    const subjectColors = {
        verbal:'text-indigo-400 bg-indigo-500/10', numerical:'text-blue-400 bg-blue-500/10',
        analytical:'text-purple-400 bg-purple-500/10', general:'text-emerald-400 bg-emerald-500/10',
        all:'text-pink-400 bg-pink-500/10'
    };

    fetch(ROOT + 'api/stats.php?action=stats')
        .then(r => r.json())
        .then(d => {
            document.getElementById('userLevel').textContent  = d.level;
            document.getElementById('userXP').textContent     = d.xp.toLocaleString();
            document.getElementById('xpInLevel').textContent  = d.xp_in_level;
            document.getElementById('xpBar').style.width      = (d.xp_in_level / d.xp_for_next * 100) + '%';

            const list = document.getElementById('historyList');
            if (!d.history.length) {
                list.innerHTML = '<p class="text-slate-500 text-sm text-center py-6">No sessions yet. Start practicing!</p>';
                return;
            }

            list.innerHTML = d.history.map(s => {
                const pct   = Math.round(s.correct / s.total * 100);
                const color = pct >= 80 ? 'text-emerald-400' : pct >= 60 ? 'text-yellow-400' : 'text-red-400';
                const bar   = pct >= 80 ? 'bg-emerald-500' : pct >= 60 ? 'bg-yellow-500' : 'bg-red-500';
                const subCls = subjectColors[s.subject] ?? 'text-slate-400 bg-slate-700';
                const date  = new Date(s.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
                return `
                <div class="py-3 border-b border-slate-800 last:border-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full ${subCls} flex-shrink-0">
                            ${subjectLabels[s.subject] ?? s.subject}
                        </span>
                        <span class="text-slate-500 text-xs ml-auto">${date}</span>
                        <a href="review.php?session_id=${s.id}" class="text-xs text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-2 py-1 rounded-lg transition-colors flex-shrink-0">
                            <i class="fas fa-eye mr-1"></i>Review
                        </a>
                    </div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-300 text-sm">${s.correct}/${s.total} correct</span>
                        <span class="font-bold text-sm ${color}">${pct}%</span>
                    </div>
                    <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full ${bar} rounded-full" style="width:${pct}%"></div>
                    </div>
                </div>`;
            }).join('');
        });
})();
</script>
</body>
</html>
