(() => {
    const API = (typeof ROOT !== 'undefined' ? ROOT : '') + 'api/import.php';

    const dropZone      = document.getElementById('dropZone');
    const pdfInput      = document.getElementById('pdfInput');
    const uploadBtn     = document.getElementById('uploadBtn');
    const analyzeBtn    = document.getElementById('analyzeBtn');
    const saveBtn       = document.getElementById('saveBtn');
    const importMoreBtn = document.getElementById('importMoreBtn');

    const stepUpload    = document.getElementById('stepUpload');
    const stepAnalyze   = document.getElementById('stepAnalyze');
    const stepPreview   = document.getElementById('stepPreview');
    const stepSuccess   = document.getElementById('stepSuccess');
    const errorBox      = document.getElementById('errorBox');
    const errorText     = document.getElementById('errorText');

    const extractedText  = document.getElementById('extractedText');
    const analyzeStatus  = document.getElementById('analyzeStatus');
    const analyzeStatusT = document.getElementById('analyzeStatusText');
    const previewTable   = document.getElementById('previewTable');
    const qCount         = document.getElementById('qCount');
    const savedCount     = document.getElementById('savedCount');

    let selectedSubject = 'verbal';
    let extractedRawText = '';
    let analyzedQuestions = [];

    // ── Subject selection ─────────────────────────────────────────────────────
    document.querySelectorAll('.subject-pick').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.subject-pick').forEach(b =>
                b.classList.remove('border-indigo-500', 'text-white', 'bg-indigo-500/10'));
            btn.classList.add('border-indigo-500', 'text-white', 'bg-indigo-500/10');
            selectedSubject = btn.dataset.subject;
        });
    });
    // Default
    document.querySelector('.subject-pick').click();

    // ── Drag & drop ───────────────────────────────────────────────────────────
    dropZone.addEventListener('click', () => pdfInput.click());

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) setFile(file);
    });

    pdfInput.addEventListener('change', () => {
        if (pdfInput.files[0]) setFile(pdfInput.files[0]);
    });

    function setFile(file) {
        if (file.type !== 'application/pdf') { showError('Please select a PDF file.'); return; }
        dropZone.innerHTML = `
            <i class="fas fa-file-pdf text-4xl text-indigo-400 mb-4 block"></i>
            <p class="text-white font-semibold mb-1">${escHtml(file.name)}</p>
            <p class="text-slate-500 text-xs">${(file.size / 1024).toFixed(1)} KB</p>`;
        uploadBtn.disabled = false;
        uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        uploadBtn._file = file;
        hideError();
    }

    // ── Upload ────────────────────────────────────────────────────────────────
    uploadBtn.addEventListener('click', async () => {
        const file = uploadBtn._file;
        if (!file) return;

        setLoading(uploadBtn, 'Extracting text…');
        hideError();

        const form = new FormData();
        form.append('pdf', file);

        try {
            const r   = await fetch(API + '?action=upload', { method: 'POST', body: form });
            const res = await r.json();

            if (res.error) { showError(res.error); resetBtn(uploadBtn, '<i class="fas fa-upload mr-2"></i> Upload & Extract Text'); return; }

            extractedRawText = res.text;
            extractedText.textContent = res.text.substring(0, 1500) + (res.text.length > 1500 ? '\n\n[truncated for preview…]' : '');

            stepAnalyze.classList.remove('hidden');
            stepAnalyze.scrollIntoView({ behavior: 'smooth', block: 'start' });
            resetBtn(uploadBtn, '<i class="fas fa-check mr-2"></i> Text Extracted');
            uploadBtn.disabled = true;
        } catch (e) {
            showError('Upload failed. Please try again.');
            resetBtn(uploadBtn, '<i class="fas fa-upload mr-2"></i> Upload & Extract Text');
        }
    });

    // ── Analyze ───────────────────────────────────────────────────────────────
    analyzeBtn.addEventListener('click', async () => {
        setLoading(analyzeBtn, 'Analyzing…');
        analyzeStatus.classList.remove('hidden');
        hideError();

        const steps = [
            'Sending to Gemini AI…',
            'Extracting questions…',
            'Generating hints & explanations…',
            'Validating answers…',
        ];
        let si = 0;
        const ticker = setInterval(() => {
            analyzeStatusT.textContent = steps[Math.min(si++, steps.length - 1)];
        }, 3000);

        try {
            const body = new URLSearchParams({ action: 'analyze', text: extractedRawText, subject: selectedSubject });
            const r    = await fetch(API + '?action=analyze', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            const res = await r.json();
            clearInterval(ticker);
            analyzeStatus.classList.add('hidden');

            if (res.error) {
                showError(res.error);
                resetBtn(analyzeBtn, '<i class="fas fa-brain mr-2"></i> Analyze with Gemini AI');
                return;
            }

            analyzedQuestions = res.questions;
            renderPreview(analyzedQuestions);
            stepPreview.classList.remove('hidden');
            stepPreview.scrollIntoView({ behavior: 'smooth', block: 'start' });
            resetBtn(analyzeBtn, '<i class="fas fa-check mr-2"></i> Analysis Complete');
            analyzeBtn.disabled = true;
        } catch (e) {
            clearInterval(ticker);
            analyzeStatus.classList.add('hidden');
            showError('Analysis failed. Check your API key in .env.');
            resetBtn(analyzeBtn, '<i class="fas fa-brain mr-2"></i> Analyze with Gemini AI');
        }
    });

    // ── Preview ───────────────────────────────────────────────────────────────
    function renderPreview(questions) {
        qCount.textContent = questions.length + ' questions found';

        const subjectColors = {
            verbal: 'text-indigo-400 bg-indigo-500/10',
            numerical: 'text-blue-400 bg-blue-500/10',
            analytical: 'text-purple-400 bg-purple-500/10',
            general: 'text-emerald-400 bg-emerald-500/10',
        };
        const diffColors = {
            easy: 'text-emerald-400', medium: 'text-yellow-400', hard: 'text-red-400'
        };
        const letters = { a: 'A', b: 'B', c: 'C', d: 'D' };

        previewTable.innerHTML = questions.map((q, i) => `
            <div class="preview-card rounded-xl p-5 border border-slate-800">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-slate-500 text-xs font-bold">#${i + 1}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full ${subjectColors[q.subject] ?? ''}">${q.subject}</span>
                        <span class="text-xs font-semibold ${diffColors[q.difficulty] ?? ''}">${q.difficulty}</span>
                    </div>
                    <button class="remove-q text-slate-600 hover:text-red-400 transition-colors flex-shrink-0" data-index="${i}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-white text-sm font-medium mb-3">${escHtml(q.question)}</p>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    ${['a','b','c','d'].map(l => `
                    <div class="flex items-center gap-2 text-xs ${q.answer === l ? 'text-emerald-400 font-semibold' : 'text-slate-400'}">
                        <span class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0 text-xs font-bold
                            ${q.answer === l ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-400'}">${letters[l]}</span>
                        ${escHtml(q['choice_' + l])}
                    </div>`).join('')}
                </div>
                ${q.hint ? `<div class="flex items-start gap-2 text-xs text-yellow-400 bg-yellow-500/10 rounded-lg px-3 py-2 mb-2">
                    <i class="fas fa-lightbulb mt-0.5 flex-shrink-0"></i> ${escHtml(q.hint)}</div>` : ''}
                ${q.explanation ? `<div class="flex items-start gap-2 text-xs text-blue-400 bg-blue-500/10 rounded-lg px-3 py-2">
                    <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i> ${escHtml(q.explanation)}</div>` : ''}
            </div>`).join('');

        previewTable.querySelectorAll('.remove-q').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.dataset.index);
                analyzedQuestions.splice(idx, 1);
                renderPreview(analyzedQuestions);
            });
        });
    }

    // ── Save ──────────────────────────────────────────────────────────────────
    saveBtn.addEventListener('click', async () => {
        if (!analyzedQuestions.length) { showError('No questions to save.'); return; }
        setLoading(saveBtn, 'Saving…');

        try {
            const body = new URLSearchParams({ action: 'save', questions: JSON.stringify(analyzedQuestions) });
            const r    = await fetch(API + '?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            const res = await r.json();

            if (res.error) { showError(res.error); resetBtn(saveBtn, '<i class="fas fa-save mr-2"></i> Save All to Database'); return; }

            savedCount.textContent = `${res.saved} questions saved to your database successfully!`;
            stepPreview.classList.add('hidden');
            stepSuccess.classList.remove('hidden');
            stepSuccess.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) {
            showError('Save failed. Please try again.');
            resetBtn(saveBtn, '<i class="fas fa-save mr-2"></i> Save All to Database');
        }
    });

    // ── Import more ───────────────────────────────────────────────────────────
    importMoreBtn.addEventListener('click', () => location.reload());

    // ── Helpers ───────────────────────────────────────────────────────────────
    function showError(msg) {
        errorText.textContent = msg;
        errorBox.classList.remove('hidden');
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function hideError() { errorBox.classList.add('hidden'); }

    function setLoading(btn, text) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${text}`;
    }
    function resetBtn(btn, html) {
        btn.disabled = false;
        btn.innerHTML = html;
    }

    const escHtml = s => String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
})();
