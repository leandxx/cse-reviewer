<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
$pageTitle = 'Import Questions — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css', 'assets/css/import.css'];
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

            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-white mb-3">Import <span class="gradient-text">Questions</span></h1>
                <p class="text-slate-400">Upload a CSE reviewer PDF — AI will extract, analyze, and enrich the questions automatically.</p>
            </div>

            <!-- Step 1: Upload -->
            <div id="stepUpload" class="glass-card rounded-2xl p-8 mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="step-badge">1</div>
                    <h2 class="text-white font-bold text-lg">Upload PDF</h2>
                </div>

                <div id="dropZone" class="drop-zone rounded-2xl p-10 text-center cursor-pointer mb-6">
                    <i class="fas fa-file-pdf text-4xl text-slate-600 mb-4 block"></i>
                    <p class="text-slate-400 mb-2">Drag & drop your PDF here, or <span class="text-indigo-400 font-semibold">click to browse</span></p>
                    <p class="text-slate-600 text-xs">Max 10MB · Text-based PDFs only (not scanned images)</p>
                    <input type="file" id="pdfInput" accept=".pdf" class="hidden">
                </div>

                <div class="mb-6">
                    <label class="text-slate-300 text-sm font-medium block mb-3">Default Subject <span class="text-slate-500 text-xs">(used if AI can't detect it)</span></label>
                    <div class="flex gap-3 flex-wrap">
                        <?php foreach (['verbal'=>'Verbal','numerical'=>'Numerical','analytical'=>'Analytical','general'=>'General Info'] as $val=>$label): ?>
                        <button class="subject-pick px-4 py-2 rounded-xl text-sm font-semibold border border-slate-700 text-slate-400 hover:border-indigo-500 hover:text-white transition-all"
                                data-subject="<?= $val ?>"><?= $label ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button id="uploadBtn" class="btn-primary w-full text-white font-bold py-4 rounded-2xl opacity-50 cursor-not-allowed" disabled>
                    <i class="fas fa-upload mr-2"></i> Upload & Extract Text
                </button>
            </div>

            <!-- Step 2: Analyze -->
            <div id="stepAnalyze" class="glass-card rounded-2xl p-8 mb-6 hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="step-badge">2</div>
                    <h2 class="text-white font-bold text-lg">AI Analysis</h2>
                </div>

                <div id="extractedPreview" class="bg-slate-900 rounded-xl p-4 mb-6 max-h-40 overflow-y-auto">
                    <p class="text-slate-500 text-xs font-mono" id="extractedText"></p>
                </div>

                <div id="analyzeStatus" class="hidden mb-4">
                    <div class="flex items-center gap-3 text-indigo-400">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span class="text-sm" id="analyzeStatusText">Sending to Gemini AI…</span>
                    </div>
                    <div class="h-1.5 bg-slate-800 rounded-full mt-3 overflow-hidden">
                        <div id="analyzeBar" class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full analyze-progress"></div>
                    </div>
                </div>

                <button id="analyzeBtn" class="btn-primary w-full text-white font-bold py-4 rounded-2xl">
                    <i class="fas fa-brain mr-2"></i> Analyze with Gemini AI
                </button>
            </div>

            <!-- Step 3: Preview & Save -->
            <div id="stepPreview" class="hidden">
                <div class="glass-card rounded-2xl p-8 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="step-badge">3</div>
                            <h2 class="text-white font-bold text-lg">Preview & Save</h2>
                        </div>
                        <span id="qCount" class="text-indigo-400 text-sm font-semibold bg-indigo-500/10 px-3 py-1 rounded-full"></span>
                    </div>

                    <div id="previewTable" class="space-y-4 mb-6 max-h-[500px] overflow-y-auto pr-1"></div>

                    <button id="saveBtn" class="btn-primary w-full text-white font-bold py-4 rounded-2xl">
                        <i class="fas fa-save mr-2"></i> Save All to Database
                    </button>
                </div>
            </div>

            <!-- Success -->
            <div id="stepSuccess" class="hidden glass-card rounded-2xl p-10 text-center">
                <div class="text-5xl mb-4">🎉</div>
                <h2 class="text-2xl font-black text-white mb-2">Questions Imported!</h2>
                <p id="savedCount" class="text-slate-400 mb-8"></p>
                <div class="flex gap-4 justify-center">
                    <a href="practice.php" class="btn-primary text-white font-bold px-8 py-3 rounded-2xl">
                        <i class="fas fa-play mr-2"></i> Start Practicing
                    </a>
                    <button id="importMoreBtn" class="glass-card text-white font-semibold px-8 py-3 rounded-2xl hover:bg-white/10 transition-all">
                        <i class="fas fa-plus mr-2"></i> Import More
                    </button>
                </div>
            </div>

            <!-- Error -->
            <div id="errorBox" class="hidden bg-red-500/10 border border-red-500/20 rounded-2xl p-5 mb-6">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                    <span class="text-red-400 font-semibold text-sm">Error</span>
                </div>
                <p id="errorText" class="text-slate-300 text-sm"></p>
            </div>

        </div>
    </div>

    <script>const ROOT = '../';</script>
    <script src="../assets/js/left-sidebar.js"></script>
    <script src="../assets/js/import.js"></script>
</body>
</html>
