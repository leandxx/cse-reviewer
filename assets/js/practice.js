(() => {
    const API = (typeof ROOT !== 'undefined' ? ROOT : '') + 'api/quiz.php';

    // Screens
    const subjectScreen  = document.getElementById('subjectScreen');
    const quizScreen     = document.getElementById('quizScreen');
    const resultsScreen  = document.getElementById('resultsScreen');

    // Subject selection
    const subjectCards   = document.querySelectorAll('.subject-card');
    const qtyBtns        = document.querySelectorAll('.qty-btn');
    const startBtn       = document.getElementById('startBtn');

    // Quiz UI
    const qProgress      = document.getElementById('qProgress');
    const qSubjectLabel  = document.getElementById('qSubjectLabel');
    const progressBar    = document.getElementById('progressBar');
    const qDifficulty    = document.getElementById('qDifficulty');
    const qText          = document.getElementById('qText');
    const hintWrap       = document.getElementById('hintWrap');
    const hintBtn        = document.getElementById('hintBtn');
    const hintText       = document.getElementById('hintText');
    const choicesWrap    = document.getElementById('choicesWrap');
    const explanationBox = document.getElementById('explanationBox');
    const explanationText= document.getElementById('explanationText');
    const nextBtn        = document.getElementById('nextBtn');

    // Results UI
    const resultIcon     = document.getElementById('resultIcon');
    const resultScore    = document.getElementById('resultScore');
    const resultDetail   = document.getElementById('resultDetail');
    const resultSubject  = document.getElementById('resultSubject');
    const retryBtn       = document.getElementById('retryBtn');

    let selectedSubject = null;
    let selectedQty     = 10;
    let sessionId       = null;
    let currentIndex    = 0;
    let totalQuestions  = 0;
    let currentAnswerId = null;
    let hintUsed        = false;

    const subjectLabels = {
        verbal: 'Verbal Ability', numerical: 'Numerical Ability',
        analytical: 'Analytical Ability', general: 'General Info'
    };

    const difficultyStyle = {
        easy:   'bg-emerald-500/20 text-emerald-400',
        medium: 'bg-yellow-500/20 text-yellow-400',
        hard:   'bg-red-500/20 text-red-400',
    };

    // ── Subject & qty selection ───────────────────────────────────────────────
    subjectCards.forEach(card => {
        card.addEventListener('click', () => {
            subjectCards.forEach(c => c.classList.remove('border-indigo-500', 'bg-indigo-500/10'));
            card.classList.add('border-indigo-500', 'bg-indigo-500/10');
            selectedSubject = card.dataset.subject;
            checkReady();
        });
    });

    qtyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            qtyBtns.forEach(b => b.classList.remove('border-indigo-500', 'text-white', 'bg-indigo-500/10'));
            btn.classList.add('border-indigo-500', 'text-white', 'bg-indigo-500/10');
            selectedQty = parseInt(btn.dataset.qty);
            checkReady();
        });
    });

    // Default qty selection
    qtyBtns[1].click();

    function checkReady() {
        const ready = selectedSubject && selectedQty;
        startBtn.disabled = !ready;
        startBtn.classList.toggle('opacity-50', !ready);
        startBtn.classList.toggle('cursor-not-allowed', !ready);
    }

    // ── Start quiz ────────────────────────────────────────────────────────────
    startBtn.addEventListener('click', async () => {
        startBtn.disabled = true;
        startBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Starting…';

        const res = await post({ action: 'start' }, { action: 'start', subject: selectedSubject, limit: selectedQty });

        if (res.error) {
            alert(res.error);
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Practice';
            return;
        }

        sessionId      = res.session_id;
        totalQuestions = res.total;
        currentIndex   = 0;

        subjectScreen.classList.add('hidden');
        quizScreen.classList.remove('hidden');
        loadQuestion();
    });

    // ── Load question ─────────────────────────────────────────────────────────
    async function loadQuestion() {
        hintUsed = false;
        explanationBox.classList.add('hidden');
        nextBtn.classList.add('hidden');
        hintText.classList.add('hidden');
        hintBtn.innerHTML = '<i class="fas fa-lightbulb"></i> Show Hint';
        choicesWrap.innerHTML = '<div class="text-slate-500 text-sm text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</div>';

        const data = await get({ action: 'question', session_id: sessionId, index: currentIndex });

        if (data.error) {
            choicesWrap.innerHTML = `<div class="text-red-400 text-sm text-center py-4">Error: ${data.error}</div>`;
            console.error('Question load error:', data.error);
            return;
        }

        if (data.done) { finishQuiz(); return; }

        currentAnswerId = data.answer_id;

        // Progress
        qProgress.textContent = `Question ${currentIndex + 1} of ${totalQuestions}`;
        qSubjectLabel.textContent = subjectLabels[data.subject] ?? data.subject;
        progressBar.style.width = ((currentIndex / totalQuestions) * 100) + '%';

        // Difficulty badge
        qDifficulty.className = 'text-xs font-semibold px-2 py-1 rounded-full ' + (difficultyStyle[data.difficulty] ?? '');
        qDifficulty.textContent = data.difficulty.charAt(0).toUpperCase() + data.difficulty.slice(1);

        qText.textContent = data.question;

        // Hint
        if (data.hint) {
            hintWrap.classList.remove('hidden');
            hintText.textContent = data.hint;
        } else {
            hintWrap.classList.add('hidden');
        }

        // Choices
        const choices = { a: data.choice_a, b: data.choice_b, c: data.choice_c, d: data.choice_d };
        choicesWrap.innerHTML = Object.entries(choices).map(([key, val]) => `
            <button class="choice-btn w-full text-left px-5 py-4 rounded-xl border border-slate-700 text-slate-300
                           hover:border-indigo-500 hover:text-white hover:bg-indigo-500/10 transition-all flex items-center gap-3"
                    data-choice="${key}">
                <span class="choice-letter w-7 h-7 rounded-lg bg-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                    ${key.toUpperCase()}
                </span>
                <span>${escHtml(val)}</span>
            </button>`).join('');

        choicesWrap.querySelectorAll('.choice-btn').forEach(btn => {
            btn.addEventListener('click', () => submitAnswer(btn.dataset.choice));
        });
    }

    // ── Hint ──────────────────────────────────────────────────────────────────
    hintBtn.addEventListener('click', () => {
        hintUsed = true;
        hintText.classList.remove('hidden');
        hintBtn.classList.add('hidden');
    });

    // ── Submit answer ─────────────────────────────────────────────────────────
    async function submitAnswer(chosen) {
        // Disable all choices
        choicesWrap.querySelectorAll('.choice-btn').forEach(b => b.disabled = true);

        const res = await post({ action: 'answer' }, {
            action: 'answer', answer_id: currentAnswerId, chosen, hint_used: hintUsed ? 1 : 0
        });

        // Highlight correct / wrong
        choicesWrap.querySelectorAll('.choice-btn').forEach(btn => {
            const isChosen  = btn.dataset.choice === chosen;
            const isCorrect = btn.dataset.choice === res.right_answer;

            if (isCorrect) {
                btn.classList.add('border-emerald-500', 'bg-emerald-500/10', 'text-emerald-300');
                btn.querySelector('.choice-letter').classList.add('bg-emerald-500', 'text-white');
            } else if (isChosen && !res.correct) {
                btn.classList.add('border-red-500', 'bg-red-500/10', 'text-red-300');
                btn.querySelector('.choice-letter').classList.add('bg-red-500', 'text-white');
            }
        });

        // Show explanation only on wrong answer
        if (!res.correct && res.explanation) {
            explanationText.textContent = res.explanation;
            explanationBox.classList.remove('hidden');
        }

        nextBtn.classList.remove('hidden');
        nextBtn.textContent = currentIndex + 1 < totalQuestions ? 'Next Question →' : 'See Results →';
    }

    // ── Next ──────────────────────────────────────────────────────────────────
    nextBtn.addEventListener('click', () => {
        currentIndex++;
        if (currentIndex >= totalQuestions) { finishQuiz(); return; }
        loadQuestion();
    });

    // ── Finish ────────────────────────────────────────────────────────────────
    async function finishQuiz() {
        const res = await post({ action: 'finish' }, { action: 'finish', session_id: sessionId });

        quizScreen.classList.add('hidden');
        resultsScreen.classList.remove('hidden');

        const pct = Math.round((res.correct / res.total) * 100);
        resultScore.textContent   = pct + '%';
        resultDetail.textContent  = `${res.correct} correct out of ${res.total} questions`;
        resultSubject.textContent = subjectLabels[res.subject] ?? res.subject;
        progressBar.style.width   = '100%';

        if (pct >= 80) {
            resultIcon.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl bg-emerald-500/20';
            resultIcon.innerHTML = '🎉';
        } else if (pct >= 60) {
            resultIcon.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl bg-yellow-500/20';
            resultIcon.innerHTML = '👍';
        } else {
            resultIcon.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl bg-red-500/20';
            resultIcon.innerHTML = '📚';
        }

        // XP earned
        if (res.xp_earned > 0) {
            const xpEl = document.getElementById('xpEarned');
            if (xpEl) { xpEl.textContent = '+' + res.xp_earned + ' XP'; xpEl.classList.remove('hidden'); }
        }

        // Review mistakes button
        const reviewBtn = document.getElementById('reviewBtn');
        if (reviewBtn) reviewBtn.href = 'review.php?session_id=' + sessionId;
    }

    // ── Retry ─────────────────────────────────────────────────────────────────
    retryBtn.addEventListener('click', () => {
        resultsScreen.classList.add('hidden');
        subjectScreen.classList.remove('hidden');
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Practice';
        // Re-select same subject
        subjectCards.forEach(c => {
            if (c.dataset.subject === selectedSubject)
                c.classList.add('border-indigo-500', 'bg-indigo-500/10');
        });
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    async function get(params) {
        try {
            const r = await fetch(API + '?' + new URLSearchParams(params));
            const text = await r.text();
            return JSON.parse(text);
        } catch (e) {
            console.error('GET error:', e);
            return { error: 'Request failed' };
        }
    }

    async function post(params, body) {
        try {
            const r = await fetch(API + '?' + new URLSearchParams(params), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(body),
            });
            const text = await r.text();
            return JSON.parse(text);
        } catch (e) {
            console.error('POST error:', e);
            return { error: 'Request failed' };
        }
    }

    const escHtml = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
})();
