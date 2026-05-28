<nav class="nav-blur fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="<?= $root ?>assets/img/logo.png" alt="CSE Reviewer Logo" class="w-9 h-9 rounded-xl object-contain">
            <span class="text-white font-bold text-lg tracking-tight">CSE<span class="gradient-text">Reviewer</span></span>
        </div>
        <div class="hidden md:flex items-center gap-8">
            <a href="#features" class="text-slate-400 hover:text-white text-sm transition-colors">Features</a>
            <a href="#subjects" class="text-slate-400 hover:text-white text-sm transition-colors">Subjects</a>
            <a href="#stats" class="text-slate-400 hover:text-white text-sm transition-colors">Stats</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= $root ?>pages/login.php" class="text-slate-300 hover:text-white text-sm font-medium transition-colors px-4 py-2">Log In</a>
            <a href="<?= $root ?>pages/register.php" class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl">Get Started</a>
        </div>
    </div>
</nav>
