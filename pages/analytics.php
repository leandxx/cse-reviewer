<?php
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pageTitle = 'Analytics — CSEReviewer';
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
        <a href="dashboard.php" class="text-slate-400 hover:text-white text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-home"></i><span class="hidden sm:inline"> Dashboard</span>
        </a>
        <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline"> Logout</span>
        </a>
    </div>
</nav>

<?php include '../includes/left-sidebar.php'; ?>

<div class="dashboard-main">
    <div class="max-w-4xl mx-auto px-6 py-12">

        <div class="mb-8">
            <h1 class="text-3xl font-black text-white mb-1">Weakness <span class="gradient-text">Dashboard</span></h1>
            <p class="text-slate-400 text-sm">Insights based on all your finished sessions.</p>
        </div>

        <!-- Weakest / Strongest -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
            <div class="card rounded-2xl p-6 flex items-center gap-4" id="weakestCard">
                <div class="w-12 h-12 bg-red-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-arrow-trend-down text-red-400 text-lg"></i>
                </div>
                <div>
                    <div class="text-slate-400 text-xs mb-1">Weakest Category</div>
                    <div class="text-white font-bold text-lg" id="weakestLabel">—</div>
                    <div class="text-red-400 text-sm font-semibold" id="weakestPct"></div>
                </div>
            </div>
            <div class="card rounded-2xl p-6 flex items-center gap-4" id="strongestCard">
                <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-arrow-trend-up text-emerald-400 text-lg"></i>
                </div>
                <div>
                    <div class="text-slate-400 text-xs mb-1">Strongest Category</div>
                    <div class="text-white font-bold text-lg" id="strongestLabel">—</div>
                    <div class="text-emerald-400 text-sm font-semibold" id="strongestPct"></div>
                </div>
            </div>
        </div>

        <!-- Accuracy per topic -->
        <div class="card rounded-2xl p-6 mb-6">
            <h2 class="text-white font-bold text-base mb-5 flex items-center gap-2">
                <i class="fas fa-bullseye text-indigo-400"></i> Accuracy per Topic
            </h2>
            <div id="accuracyBars" class="space-y-4">
                <div class="text-slate-500 text-sm text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</div>
            </div>
        </div>

        <!-- Time per subject -->
        <div class="card rounded-2xl p-6">
            <h2 class="text-white font-bold text-base mb-5 flex items-center gap-2">
                <i class="fas fa-stopwatch text-purple-400"></i> Avg Time per Question Type
            </h2>
            <div id="timeBars" class="space-y-4">
                <div class="text-slate-500 text-sm text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</div>
            </div>
        </div>

    </div>
</div>

<script>const ROOT = '../';</script>
<script src="../assets/js/left-sidebar.js"></script>
<script>
(() => {
    const LABELS = { verbal:'Verbal', numerical:'Numerical', analytical:'Analytical', general:'General Info', all:'Mock Exam' };
    const COLORS  = {
        verbal:    { bar:'bg-indigo-500',  text:'text-indigo-400'  },
        numerical: { bar:'bg-blue-500',    text:'text-blue-400'    },
        analytical:{ bar:'bg-purple-500',  text:'text-purple-400'  },
        general:   { bar:'bg-emerald-500', text:'text-emerald-400' },
        all:       { bar:'bg-pink-500',    text:'text-pink-400'    },
    };

    function label(s) { return LABELS[s] ?? s; }
    function color(s) { return COLORS[s] ?? { bar:'bg-slate-500', text:'text-slate-400' }; }

    function accuracyColor(pct) {
        if (pct >= 80) return 'bg-emerald-500';
        if (pct >= 60) return 'bg-yellow-500';
        return 'bg-red-500';
    }
    function accuracyText(pct) {
        if (pct >= 80) return 'text-emerald-400';
        if (pct >= 60) return 'text-yellow-400';
        return 'text-red-400';
    }

    fetch(ROOT + 'api/analytics.php')
        .then(r => r.json())
        .then(d => {
            // Weakest / Strongest
            if (d.weakest) {
                document.getElementById('weakestLabel').textContent = label(d.weakest);
                document.getElementById('weakestPct').textContent   = d.accuracy[d.weakest].accuracy + '% accuracy';
            } else {
                document.getElementById('weakestLabel').textContent = 'No data yet';
            }
            if (d.strongest) {
                document.getElementById('strongestLabel').textContent = label(d.strongest);
                document.getElementById('strongestPct').textContent   = d.accuracy[d.strongest].accuracy + '% accuracy';
            } else {
                document.getElementById('strongestLabel').textContent = 'No data yet';
            }

            // Accuracy bars
            const accEl = document.getElementById('accuracyBars');
            const subjects = Object.keys(d.accuracy);
            if (!subjects.length) {
                accEl.innerHTML = '<p class="text-slate-500 text-sm text-center py-4">No sessions yet. Start practicing!</p>';
            } else {
                accEl.innerHTML = subjects.map(s => {
                    const { correct, total, accuracy: pct } = d.accuracy[s];
                    return `
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-slate-300 text-sm font-medium">${label(s)}</span>
                            <span class="text-xs font-semibold ${accuracyText(pct)}">${pct}% &nbsp;<span class="text-slate-500 font-normal">${correct}/${total}</span></span>
                        </div>
                        <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full ${accuracyColor(pct)} rounded-full transition-all duration-700" style="width:${pct}%"></div>
                        </div>
                    </div>`;
                }).join('');
            }

            // Time bars
            const timeEl = document.getElementById('timeBars');
            const timeSubjects = Object.keys(d.time);
            if (!timeSubjects.length) {
                timeEl.innerHTML = '<p class="text-slate-500 text-sm text-center py-4">No time data yet. Time tracking activates on new sessions.</p>';
            } else {
                const maxTime = Math.max(...timeSubjects.map(s => d.time[s]));
                timeEl.innerHTML = timeSubjects.map(s => {
                    const secs = d.time[s];
                    const pct  = maxTime > 0 ? Math.round(secs / maxTime * 100) : 0;
                    const c    = color(s);
                    return `
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-slate-300 text-sm font-medium">${label(s)}</span>
                            <span class="text-xs font-semibold ${c.text}">${secs}s avg</span>
                        </div>
                        <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full ${c.bar} rounded-full transition-all duration-700" style="width:${pct}%"></div>
                        </div>
                    </div>`;
                }).join('');
            }
        });
})();
</script>
</body>
</html>
