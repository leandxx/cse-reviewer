(() => {
    const API = (typeof ROOT !== 'undefined' ? ROOT : '') + 'api/quiz.php';

    const startScreen      = document.getElementById('startScreen');
    const examScreen       = document.getElementById('examScreen');
    const resultsScreen    = document.getElementById('resultsScreen');
    const startExamBtn     = document.getElementById('startExamBtn');
    const retakeBtn        = document.getElementById('retakeBtn');

    // Exam UI
    const examProgress     = document.getElementById('examProgress');
    const examProgressBar  = document.getElementById('examProgressBar');
    const timerDisplay     = document.getElementById('timerDisplay');
    const timerBox         = document.getElementById('timerBox');
    const examQText        = document.getElementById('examQText');
    const examQSubject     = document.getElementById('examQSubject');
    const examQDiff        = document.getElementById('examQDiff');
    const examHintWrap     = document.getElementById('examHintWrap');
    const examHintBtn      = document.getElementById('examHintBtn');
    const examHintText     = document.getElementById('examHintText');
    const examChoicesWrap  = document.getElementById('examChoicesWrap');
    const examExplanation  = document.getElementById('examExplanationBox');
    const examExplanationT = document.getElementById('examExplanationText');
    const flagBtn          = document.getElementById('flagBtn');
    const flaggedBadge     = document.getElementById('flaggedBadge');
    const flagCount        = document.getElementById('flagCount');
    const prevBtn          = document.getElementById('prevBtn');
    const examNextBtn      = document.getElementById('examNextBtn');
    const submitExamBtn    = document.getElementById('submitExamBtn');
    const qNavGrid         = document.getElementById('qNavGrid');

    // Results
    const mockResultIcon   = document.getElementById('mockResultIcon');
    const mockScore        = document.getElementById('mockScore');
    const mockCorrect      = document.getElementById('mockCorrect');
    const mockWrong        = document.getElementById('mockWrong');
    const mockVerdict      = document.getElementById('mockVerdict');

    const TOTAL_TIME = 2 * 60 * 60; // 2 hours in seconds
    const MOCK_LIMIT = 100;

    let sessionId    = null;
    let totalQ       = 0;
    let currentIndex = 0;
    let timeLeft     = TOTAL_TIME;
    let timerInterval= null;
    let hintUsed     = false;

    // Per-question state cache
    let qCache = {}; // index -> { data, chosen, hintUsed, answered }
    let flagged = new Set();

    const subjectColors = {
        verbal:     'text-indigo-400 bg-indigo-500/10',
        numerical:  'text-blue-400 bg-blue-500/10',
        analytical: 'text-purple-400 bg-purple-500/10',
        general:    'text-emerald-400 bg-emerald-500/10',
    };
    const diffColors = { easy: 'text-emerald-400', medium: 'text-yellow-400', hard: 'text-red-400' };

    // ── Start ─────────────────────────────────────────────────────────────────
    startExamBtn.addEventListener('click', async () => {
        startExamBtn.disabled = true;
        startExamBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Preparing exam…';

        const res = await post({ action: 'start' }, { action: 'start', subject: 'all', limit: MOCK_LIMIT });

        if (res.error) {
            alert(res.error);
            startExamBtn.disabled = false;
            startExamBtn.innerHTML = '<i class="fas fa-play mr-2"></i> Start Mock Exam';
            return;
        }

        sessionId = res.session_id;
        totalQ    = res.total;
        currentIndex = 0;

        startScreen.classList.add('hidden');
        examScreen.classList.remove('hidden');

        buildNavGrid();
        startTimer();
        loadQuestion(0);
    });

    // ── Timer ─────────────────────────────────────────────────────────────────
    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                finishExam(true);
            }
            // Warn when 5 minutes left
            if (timeLeft === 300) {
                timerBox.classList.add('timer-warning');
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const h = Math.floor(timeLeft / 3600);
        const m = Math.floor((timeLeft % 3600) / 60);
        const s = timeLeft % 60;
        timerDisplay.textContent =
            String(h).padStart(1,'0') + ':' +
            String(m).padStart(2,'0') + ':' +
            String(s).padStart(2,'0');
    }

    // ── Load question ─────────────────────────────────────────────────────────
    async function loadQuestion(index) {
        currentIndex = index;
        examExplanation.classList.add('hidden');
        examHintText.classList.add('hidden');
        examHintBtn.innerHTML = '<i class="fas fa-lightbulb"></i> Show Hint';

        // Update progress
        examProgress.textContent = `${index + 1} / ${totalQ}`;
        examProgressBar.style.width = ((index / totalQ) * 100) + '%';

        // Nav/buttons
        prevBtn.classList.toggle('hidden', index === 0);
        updateNavButtons();

        // Use cache if available
        if (qCache[index]) {
            renderQuestion(qCache[index]);
            return;
        }

        examChoicesWrap.innerHTML = '<div class="text-slate-500 text-sm text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</div>';

        const data = await get({ action: 'question', session_id: sessionId, index });
        if (data.done || data.error) return;

        qCache[index] = { data, chosen: null, hintUsed: false, answered: false };
        renderQuestion(qCache[index]);
    }

    function renderQuestion(state) {
        const { data, chosen, answered } = state;

        examQSubject.className = 'text-xs font-semibold px-2 py-1 rounded-full ' + (subjectColors[data.subject] ?? '');
        examQSubject.textContent = data.subject;
        examQDiff.className = 'text-xs font-semibold ' + (diffColors[data.difficulty] ?? '');
        examQDiff.textContent = data.difficulty;
        examQText.textContent = data.question;

        // Flag button state
        const isFlagged = flagged.has(currentIndex);
        flagBtn.className = 'flex items-center gap-1.5 text-xs font-semibold transition-colors px-3 py-1.5 rounded-lg ' +
            (isFlagged ? 'text-yellow-400 bg-yellow-500/10' : 'text-slate-500 hover:text-yellow-400 hover:bg-yellow-500/10');
        flagBtn.innerHTML = `<i class="fas fa-flag"></i> ${isFlagged ? 'Flagged' : 'Flag'}`;

        // Hint
        if (data.hint) {
            examHintWrap.classList.remove('hidden');
            examHintText.textContent = data.hint;
            if (state.hintUsed) {
                examHintText.classList.remove('hidden');
                examHintBtn.classList.add('hidden');
            }
        } else {
            examHintWrap.classList.add('hidden');
        }

        // Choices
        const choices = { a: data.choice_a, b: data.choice_b, c: data.choice_c, d: data.choice_d };
        examChoicesWrap.innerHTML = Object.entries(choices).map(([key, val]) => {
            let cls = 'choice-btn w-full text-left px-5 py-4 rounded-xl border border-slate-700 text-slate-300 flex items-center gap-3 transition-all';
            let letterCls = 'choice-letter w-7 h-7 rounded-lg bg-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0';

            if (answered && chosen) {
                if (key === chosen && key !== data.right_answer) {
                    cls += ' border-red-500 bg-red-500/10 text-red-300';
                    letterCls = 'choice-letter w-7 h-7 rounded-lg bg-red-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0';
                } else if (key === data.right_answer) {
                    cls += ' border-emerald-500 bg-emerald-500/10 text-emerald-300';
                    letterCls = 'choice-letter w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0';
                }
            } else if (key === chosen) {
                cls += ' border-indigo-500 bg-indigo-500/10 text-white';
                letterCls = 'choice-letter w-7 h-7 rounded-lg bg-indigo-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0';
            } else {
                cls += ' hover:border-indigo-500 hover:text-white hover:bg-indigo-500/10';
            }

            return `<button class="${cls}" data-choice="${key}" ${answered ? 'disabled' : ''}>
                <span class="${letterCls}">${key.toUpperCase()}</span>
                <span>${escHtml(val)}</span>
            </button>`;
        }).join('');

        if (!answered) {
            examChoicesWrap.querySelectorAll('.choice-btn').forEach(btn => {
                btn.addEventListener('click', () => selectAnswer(btn.dataset.choice));
            });
        }

        // Show explanation if already answered wrong
        if (answered && state.data.explanation && chosen !== state.data.right_answer) {
            examExplanationT.textContent = state.data.explanation;
            examExplanation.classList.remove('hidden');
        }

        updateNavGrid();
        updateNavButtons();
    }

    // ── Select answer (saves locally, submits to API) ─────────────────────────
    async function selectAnswer(chosen) {
        const state = qCache[currentIndex];
        if (!state || state.answered) return;

        // Disable choices immediately
        examChoicesWrap.querySelectorAll('.choice-btn').forEach(b => b.disabled = true);
        state.chosen = chosen;

        const res = await post({ action: 'answer' }, {
            action: 'answer',
            answer_id: state.data.answer_id,
            chosen,
            hint_used: state.hintUsed ? 1 : 0,
        });

        state.answered   = true;
        state.data.right_answer = res.right_answer;
        state.data.explanation  = res.explanation;

        if (!res.correct && res.explanation) {
            examExplanationT.textContent = res.explanation;
            examExplanation.classList.remove('hidden');
        }

        // Re-render to show correct/wrong colors
        renderQuestion(state);
        updateNavGrid();
        updateNavButtons();
    }

    // ── Hint ──────────────────────────────────────────────────────────────────
    examHintBtn.addEventListener('click', () => {
        const state = qCache[currentIndex];
        if (state) state.hintUsed = true;
        examHintText.classList.remove('hidden');
        examHintBtn.classList.add('hidden');
    });

    // ── Flag ──────────────────────────────────────────────────────────────────
    flagBtn.addEventListener('click', () => {
        if (flagged.has(currentIndex)) flagged.delete(currentIndex);
        else flagged.add(currentIndex);

        const count = flagged.size;
        flagCount.textContent = count;
        flaggedBadge.classList.toggle('hidden', count === 0);

        renderQuestion(qCache[currentIndex]);
    });

    // ── Navigation ────────────────────────────────────────────────────────────
    prevBtn.addEventListener('click', () => { if (currentIndex > 0) loadQuestion(currentIndex - 1); });

    examNextBtn.addEventListener('click', () => {
        if (currentIndex < totalQ - 1) loadQuestion(currentIndex + 1);
    });

    function updateNavButtons() {
        const isLast = currentIndex === totalQ - 1;
        examNextBtn.classList.toggle('hidden', isLast);
        submitExamBtn.classList.toggle('hidden', !isLast);
    }

    // ── Nav grid ──────────────────────────────────────────────────────────────
    function buildNavGrid() {
        qNavGrid.innerHTML = Array.from({ length: totalQ }, (_, i) => `
            <button class="nav-cell w-full aspect-square rounded text-xs font-bold bg-slate-700 text-slate-400
                           hover:bg-indigo-500/30 hover:text-white transition-all" data-i="${i}">${i + 1}</button>
        `).join('');

        qNavGrid.querySelectorAll('.nav-cell').forEach(btn => {
            btn.addEventListener('click', () => loadQuestion(parseInt(btn.dataset.i)));
        });
    }

    function updateNavGrid() {
        qNavGrid.querySelectorAll('.nav-cell').forEach(btn => {
            const i = parseInt(btn.dataset.i);
            const state = qCache[i];
            const isCurrent = i === currentIndex;

            btn.className = 'nav-cell w-full aspect-square rounded text-xs font-bold transition-all ' +
                (isCurrent        ? 'ring-2 ring-indigo-400 ' : '') +
                (flagged.has(i)   ? 'bg-yellow-500 text-slate-900 ' :
                 state?.answered  ? 'bg-emerald-500 text-white ' :
                                    'bg-slate-700 text-slate-400 hover:bg-indigo-500/30 hover:text-white ');
        });
    }

    // ── Submit ────────────────────────────────────────────────────────────────
    submitExamBtn.addEventListener('click', async () => {
        const unanswered = totalQ - Object.values(qCache).filter(s => s?.answered).length;
        if (unanswered > 0) {
            const ok = confirm(`You have ${unanswered} unanswered question(s). Submit anyway?`);
            if (!ok) return;
        }
        finishExam(false);
    });

    async function finishExam(timeUp) {
        clearInterval(timerInterval);
        if (timeUp) alert('Time is up! Your exam has been submitted.');

        submitExamBtn.disabled = true;
        submitExamBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting…';

        const res = await post({ action: 'finish' }, { action: 'finish', session_id: sessionId });

        examScreen.classList.add('hidden');
        resultsScreen.classList.remove('hidden');

        const pct   = Math.round((res.correct / res.total) * 100);
        const wrong = res.total - res.correct;

        mockScore.textContent   = pct + '%';
        mockCorrect.textContent = res.correct;
        mockWrong.textContent   = wrong;

        if (pct >= 80) {
            mockResultIcon.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl bg-emerald-500/20';
            mockResultIcon.innerHTML = '🎉';
            mockVerdict.className    = 'rounded-2xl p-4 mb-8 text-sm font-semibold bg-emerald-500/10 text-emerald-400';
            mockVerdict.textContent  = 'Excellent! You passed the mock exam. Keep it up!';
        } else if (pct >= 60) {
            mockResultIcon.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl bg-yellow-500/20';
            mockResultIcon.innerHTML = '👍';
            mockVerdict.className    = 'rounded-2xl p-4 mb-8 text-sm font-semibold bg-yellow-500/10 text-yellow-400';
            mockVerdict.textContent  = 'Good effort! Review your weak areas and try again.';
        } else {
            mockResultIcon.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl bg-red-500/20';
            mockResultIcon.innerHTML = '📚';
            mockVerdict.className    = 'rounded-2xl p-4 mb-8 text-sm font-semibold bg-red-500/10 text-red-400';
            mockVerdict.textContent  = 'Keep studying! Practice more and retake the exam.';
        }
    }

    retakeBtn.addEventListener('click', () => location.reload());

    // ── Helpers ───────────────────────────────────────────────────────────────
    async function get(params) {
        const r = await fetch(API + '?' + new URLSearchParams(params));
        return r.json();
    }
    async function post(params, body) {
        const r = await fetch(API + '?' + new URLSearchParams(params), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(body),
        });
        return r.json();
    }
    const escHtml = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
})();
