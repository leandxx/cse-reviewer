<?php
$pageTitle = 'CSE Reviewer — Civil Service Exam Reviewer';
$extraCss  = ['assets/css/index.css'];
$root      = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-slate-950">

    <?php include 'includes/navbar.php'; ?>

    <!-- Hero -->
    <section class="hero-bg">
        <div class="hero-content max-w-7xl mx-auto px-6 pt-36 pb-24 flex flex-col lg:flex-row items-center gap-16">

            <!-- Left -->
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 glass-card px-4 py-2 rounded-full mb-6 floating-badge">
                    <div class="glow-dot"></div>
                    <span class="text-slate-300 text-xs font-medium">Philippines Civil Service Exam Reviewer</span>
                </div>

                <h1 class="text-5xl lg:text-7xl font-black text-white leading-tight mb-6">
                    Pass the<br>
                    <span class="gradient-text">Civil Service</span><br>
                    Exam
                </h1>

                <p class="text-slate-400 text-lg leading-relaxed mb-10 max-w-xl">
                    Comprehensive reviewer for the Philippine Civil Service Examination. Practice with real-type questions, track your progress, and ace the exam with confidence.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="pages/register.php" class="btn-primary text-white font-bold px-8 py-4 rounded-2xl text-base flex items-center justify-center gap-2">
                        <i class="fas fa-rocket"></i> Start Reviewing Free
                    </a>
                    <a href="pages/login.php" class="glass-card text-white font-semibold px-8 py-4 rounded-2xl text-base flex items-center justify-center gap-2 hover:bg-white/10 transition-all">
                        <i class="fas fa-sign-in-alt"></i> Log In
                    </a>
                </div>

                <div class="flex items-center gap-6 mt-10 justify-center lg:justify-start">
                    <div class="text-center">
                        <div class="text-2xl font-black stat-number">10K+</div>
                        <div class="text-slate-500 text-xs">Questions</div>
                    </div>
                    <div class="w-px h-10 bg-slate-700"></div>
                    <div class="text-center">
                        <div class="text-2xl font-black stat-number">5K+</div>
                        <div class="text-slate-500 text-xs">Reviewees</div>
                    </div>
                    <div class="w-px h-10 bg-slate-700"></div>
                    <div class="text-center">
                        <div class="text-2xl font-black stat-number">92%</div>
                        <div class="text-slate-500 text-xs">Pass Rate</div>
                    </div>
                </div>
            </div>

            <!-- Right — Visual Card -->
            <div class="flex-1 flex justify-center lg:justify-end">
                <div class="glass-card rounded-3xl p-8 w-full max-w-sm">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-white font-semibold text-sm">Today's Progress</span>
                        <span class="text-indigo-400 text-xs font-medium bg-indigo-500/10 px-3 py-1 rounded-full">Day 7</span>
                    </div>

                    <div class="space-y-4 mb-6">
                        <?php
                        $subjects = [
                            ['Verbal Ability',    78, 'from-indigo-500 to-purple-500'],
                            ['Numerical Ability', 65, 'from-blue-500 to-cyan-500'],
                            ['Analytical Ability',82, 'from-purple-500 to-pink-500'],
                            ['General Info',      71, 'from-emerald-500 to-teal-500'],
                        ];
                        foreach ($subjects as $s): ?>
                        <div>
                            <div class="flex justify-between text-xs mb-1.5">
                                <span class="text-slate-300"><?= $s[0] ?></span>
                                <span class="text-slate-400"><?= $s[1] ?>%</span>
                            </div>
                            <div class="h-2 bg-slate-700/60 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r <?= $s[2] ?> rounded-full" style="width:<?= $s[1] ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="bg-gradient-to-r from-indigo-500/20 to-purple-500/20 border border-indigo-500/20 rounded-2xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-fire text-white"></i>
                        </div>
                        <div>
                            <div class="text-white font-bold text-lg">74% Avg</div>
                            <div class="text-slate-400 text-xs">Keep it up! You're on track.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave -->
        <div class="hero-content">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#020617"/>
            </svg>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="features-bg py-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white mb-4">Everything You Need to <span class="gradient-text">Pass</span></h2>
                <p class="text-slate-400 max-w-xl mx-auto">Designed specifically for the Philippine Civil Service Examination — Professional and Subprofessional levels.</p>
            </div>
        </div>

        <!-- Marquee -->
        <div class="marquee-wrapper">
            <div class="marquee-track" id="marqueeTrack">
                <?php
                $features = [
                    ['fas fa-brain',      'from-indigo-500 to-purple-500', 'Smart Practice',    'Adaptive questions that focus on your weak areas for faster improvement.'],
                    ['fas fa-chart-line', 'from-blue-500 to-cyan-500',     'Progress Tracking', 'Detailed analytics to monitor your performance across all subject areas.'],
                    ['fas fa-clock',      'from-purple-500 to-pink-500',   'Timed Exams',       'Simulate real exam conditions with timed mock tests and instant scoring.'],
                    ['fas fa-book-open',  'from-emerald-500 to-teal-500',  'Full Coverage',     'All CSE topics: Verbal, Numerical, Analytical, and General Information.'],
                    ['fas fa-mobile-alt', 'from-orange-500 to-red-500',    'Mobile Friendly',   'Review anytime, anywhere on any device — fully responsive design.'],
                    ['fas fa-trophy',     'from-yellow-500 to-orange-500', 'Leaderboards',      'Compete with other reviewees and stay motivated to reach the top.'],
                    ['fas fa-shield-alt', 'from-teal-500 to-emerald-500',  'Secure & Private',  'Your data and progress are safe, private, and always accessible.'],
                    ['fas fa-bolt',       'from-pink-500 to-rose-500',     'Instant Feedback',  'Get immediate explanations after every answer to learn faster.'],
                ];
                // render twice for seamless loop
                for ($r = 0; $r < 2; $r++):
                foreach ($features as $f): ?>
                <div class="marquee-card">
                    <div class="marquee-icon bg-gradient-to-br <?= $f[1] ?>">
                        <i class="<?= $f[0] ?> text-white"></i>
                    </div>
                    <h3><?= $f[2] ?></h3>
                    <p><?= $f[3] ?></p>
                </div>
                <?php endforeach; endfor; ?>
            </div>
        </div>
    </section>

    <!-- Subjects -->
    <section id="subjects" class="bg-slate-900/50 py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white mb-4">CSE <span class="gradient-text">Subject Areas</span></h2>
                <p class="text-slate-400">Complete coverage of all Civil Service Exam topics.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php
                $subjects = [
                    ['fas fa-comment-dots', 'from-indigo-600 to-purple-600', 'Verbal Ability',     'Grammar, vocabulary, reading comprehension, analogy, and correct usage.', '2,500+ questions'],
                    ['fas fa-calculator',   'from-blue-600 to-cyan-600',     'Numerical Ability',  'Basic math, word problems, number series, and data interpretation.',       '2,000+ questions'],
                    ['fas fa-puzzle-piece', 'from-purple-600 to-pink-600',   'Analytical Ability', 'Logical reasoning, data sufficiency, and abstract reasoning.',             '1,800+ questions'],
                    ['fas fa-globe-asia',   'from-emerald-600 to-teal-600',  'General Information','Philippine Constitution, government, history, and current events.',        '3,700+ questions'],
                ];
                foreach ($subjects as $s): ?>
                <div class="feature-card rounded-2xl p-6 flex gap-5">
                    <div class="w-14 h-14 bg-gradient-to-br <?= $s[1] ?> rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="<?= $s[0] ?> text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg mb-1"><?= $s[2] ?></h3>
                        <p class="text-slate-400 text-sm mb-3"><?= $s[3] ?></p>
                        <span class="text-xs font-semibold text-indigo-400 bg-indigo-500/10 px-3 py-1 rounded-full"><?= $s[4] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Stats / CTA -->
    <section id="stats" class="bg-slate-950 py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="glass-card rounded-3xl p-12 text-center" style="background:linear-gradient(135deg,rgba(79,70,229,0.15),rgba(124,58,237,0.1));border-color:rgba(129,140,248,0.2);">
                <h2 class="text-4xl font-black text-white mb-4">Ready to <span class="gradient-text">Pass the CSE?</span></h2>
                <p class="text-slate-400 mb-8 max-w-lg mx-auto">Join thousands of Filipinos who used CSEReviewer to pass the Civil Service Examination.</p>
                <a href="pages/register.php" class="btn-primary text-white font-bold px-10 py-4 rounded-2xl text-base inline-flex items-center gap-2">
                    <i class="fas fa-rocket"></i> Create Free Account
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/50 py-8">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-white text-xs"></i>
                </div>
                <span class="text-white font-bold text-sm">CSE<span class="gradient-text">Reviewer</span></span>
            </div>
            <p class="text-slate-500 text-sm">© <?= date('Y') ?> CSEReviewer. Built for Filipino civil servants.</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.marquee-card').forEach(card => {
            const track = document.getElementById('marqueeTrack');
            card.addEventListener('mouseenter', () => track.style.animationPlayState = 'paused');
            card.addEventListener('mouseleave', () => track.style.animationPlayState = 'running');
        });
    </script>
</body>
</html>
