(() => {
    const API = (typeof ROOT !== 'undefined' ? ROOT : '') + 'api/friends.php';

    const sidebar          = document.getElementById('leftSidebar');
    const toggleBtn        = document.getElementById('sbToggle');
    const searchInput      = document.getElementById('friendSearch');
    const searchDrop       = document.getElementById('searchResults');
    const friendsList      = document.getElementById('friendsList');
    const friendsEmpty     = document.getElementById('friendsEmpty');
    const friendCount      = document.getElementById('friendCount');
    const pendingSection   = document.getElementById('pendingSection');
    const pendingList      = document.getElementById('pendingList');
    const pendingCount     = document.getElementById('pendingCount');
    const pendingBadgeIcon = document.getElementById('pendingBadgeIcon');
    const pendingBadgeTab  = document.getElementById('pendingBadgeTab');
    const onlineCountBadge = document.getElementById('onlineCountBadge');
    const suggestionsList  = document.getElementById('suggestionsList');
    const suggestionsEmpty = document.getElementById('suggestionsEmpty');
    const suggestCount     = document.getElementById('suggestCount');

    // ── Modal ─────────────────────────────────────────────────────────────────
    // Inject modal HTML once
    const modalEl = document.createElement('div');
    modalEl.id = 'sbModal';
    modalEl.innerHTML = `
        <div class="sb-modal-backdrop"></div>
        <div class="sb-modal-box">
            <div class="sb-modal-icon"><i class="fas fa-question-circle"></i></div>
            <div class="sb-modal-msg"></div>
            <div class="sb-modal-actions">
                <button class="sb-modal-cancel">Cancel</button>
                <button class="sb-modal-confirm">Confirm</button>
            </div>
        </div>`;
    document.body.appendChild(modalEl);

    function showModal(message, confirmText = 'Confirm', danger = false) {
        return new Promise(resolve => {
            modalEl.querySelector('.sb-modal-msg').textContent = message;
            modalEl.querySelector('.sb-modal-confirm').textContent = confirmText;
            modalEl.querySelector('.sb-modal-confirm').className =
                'sb-modal-confirm' + (danger ? ' danger' : '');
            modalEl.classList.add('active');

            const cleanup = (result) => {
                modalEl.classList.remove('active');
                resolve(result);
            };
            modalEl.querySelector('.sb-modal-confirm').onclick = () => cleanup(true);
            modalEl.querySelector('.sb-modal-cancel').onclick  = () => cleanup(false);
            modalEl.querySelector('.sb-modal-backdrop').onclick = () => cleanup(false);
        });
    }

    // ── Nav height sync ───────────────────────────────────────────────────────
    const nav = document.querySelector('nav');
    function syncNavHeight() {
        const h = nav ? nav.offsetHeight : 65;
        document.documentElement.style.setProperty('--nav-h', h + 'px');
        sidebar.style.top    = h + 'px';
        sidebar.style.height = 'calc(100vh - ' + h + 'px)';
    }
    syncNavHeight();
    window.addEventListener('resize', syncNavHeight);

    function applySidebarWidth() {
        const collapsed = sidebar.dataset.collapsed === 'true';
        const w = collapsed ? '52px' : '280px';
        sidebar.style.width = w;
        const main = document.querySelector('.dashboard-main');
        if (main) main.style.marginLeft = w;
    }

    // ── Collapse / expand ─────────────────────────────────────────────────────
    const STORAGE_KEY = 'leftSidebarCollapsed';
    if (localStorage.getItem(STORAGE_KEY) === 'true') sidebar.dataset.collapsed = 'true';
    applySidebarWidth();

    toggleBtn.addEventListener('click', () => {
        const isCollapsed = sidebar.dataset.collapsed === 'true';
        sidebar.dataset.collapsed = isCollapsed ? 'false' : 'true';
        localStorage.setItem(STORAGE_KEY, String(!isCollapsed));
        applySidebarWidth();
    });

    // ── Tab switching ─────────────────────────────────────────────────────────
    function activateTab(tabName) {
        document.querySelectorAll('.sb-tab-btn').forEach(b =>
            b.classList.toggle('active', b.dataset.tab === tabName));
        document.querySelectorAll('.sb-tab-icon').forEach(b =>
            b.classList.toggle('active', b.dataset.tab === tabName));
        document.querySelectorAll('.sb-pane').forEach(p =>
            p.classList.toggle('active', p.id === 'pane-' + tabName));
    }

    document.querySelectorAll('.sb-tab-btn, .sb-tab-icon').forEach(btn => {
        btn.addEventListener('click', () => {
            if (sidebar.dataset.collapsed === 'true') {
                sidebar.dataset.collapsed = 'false';
                localStorage.setItem(STORAGE_KEY, 'false');
                applySidebarWidth();
            }
            activateTab(btn.dataset.tab);
        });
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    const initials = name =>
        name.trim().split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();

    const escHtml = str =>
        str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    async function api(params = {}, method = 'GET', body = null) {
        const url  = API + '?' + new URLSearchParams(params);
        const opts = { method };
        if (body) {
            opts.body    = new URLSearchParams(body);
            opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
        }
        const r = await fetch(url, opts);
        return r.json();
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────────
    function ping() { api({ action: 'ping' }); }
    ping();
    setInterval(ping, 60_000);

    // ── Render friends ────────────────────────────────────────────────────────
    function renderFriends(friends) {
        const onlineCount = friends.filter(f => f.is_online == 1).length;
        onlineCountBadge.textContent = onlineCount + ' online';
        friendCount.textContent = friends.length;

        if (!friends.length) {
            friendsList.innerHTML = '';
            friendsList.appendChild(friendsEmpty);
            return;
        }

        friendsList.innerHTML = friends.map(f => {
            const online = f.is_online == 1;
            return `
            <div class="friend-row">
                <div class="f-avatar-wrap">
                    <div class="f-avatar">${initials(f.full_name)}</div>
                    <span class="f-status-dot ${online ? 'online' : ''}"></span>
                </div>
                <div class="f-info">
                    <div class="f-name">${escHtml(f.full_name)}</div>
                    <div class="f-status-text ${online ? 'online' : ''}">${online ? 'Online' : 'Offline'}</div>
                </div>
                <button class="btn-remove" data-id="${f.id}" title="Remove friend">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        }).join('');

        friendsList.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', async () => {
                const ok = await showModal('Remove this friend?', 'Remove', true);
                if (!ok) return;
                btn.disabled = true;
                await api({ action: 'remove' }, 'POST', { action: 'remove', friend_id: btn.dataset.id });
                refresh();
            });
        });
    }

    // ── Render pending ────────────────────────────────────────────────────────
    function renderPending(reqs) {
        const count = reqs.length;
        pendingCount.textContent = count;
        [pendingBadgeIcon, pendingBadgeTab].forEach(el => {
            el.textContent = count;
            el.classList.toggle('hidden', count === 0);
        });

        if (!count) { pendingSection.classList.add('hidden'); return; }
        pendingSection.classList.remove('hidden');

        pendingList.innerHTML = reqs.map(r => `
            <div class="pending-row" data-req="${r.request_id}">
                <div class="f-avatar-wrap">
                    <div class="f-avatar">${initials(r.full_name)}</div>
                </div>
                <div class="f-info">
                    <div class="f-name">${escHtml(r.full_name)}</div>
                    <div class="f-status-text">wants to be friends</div>
                </div>
                <div class="pending-actions">
                    <button class="btn-accept"  data-req="${r.request_id}"><i class="fas fa-check"></i></button>
                    <button class="btn-decline" data-req="${r.request_id}"><i class="fas fa-times"></i></button>
                </div>
            </div>`).join('');

        pendingList.querySelectorAll('.btn-accept').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (btn.disabled) return;
                const ok = await showModal('Accept this friend request?', 'Accept');
                if (!ok) return;
                btn.disabled = true; // lock immediately after confirm
                await api({ action: 'accept' }, 'POST', { action: 'accept', request_id: btn.dataset.req });
                refresh();
            });
        });

        pendingList.querySelectorAll('.btn-decline').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (btn.disabled) return;
                const ok = await showModal('Decline this friend request?', 'Decline', true);
                if (!ok) return;
                btn.disabled = true;
                await api({ action: 'decline' }, 'POST', { action: 'decline', request_id: btn.dataset.req });
                refresh();
            });
        });
    }

    // ── Render suggestions ────────────────────────────────────────────────────
    function renderSuggestions(users) {
        suggestCount.textContent = users.length;

        if (!users.length) {
            suggestionsList.innerHTML = '';
            suggestionsList.appendChild(suggestionsEmpty);
            return;
        }

        suggestionsList.innerHTML = users.map(u => `
            <div class="suggest-row">
                <div class="f-avatar-wrap">
                    <div class="f-avatar">${initials(u.full_name)}</div>
                    <span class="f-status-dot ${u.is_online == 1 ? 'online' : ''}"></span>
                </div>
                <div class="f-info">
                    <div class="f-name">${escHtml(u.full_name)}</div>
                    <div class="f-status-text ${u.is_online == 1 ? 'online' : ''}">${u.is_online == 1 ? 'Online' : 'Offline'}</div>
                </div>
                <button class="btn-add-suggest" data-id="${u.id}">
                    <i class="fas fa-user-plus"></i> Add
                </button>
            </div>`).join('');

        suggestionsList.querySelectorAll('.btn-add-suggest').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (btn.disabled) return;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-check"></i> Sent';
                await api({ action: 'add' }, 'POST', { action: 'add', to: btn.dataset.id });
                setTimeout(refresh, 800);
            });
        });
    }

    // ── Refresh ───────────────────────────────────────────────────────────────
    async function refresh() {
        const [friends, pending, suggestions] = await Promise.all([
            api({ action: 'list' }),
            api({ action: 'pending' }),
            api({ action: 'suggestions' }),
        ]);
        renderFriends(Array.isArray(friends) ? friends : []);
        renderPending(Array.isArray(pending) ? pending : []);
        renderSuggestions(Array.isArray(suggestions) ? suggestions : []);
    }

    refresh();
    setInterval(refresh, 30_000);

    // ── Load XP / progress stats into leaderboard pane ────────────────────────
    const STATS_API = (typeof ROOT !== 'undefined' ? ROOT : '') + 'api/stats.php';
    const subjectLabels = { verbal:'Verbal', numerical:'Numerical', analytical:'Analytical', general:'General Info', all:'Mock Exam' };
    const subjectColors = { verbal:'#818cf8', numerical:'#38bdf8', analytical:'#c084fc', general:'#34d399', all:'#f472b6' };

    async function loadStats() {
        try {
            const r = await fetch(STATS_API + '?action=stats');
            const d = await r.json();
            if (d.error) return;

            document.getElementById('sb-level').textContent   = d.level;
            document.getElementById('sb-xp').textContent      = d.xp.toLocaleString();
            document.getElementById('sb-xp-next').textContent = d.xp_in_level;
            document.getElementById('sb-xp-bar').style.width  = (d.xp_in_level / d.xp_for_next * 100) + '%';

            const hist = document.getElementById('sb-history');
            if (!d.history.length) {
                hist.innerHTML = '<div class="sb-empty" style="padding:16px;"><p>No sessions yet.</p></div>';
                return;
            }
            hist.innerHTML = d.history.map(s => {
                const pct   = Math.round(s.correct / s.total * 100);
                const color = pct >= 80 ? '#4ade80' : pct >= 60 ? '#facc15' : '#f87171';
                const bar   = pct >= 80 ? '#22c55e' : pct >= 60 ? '#eab308' : '#ef4444';
                return `
                <a href="review.php?session_id=${s.id}" style="display:flex;align-items:center;gap:8px;padding:6px 4px;border-radius:8px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
                    <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:20px;background:rgba(255,255,255,0.06);color:${subjectColors[s.subject] ?? '#94a3b8'};flex-shrink:0;">${subjectLabels[s.subject] ?? s.subject}</span>
                    <div style="flex:1;min-width:0;">
                        <div style="height:4px;background:#1e293b;border-radius:99px;overflow:hidden;">
                            <div style="height:100%;background:${bar};border-radius:99px;width:${pct}%;"></div>
                        </div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:${color};flex-shrink:0;">${pct}%</span>
                </a>`;
            }).join('');
        } catch(e) {}
    }

    loadStats();
    setInterval(loadStats, 60_000);

    // ── Search ────────────────────────────────────────────────────────────────
    let searchTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = searchInput.value.trim();
        if (!q) { searchDrop.classList.add('hidden'); return; }

        searchTimer = setTimeout(async () => {
            const results = await api({ action: 'search', q });
            if (!Array.isArray(results) || !results.length) {
                searchDrop.innerHTML = `<div class="search-result-item"><span class="s-name" style="color:#475569">No users found</span></div>`;
            } else {
                searchDrop.innerHTML = results.map(u => {
                    let action = '';
                    if      (u.rel === 'friend')   action = `<span class="rel-tag">Friends</span>`;
                    else if (u.rel === 'sent')      action = `<button class="btn-add-friend" disabled>Sent</button>`;
                    else if (u.rel === 'received')  action = `<button class="btn-add-friend" data-accept="${u.id}">Accept</button>`;
                    else                            action = `<button class="btn-add-friend" data-add="${u.id}">Add</button>`;
                    return `
                    <div class="search-result-item">
                        <div class="s-avatar">${initials(u.full_name)}</div>
                        <span class="s-name">${escHtml(u.full_name)}</span>
                        ${action}
                    </div>`;
                }).join('');
            }
            searchDrop.classList.remove('hidden');

            searchDrop.querySelectorAll('[data-add]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (btn.disabled) return;
                    btn.disabled = true;
                    btn.textContent = 'Sent';
                    await api({ action: 'add' }, 'POST', { action: 'add', to: btn.dataset.add });
                });
            });

            searchDrop.querySelectorAll('[data-accept]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (btn.disabled) return;
                    btn.disabled = true;
                    const pending = await api({ action: 'pending' });
                    const req = Array.isArray(pending) && pending.find(r => String(r.id) === btn.dataset.accept);
                    if (req) {
                        await api({ action: 'accept' }, 'POST', { action: 'accept', request_id: req.request_id });
                        refresh();
                        searchInput.value = '';
                        searchDrop.classList.add('hidden');
                    }
                });
            });
        }, 280);
    });

    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target) && !searchDrop.contains(e.target))
            searchDrop.classList.add('hidden');
    });
})();
