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
        if (!isMobile()) {
            sidebar.style.top    = h + 'px';
            sidebar.style.height = 'calc(100vh - ' + h + 'px)';
        } else {
            sidebar.style.top    = '';
            sidebar.style.height = '';
        }
    }
    syncNavHeight();
    window.addEventListener('resize', () => { syncNavHeight(); applyLayout(); });

    const overlay = document.getElementById('sbOverlay');
    const hamburger = document.getElementById('sbHamburger');

    function isMobile() { return window.innerWidth < 768; }

    function applyLayout() {
        const main = document.querySelector('.dashboard-main');
        if (isMobile()) {
            sidebar.style.width = '';
            sidebar.style.zIndex = '';
            if (main) main.style.marginLeft = '0';
        } else {
            applySidebarWidth();
        }
    }

    function openMobileSidebar() {
        sidebar.classList.add('mobile-open');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburger) {
        hamburger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-open')) closeMobileSidebar();
            else openMobileSidebar();
        });
    }
    if (overlay) overlay.addEventListener('click', closeMobileSidebar);

    // Prevent clicks inside sidebar from closing it
    sidebar.addEventListener('click', (e) => e.stopPropagation());

    function applySidebarWidth() {
        if (isMobile()) return;
        const collapsed = sidebar.dataset.collapsed === 'true';
        const w = collapsed ? '52px' : '280px';
        sidebar.style.width = w;
        const main = document.querySelector('.dashboard-main');
        if (main) main.style.marginLeft = w;
    }

    // ── Collapse / expand ─────────────────────────────────────────────────────
    const STORAGE_KEY = 'leftSidebarCollapsed';
    if (localStorage.getItem(STORAGE_KEY) === 'true') sidebar.dataset.collapsed = 'true';
    applyLayout();

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
            if (!isMobile() && sidebar.dataset.collapsed === 'true') {
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

    // ── Load leaderboard ────────────────────────────────────────────────────────
    const STATS_API = (typeof ROOT !== 'undefined' ? ROOT : '') + 'api/stats.php';

    async function loadLeaderboard() {
        try {
            const r = await fetch(STATS_API + '?action=leaderboard');
            const data = await r.json();
            if (!Array.isArray(data)) return;

            const el = document.getElementById('sb-leaderboard');
            if (!el) return;

            const medals = ['🥇','🥈','🥉'];
            el.innerHTML = data.map((u, i) => {
                const rank = i + 1;
                const pct  = u.xp % 100;
                const isMe = u.is_me;
                return `
                <div style="display:flex;align-items:center;gap:8px;padding:7px 4px;border-radius:10px;${isMe ? 'background:rgba(99,102,241,0.12);' : ''}">
                    <div style="width:22px;text-align:center;font-size:${rank <= 3 ? '16px' : '11px'};font-weight:700;color:#475569;flex-shrink:0;">
                        ${rank <= 3 ? medals[rank-1] : rank}
                    </div>
                    <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;">
                        ${u.full_name.trim().split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase()}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:11px;font-weight:${isMe ? '700' : '600'};color:${isMe ? '#818cf8' : '#e2e8f0'};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            ${escHtml(u.full_name)}${isMe ? ' (you)' : ''}
                        </div>
                        <div style="height:3px;background:#0f172a;border-radius:99px;overflow:hidden;margin-top:3px;">
                            <div style="height:100%;background:linear-gradient(90deg,#f59e0b,#f97316);border-radius:99px;width:${pct}%;"></div>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:11px;font-weight:700;color:#f59e0b;">Lv.${u.level}</div>
                        <div style="font-size:9px;color:#475569;">${u.xp} XP</div>
                    </div>
                </div>`;
            }).join('');
        } catch(e) {}
    }

    loadLeaderboard();
    setInterval(loadLeaderboard, 60_000);

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
