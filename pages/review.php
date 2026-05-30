<?php
ini_set('session.cookie_path', '/');
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pageTitle = 'Review Mistakes — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css', 'assets/css/practice.css'];
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
        <img src="../assets/img/logo.png" class="w-9 h-9 rounded-xl object-contain">
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
    <div class="max-w-3xl mx-auto px-6 py-12">
        <div class="flex items-center gap-4 mb-8">
            <a href="javascript:history.back()" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black text-white">Review <span class="gradient-text">Mistakes</span></h1>
                <p class="text-slate-400 text-sm mt-1" id="reviewMeta"></p>
            </div>
        </div>

        <div id="reviewList" class="space-y-6">
            <div class="text-slate-500 text-center py-12">
                <i class="fas fa-spinner fa-spin text-2xl mb-3 block"></i>
                Loading…
            </div>
        </div>

        <div class="mt-8 flex gap-4 justify-center">
            <a href="javascript:history.back()" class="btn-primary text-white font-bold px-8 py-3 rounded-2xl">
                <i class="fas fa-arrow-left mr-2"></i> Back to Results
            </a>
            <a href="dashboard.php" class="glass-card text-white font-semibold px-8 py-3 rounded-2xl flex items-center hover:bg-white/10 transition-all">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<script>const ROOT = '../';</script>
<script src="../assets/js/left-sidebar.js"></script>
<script>
(() => {
    const params = new URLSearchParams(location.search);
    const sessionId = params.get('session_id');
    if (!sessionId) { document.getElementById('reviewList').innerHTML = '<p class="text-red-400 text-center">No session specified.</p>'; return; }

    const escHtml = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const letterColors = { a:'bg-slate-700', b:'bg-slate-700', c:'bg-slate-700', d:'bg-slate-700' };

    fetch(ROOT + 'api/quiz.php?action=review&session_id=' + sessionId)
        .then(r => r.json())
        .then(data => {
            if (data.error) { document.getElementById('reviewList').innerHTML = `<p class="text-red-400 text-center">${data.error}</p>`; return; }

            document.getElementById('reviewMeta').textContent = `${data.wrong} wrong out of ${data.total} questions`;

            if (data.items.length === 0) {
                document.getElementById('reviewList').innerHTML = `
                    <div class="glass-card rounded-2xl p-10 text-center">
                        <div class="text-4xl mb-4">🎉</div>
                        <p class="text-white font-bold text-lg">Perfect score! No mistakes to review.</p>
                    </div>`;
                return;
            }

            const choiceLabels = { a:'A', b:'B', c:'C', d:'D' };
            document.getElementById('reviewList').innerHTML = data.items.map((q, i) => `
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-7 h-7 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">${i+1}</span>
                        <p class="text-white font-medium leading-relaxed">${escHtml(q.question)}</p>
                    </div>
                    <div class="space-y-2 mb-4">
                        ${['a','b','c','d'].map(k => {
                            const val = q['choice_' + k];
                            const isCorrect = k === q.answer;
                            const isChosen  = k === q.chosen;
                            let cls = 'flex items-center gap-3 px-4 py-3 rounded-xl border text-sm ';
                            let dot = 'w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0 ';
                            if (isCorrect) {
                                cls += 'border-emerald-500 bg-emerald-500/10 text-emerald-300';
                                dot += 'bg-emerald-500 text-white';
                            } else if (isChosen) {
                                cls += 'border-red-500 bg-red-500/10 text-red-300';
                                dot += 'bg-red-500 text-white';
                            } else {
                                cls += 'border-slate-700 text-slate-500';
                                dot += 'bg-slate-700 text-slate-400';
                            }
                            const icon = isCorrect ? ' <i class="fas fa-check ml-auto text-emerald-400"></i>' : (isChosen ? ' <i class="fas fa-times ml-auto text-red-400"></i>' : '');
                            return `<div class="${cls}"><span class="${dot}">${k.toUpperCase()}</span><span class="flex-1">${escHtml(val)}</span>${icon}</div>`;
                        }).join('')}
                    </div>
                    ${q.explanation ? `
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-info-circle text-blue-400 text-xs"></i>
                            <span class="text-blue-400 text-xs font-semibold">Explanation</span>
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed">${escHtml(q.explanation)}</p>
                    </div>` : ''}
                </div>
            `).join('');
        });
})();
</script>
</body>
</html>
