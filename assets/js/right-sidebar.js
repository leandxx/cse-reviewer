(() => {
    const API = 'api/friends.php';

    const sidebar       = document.getElementById('rightSidebar');
    const toggleBtn     = document.getElementById('sbToggle');
    const searchInput   = document.getElementById('friendSearch');
    const searchDrop    = document.getElementById('searchResults');
    const friendsList   = document.getElementById('friendsList');
    const friendsEmpty  = document.getElementById('friendsEmpty');
    const friendCount   = document.getElementById('friendCount');
    const pendingSection    = document.getElementById('pendingSection');
    const pendingList       = document.getElementById('pendingList');
    const pendingCount      = document.getElementById('pendingCount');
    const pendingBadgeIcon  = document.getElementById('pendingBadgeIcon');
    const pendingBadgeTab   = document.getElementById('pendingBadgeTab');
    const onlineCountBadge  = document.getElementById('onlineCountBadge');

    // ── Sync --nav-h to actual nav height ────────────────────────────────────
    const nav = document.querySelector('nav');
    function syncNavHeight() {
        if (nav) document.documentElement.style.setProperty('--nav-h', nav.offsetHeight + 'px');
    }
    syncNavHeight();
    window.addEventListener('resize', syncNavHeight);

    // ── Collapse / expand ────────────────────────────────────────────────────
    const STORAGE_KEY = 'rightSidebarCollapsed';
    if (localStorage.getItem(STORAGE_KEY) === 'true') {
        sidebar.dataset.collapsed = 'true';
    }

    toggleBtn.addEventListener('click', () => {
        const isCollapsed = sidebar.dataset.collapsed === 'true';
        sidebar.dataset.collapsed = isCollapsed ? 'false' : 'true';
        localStorage.setItem(STORAGE_KEY, String(!isCollapsed));
    });

    // ── Tab switching ─────────────────────────────────────────────────────────
    function activateTab(tabName) {
        // panel tab buttons
        document.querySelectorAll('.sb-tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabName);
        });
        // icon strip buttons
        document.querySelectorAll('.sb-tab-icon').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabName);
        });
        // panes
        document.querySelectorAll('.sb-pane').forEach(pane => {
            pane.classList.toggle('active', pane.id === 'pane-' + tabName);
        });
    }

    document.querySelectorAll('.sb-tab-btn, .sb-tab-icon').forEach(btn => {
        btn.addEventListener('click', () => {
            // if collapsed and clicking icon strip, expand first
            if (sidebar.dataset.collapsed === 'true') {
                sidebar.dataset.collapsed = 'false';
                localStorage.setItem(STORAGE_KEY, 'false');
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
        const url = API + '?' + new URLSearchParams(params);
        const opts = { method };
        if (body) {
            opts.body = new URLSearchParams(body);
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
                if (!confirm('Remove this friend?')) return;
                await api({ action: 'remove' }, 'POST', { action: 'remove', friend_id: btn.dataset.id });
                refresh();
            });
        });
    }

    // ── Render pending ────────────────────────────────────────────────────────
    function renderPending(reqs) {
        const count = reqs.length;
        pendingCount.textContent = count;

        // badges
        [pendingBadgeIcon, pendingBadgeTab].forEach(el => {
            el.textContent = count;
            el.classList.toggle('hidden', count === 0);
        });

        if (!count) {
            pendingSection.classList.add('hidden');
            return;
        }
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
                await api({ action: 'accept' }, 'POST', { action: 'accept', request_id: btn.dataset.req });
                refresh();
            });
        });
        pendingList.querySelectorAll('.btn-decline').forEach(btn => {
            btn.addEventListener('click', async () => {
                await api({ action: 'decline' }, 'POST', { action: 'decline', request_id: btn.dataset.req });
                refresh();
            });
        });
    }

    // ── Refresh ───────────────────────────────────────────────────────────────
    async function refresh() {
        const [friends, pending] = await Promise.all([
            api({ action: 'list' }),
            api({ action: 'pending' }),
        ]);
        renderFriends(Array.isArray(friends) ? friends : []);
        renderPending(Array.isArray(pending) ? pending : []);
    }

    refresh();
    setInterval(refresh, 30_000);

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
                    btn.disabled = true;
                    btn.textContent = 'Sent';
                    await api({ action: 'add' }, 'POST', { action: 'add', to: btn.dataset.add });
                });
            });
            searchDrop.querySelectorAll('[data-accept]').forEach(btn => {
                btn.addEventListener('click', async () => {
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
        if (!searchInput.contains(e.target) && !searchDrop.contains(e.target)) {
            searchDrop.classList.add('hidden');
        }
    });
})();
