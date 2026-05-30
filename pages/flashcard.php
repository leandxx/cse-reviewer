<?php
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pageTitle = 'Flashcards — CSEReviewer';
$extraCss  = ['assets/css/flashcard.css', 'assets/css/left-sidebar.css'];
$root      = '../';

$data       = json_decode(file_get_contents('../database/flashcard.json'), true);
$categories = $data['categories'] ?? [];
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
            <i class="fas fa-arrow-left"></i><span class="hidden sm:inline"> Dashboard</span>
        </a>
        <span class="hidden sm:inline text-slate-400 text-sm">Hello, <span class="text-white font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span></span>
        <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline"> Logout</span>
        </a>
    </div>
</nav>

<?php include '../includes/left-sidebar.php'; ?>

<div class="flashcard-main min-h-screen">
    <div class="max-w-3xl mx-auto px-6 py-12">

        <!-- Category Picker -->
        <div id="pickerView">
            <div class="mb-8 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-layer-group text-white text-2xl"></i>
                </div>
                <h1 class="text-white font-bold text-2xl mb-1">Flashcards</h1>
                <p class="text-slate-400 text-sm">Pick a category to start reviewing</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php
                $catStyles = [
                    'Verbal Ability'      => ['from-indigo-500 to-purple-500',  'fas fa-spell-check'],
                    'Analytical Ability'  => ['from-purple-500 to-pink-500',    'fas fa-brain'],
                    'Numerical Ability'   => ['from-blue-500 to-cyan-500',      'fas fa-calculator'],
                    'General Information' => ['from-emerald-500 to-teal-500',   'fas fa-globe-asia'],
                ];
                foreach ($categories as $i => $cat):
                    $name  = $cat['category_name'];
                    $count = count($cat['flashcards']);
                    [$grad, $icon] = $catStyles[$name] ?? ['from-slate-500 to-slate-600', 'fas fa-cards'];
                ?>
                <button onclick="startSession(<?= $i ?>)"
                    class="card rounded-2xl p-6 text-left hover:border-indigo-500/40 hover:bg-white/[0.06] transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br <?= $grad ?> rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="<?= $icon ?> text-white text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold text-base group-hover:text-indigo-300 transition-colors"><?= htmlspecialchars($name) ?></div>
                            <div class="text-slate-500 text-xs mt-0.5"><?= $count ?> flashcards</div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-600 group-hover:text-indigo-400 ml-auto transition-colors"></i>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Flashcard Session View -->
        <div id="sessionView" class="hidden">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <button onclick="backToPicker()" class="text-slate-400 hover:text-white text-sm flex items-center gap-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <div class="text-center">
                    <div id="sessionTitle" class="text-white font-bold text-lg"></div>
                    <div id="sessionProgress" class="text-slate-400 text-xs"></div>
                </div>
                <button onclick="shuffleCards()" title="Shuffle" class="text-slate-400 hover:text-indigo-400 transition-colors">
                    <i class="fas fa-random"></i>
                </button>
            </div>

            <!-- Progress bar -->
            <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden mb-8">
                <div id="progressBar" class="h-full bg-gradient-to-r from-violet-500 to-fuchsia-500 rounded-full transition-all duration-500" style="width:0%"></div>
            </div>

            <!-- Flip Card -->
            <div class="fc-scene mb-6">
                <div class="fc-card" id="fcCard" onclick="flipCard()">
                    <div class="fc-face front">
                        <span class="text-slate-500 text-xs uppercase tracking-widest mb-3">Question — click to reveal</span>
                        <p id="fcQuestion" class="text-white text-lg font-medium leading-relaxed"></p>
                    </div>
                    <div class="fc-face back">
                        <span class="text-indigo-400 text-xs uppercase tracking-widest mb-3">Answer</span>
                        <p id="fcAnswer" class="text-white text-xl font-bold mb-3"></p>
                        <p id="fcExplanation" class="text-slate-400 text-sm leading-relaxed"></p>
                    </div>
                </div>
            </div>

            <!-- Card ID badge -->
            <div class="text-center mb-6">
                <span id="fcId" class="text-slate-600 text-xs font-mono"></span>
            </div>

            <!-- Navigation -->
            <div class="flex items-center justify-center gap-4">
                <button id="btnPrev" onclick="navigate(-1)"
                    class="w-12 h-12 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button onclick="flipCard()"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-all">
                    <i class="fas fa-sync-alt mr-2"></i>Flip
                </button>
                <button id="btnNext" onclick="navigate(1)"
                    class="w-12 h-12 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Done banner -->
            <div id="doneBanner" class="hidden mt-8 card rounded-2xl p-6 text-center animate-in">
                <i class="fas fa-check-circle text-emerald-400 text-3xl mb-3"></i>
                <p class="text-white font-bold text-lg mb-1">All cards reviewed!</p>
                <p class="text-slate-400 text-sm mb-4">Great job going through the deck.</p>
                <div class="flex justify-center gap-3">
                    <button onclick="restartSession()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-all">
                        <i class="fas fa-redo mr-1"></i> Restart
                    </button>
                    <button onclick="backToPicker()" class="px-5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold transition-all">
                        <i class="fas fa-th-large mr-1"></i> Categories
                    </button>
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
const CATEGORIES = <?= json_encode(array_map(fn($c) => [
    'name'       => $c['category_name'],
    'flashcards' => $c['flashcards'],
], $categories)) ?>;

let deck = [], idx = 0;

function startSession(catIndex) {
    deck = [...CATEGORIES[catIndex].flashcards];
    idx  = 0;
    document.getElementById('sessionTitle').textContent = CATEGORIES[catIndex].name;
    document.getElementById('pickerView').classList.add('hidden');
    document.getElementById('sessionView').classList.remove('hidden');
    document.getElementById('doneBanner').classList.add('hidden');
    renderCard();
}

function renderCard() {
    const card = deck[idx];
    const total = deck.length;

    document.getElementById('fcQuestion').textContent    = card.question;
    document.getElementById('fcAnswer').textContent      = card.answer;
    document.getElementById('fcExplanation').textContent = card.explanation;
    document.getElementById('fcId').textContent          = card.id;
    document.getElementById('sessionProgress').textContent = `${idx + 1} / ${total}`;
    document.getElementById('progressBar').style.width  = ((idx + 1) / total * 100) + '%';

    // Reset flip
    const fc = document.getElementById('fcCard');
    fc.classList.remove('flipped');

    document.getElementById('btnPrev').disabled = idx === 0;
    document.getElementById('btnNext').disabled = idx === total - 1;
    document.getElementById('doneBanner').classList.add('hidden');
}

function flipCard() {
    document.getElementById('fcCard').classList.toggle('flipped');
}

function navigate(dir) {
    const next = idx + dir;
    if (next < 0 || next >= deck.length) return;
    idx = next;
    renderCard();
    if (idx === deck.length - 1) {
        setTimeout(() => document.getElementById('doneBanner').classList.remove('hidden'), 400);
    }
}

function shuffleCards() {
    for (let i = deck.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [deck[i], deck[j]] = [deck[j], deck[i]];
    }
    idx = 0;
    renderCard();
}

function restartSession() {
    idx = 0;
    document.getElementById('doneBanner').classList.add('hidden');
    renderCard();
}

function backToPicker() {
    document.getElementById('sessionView').classList.add('hidden');
    document.getElementById('pickerView').classList.remove('hidden');
}
</script>
</body>
</html>
