<?php
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pageTitle = 'Study Ebook — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/ebook.css'];
$root      = '../';

$jsonPath = __DIR__ . '/../database/CSE_Mastery_Guide_Ebook.json';
$ebook    = json_decode(file_get_contents($jsonPath), true);
$meta     = $ebook['book_metadata'];
$chapters = $ebook['chapters'];

$chapterIcons = [
    1 => 'fa-brain',
    2 => 'fa-clock',
    3 => 'fa-spell-check',
    4 => 'fa-calculator',
    5 => 'fa-puzzle-piece',
    6 => 'fa-landmark',
    7 => 'fa-folder-open',
    8 => 'fa-flag-checkered',
];
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../includes/head.php'; ?></head>
<body class="bg-slate-950 min-h-screen">

<!-- Reading progress bar -->
<div class="reading-progress"><div class="reading-progress-bar" id="readingBar"></div></div>

<!-- Nav -->
<nav class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="dashboard.php" class="text-slate-400 hover:text-white transition-colors mr-1" title="Back to Dashboard">
            <i class="fas fa-arrow-left"></i>
        </a>
        <img src="../assets/img/logo.png" alt="Logo" class="w-9 h-9 rounded-xl object-contain">
        <span class="text-white font-bold text-lg">CSE<span class="gradient-text">Reviewer</span></span>
        <span class="hidden sm:inline text-slate-600 text-sm ml-1">/ Study Ebook</span>
    </div>
    <div class="flex items-center gap-3">
        <span class="hidden sm:inline text-slate-400 text-sm">Hello, <span class="text-white font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span></span>
        <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline"> Logout</span>
        </a>
    </div>
</nav>

<div class="ebook-layout">

    <!-- TOC Sidebar -->
    <aside class="ebook-toc" id="ebookToc">
        <div class="toc-title"><i class="fas fa-book-open mr-1"></i> Table of Contents</div>
        <a href="#preface" class="toc-item" data-target="preface">
            <span class="toc-num"><i class="fas fa-star" style="font-size:8px;"></i></span> Preface
        </a>
        <?php foreach ($chapters as $ch): ?>
        <a href="#chapter-<?= $ch['chapter_number'] ?>" class="toc-item" data-target="chapter-<?= $ch['chapter_number'] ?>">
            <span class="toc-num"><?= $ch['chapter_number'] ?></span><?= htmlspecialchars($ch['title']) ?>
        </a>
        <?php endforeach; ?>
    </aside>

    <!-- Main Content -->
    <main class="ebook-content">

        <!-- Book Header -->
        <div class="mb-10">
            <div class="text-xs font-bold uppercase tracking-widest text-indigo-400 mb-2">
                <i class="fas fa-book mr-1"></i> CSE Mastery Guide
            </div>
            <h1 class="text-3xl font-extrabold text-white leading-tight mb-3">
                <?= htmlspecialchars($meta['title']) ?>
            </h1>
            <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                <span><i class="fas fa-user-tie mr-1 text-indigo-400"></i><?= htmlspecialchars($meta['author']) ?></span>
                <span><i class="fas fa-users mr-1 text-purple-400"></i><?= htmlspecialchars($meta['target_audience']) ?></span>
            </div>
        </div>

        <!-- Preface -->
        <div class="preface-card" id="preface" style="scroll-margin-top:80px;">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-quote-left text-indigo-400"></i>
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-400">Preface</span>
            </div>
            <p class="text-slate-300 text-sm leading-relaxed italic"><?= htmlspecialchars($ebook['preface']) ?></p>
        </div>

        <!-- Chapters -->
        <?php foreach ($chapters as $ch):
            $icon = $chapterIcons[$ch['chapter_number']] ?? 'fa-circle';
        ?>
        <div class="chapter-block" id="chapter-<?= $ch['chapter_number'] ?>">
            <div class="chapter-header">
                <span class="chapter-badge">Chapter <?= $ch['chapter_number'] ?></span>
                <i class="fas <?= $icon ?> text-indigo-400" style="font-size:15px;"></i>
            </div>
            <h2 class="chapter-title mb-3"><?= htmlspecialchars($ch['title']) ?></h2>
            <p class="chapter-intro"><?= htmlspecialchars($ch['introduction']) ?></p>
            <div class="chapter-divider"></div>

            <!-- Sections -->
            <?php foreach ($ch['sections'] as $i => $sec): ?>
            <div class="section-card <?= $i === 0 ? 'open' : '' ?>">
                <button class="section-toggle" onclick="toggleSection(this)">
                    <span class="section-topic">
                        <i class="fas fa-lightbulb"></i>
                        <?= htmlspecialchars($sec['topic']) ?>
                    </span>
                    <i class="fas fa-chevron-down section-chevron"></i>
                </button>
                <div class="section-body">
                    <p class="section-content"><?= htmlspecialchars($sec['content']) ?></p>

                    <?php if (!empty($sec['examples'])): ?>
                        <?php foreach ($sec['examples'] as $ex): ?>
                        <div class="example-box">
                            <div class="example-label"><i class="fas fa-flask mr-1"></i> Example</div>
                            <div class="example-question"><?= htmlspecialchars($ex['question']) ?></div>
                            <ul class="example-options">
                                <?php foreach ($ex['options'] as $opt):
                                    $isCorrect = (trim($opt) === trim($ex['answer']));
                                ?>
                                <li class="<?= $isCorrect ? 'correct' : '' ?>">
                                    <?php if ($isCorrect): ?><i class="fas fa-check-circle mr-1"></i><?php endif; ?>
                                    <?= htmlspecialchars($opt) ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="answer-row">
                                <span class="answer-badge">Answer: <?= htmlspecialchars($ex['answer']) ?></span>
                                <span class="answer-explanation"><?= htmlspecialchars($ex['explanation']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

    </main>
</div>

<!-- Mobile TOC toggle -->
<button class="toc-toggle-btn" id="tocToggleBtn" title="Table of Contents">
    <i class="fas fa-list"></i>
</button>

<footer class="relative z-10 text-center py-6 border-t border-slate-800 bg-slate-800/30">
    <p class="text-slate-600 text-xs">
        Made by <span class="text-slate-400 font-semibold">Leandro Lojero</span>
        &nbsp;·&nbsp;
        <a href="https://facebook.com/leandro.lojero23" target="_blank" rel="noopener"
           class="text-indigo-400 hover:text-indigo-300 transition-colors">
            <i class="fab fa-facebook mr-1"></i>@leandro.lojero23
        </a>
    </p>
</footer>

<script>
function toggleSection(btn) {
    btn.closest('.section-card').classList.toggle('open');
}

// Reading progress
window.addEventListener('scroll', () => {
    const doc = document.documentElement;
    const pct = (doc.scrollTop / (doc.scrollHeight - doc.clientHeight)) * 100;
    document.getElementById('readingBar').style.width = pct + '%';
});

// TOC active highlight
const tocItems = document.querySelectorAll('.toc-item[data-target]');
const sections = [...document.querySelectorAll('[id]')].filter(el =>
    el.id === 'preface' || el.id.startsWith('chapter-')
);

const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            tocItems.forEach(t => t.classList.remove('active'));
            const active = document.querySelector(`.toc-item[data-target="${e.target.id}"]`);
            if (active) active.classList.add('active');
        }
    });
}, { rootMargin: '-20% 0px -70% 0px' });

sections.forEach(s => observer.observe(s));

// Smooth scroll for TOC links
tocItems.forEach(item => {
    item.addEventListener('click', e => {
        e.preventDefault();
        const target = document.getElementById(item.dataset.target);
        if (target) target.scrollIntoView({ behavior: 'smooth' });
        document.getElementById('ebookToc').classList.remove('mobile-open');
    });
});

// Mobile TOC toggle
document.getElementById('tocToggleBtn').addEventListener('click', () => {
    document.getElementById('ebookToc').classList.toggle('mobile-open');
});
</script>
</body>
</html>
