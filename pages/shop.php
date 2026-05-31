<?php
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pageTitle = 'Coin Shop — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css', 'assets/css/shop.css'];
$root      = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../includes/head.php'; ?></head>
<body class="shop-page min-h-screen">

<div class="ember-field" id="emberField"></div>

<nav class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <button id="sbHamburger" class="md:hidden text-slate-400 hover:text-white mr-1" aria-label="Open sidebar">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <img src="../assets/img/logo.png" alt="CSE Reviewer Logo" class="w-9 h-9 rounded-xl object-contain">
        <span class="text-white font-bold text-lg">CSE<span class="gradient-text">Reviewer</span></span>
        <span id="gmBadge" class="hidden gm-badge"><i class="fas fa-crown mr-1"></i>Game Master</span>
    </div>
    <div class="flex items-center gap-4">
        <div class="shop-coin-pill" style="margin-bottom:0;">
            <i class="fas fa-coins"></i>
            <span id="coinBalance">—</span> coins
        </div>
        <button id="inventoryBtn" class="inventory-nav-btn">
            <i class="fas fa-box-open mr-1"></i><span class="hidden sm:inline">Inventory</span>
        </button>
        <a href="dashboard.php" class="text-orange-700 hover:text-orange-400 text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-home"></i><span class="hidden sm:inline"> Dashboard</span>
        </a>
        <a href="../auth/logout.php" class="text-orange-700 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline"> Logout</span>
        </a>
    </div>
</nav>

<?php include '../includes/left-sidebar.php'; ?>

<div class="dashboard-main" style="position:relative;z-index:1;">
    <div class="max-w-4xl mx-auto px-6 pb-16">

        <!-- Hero -->
        <div class="shop-hero">
            <div class="shop-hero-title">🔥 Coin Shop</div>
            <p class="shop-hero-sub">Spend your hard-earned coins. Flex on the leaderboard.</p>
            <div class="flex justify-center mt-5">
                <div class="shop-coin-pill">
                    <i class="fas fa-coins"></i>
                    <span id="coinBalanceHero">—</span> coins available
                </div>
            </div>
        </div>

        <!-- Reward info -->
        <div class="shop-reward-banner mb-8">
            <div class="reward-banner-title">
                <i class="fas fa-fire"></i> How to Earn Coins
            </div>
            <div class="reward-tiers">
                <div class="reward-tier"><span class="tier-pct">90–100%</span><span class="tier-coins">🪙 50</span></div>
                <div class="reward-tier"><span class="tier-pct">75–89%</span><span class="tier-coins">🪙 30</span></div>
                <div class="reward-tier"><span class="tier-pct">60–74%</span><span class="tier-coins">🪙 15</span></div>
                <div class="reward-tier"><span class="tier-pct">50–59%</span><span class="tier-coins">🪙 8</span></div>
                <div class="reward-tier"><span class="tier-pct">Below 50%</span><span class="tier-coins">🪙 3</span></div>
                <div class="reward-tier bonus"><span class="tier-pct">Mock Exam bonus</span><span class="tier-coins">🪙 +10</span></div>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="shop-tabs">
            <button class="shop-tab active" data-filter="all"><i class="fas fa-fire mr-1"></i>All</button>
            <button class="shop-tab" data-filter="fire">🔥 Fire</button>
            <button class="shop-tab" data-filter="title"><i class="fas fa-tag mr-1"></i>Titles</button>
            <button class="shop-tab" data-filter="name_color"><i class="fas fa-palette mr-1"></i>Name Colors</button>
            <button class="shop-tab" data-filter="name_bg"><i class="fas fa-fill-drip mr-1"></i>Backgrounds</button>
        </div>

        <!-- Section label -->
        <div class="shop-section-label"><i class="fas fa-store"></i> Items</div>

        <!-- Grid -->
        <div id="shopGrid" class="shop-grid">
            <div style="grid-column:1/-1;text-align:center;padding:48px 0;color:#78350f;">
                <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:10px;display:block;"></i>
                Loading shop…
            </div>
        </div>

    </div>
</div>

<!-- ── Inventory Modal ──────────────────────────────────────────────────────── -->
<div id="inventoryModal" class="inv-modal-overlay hidden">
    <div class="inv-modal">
        <div class="inv-modal-header">
            <span><i class="fas fa-box-open mr-2"></i>My Inventory</span>
            <button id="invClose" class="inv-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="inv-tabs">
            <button class="inv-tab active" data-type="all">All</button>
            <button class="inv-tab" data-type="title">Titles</button>
            <button class="inv-tab" data-type="name_color">Name Colors</button>
            <button class="inv-tab" data-type="name_bg">Backgrounds</button>
        </div>
        <div id="invGrid" class="inv-grid">
            <div style="text-align:center;padding:32px;color:#475569;">
                <i class="fas fa-spinner fa-spin" style="font-size:20px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- ── GM Gift Modal ────────────────────────────────────────────────────────── -->
<div id="giftModal" class="inv-modal-overlay hidden">
    <div class="inv-modal" style="max-width:400px;">
        <div class="inv-modal-header">
            <span><i class="fas fa-gift mr-2"></i>Gift Item</span>
            <button id="giftClose" class="inv-close"><i class="fas fa-times"></i></button>
        </div>
        <div style="padding:20px;">
            <p id="giftItemName" style="color:#e2e8f0;font-weight:700;font-size:14px;margin-bottom:14px;"></p>
            <div style="position:relative;margin-bottom:10px;">
                <input id="giftSearch" type="text" placeholder="Search player name…" class="gm-search-input">
            </div>
            <div id="giftUserResults" class="gm-user-list"></div>
            <p id="giftStatus" style="font-size:12px;margin-top:10px;min-height:18px;"></p>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="shopToast" class="shop-toast hidden"></div>

<script>const ROOT = '../';</script>
<script src="../assets/js/left-sidebar.js"></script>
<script>
(() => {
    const API = ROOT + 'api/coins.php';
    let allItems = [], currentFilter = 'all', myCoins = 0, isGM = false;
    let giftTargetItemId = null;

    const grid    = document.getElementById('shopGrid');
    const balNav  = document.getElementById('coinBalance');
    const balHero = document.getElementById('coinBalanceHero');
    const toast   = document.getElementById('shopToast');

    function setBalance(n) {
        myCoins = n;
        const fmt = n.toLocaleString();
        balNav.textContent  = fmt;
        balHero.textContent = fmt;
    }

    function showToast(msg, ok = true) {
        toast.textContent = msg;
        toast.className = 'shop-toast ' + (ok ? 'ok' : 'err');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.add('hidden'), 2800);
    }

    const escHtml = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

    async function loadBalance() {
        const r = await fetch(API + '?action=balance').then(r => r.json());
        setBalance(r.coins ?? 0);
        isGM = r.is_gm === true;
        if (isGM) document.getElementById('gmBadge').classList.remove('hidden');
    }

    async function loadShop() {
        const items = await fetch(API + '?action=shop').then(r => r.json());
        allItems = Array.isArray(items) ? items : [];
        render();
    }

    function render() {
        let filtered;
        if (currentFilter === 'all')       filtered = allItems;
        else if (currentFilter === 'fire') filtered = allItems.filter(i => i.theme === 'fire');
        else                               filtered = allItems.filter(i => i.type === currentFilter);

        if (!filtered.length) {
            grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:48px 0;color:#475569;">No items here.</p>';
            return;
        }

        const typeLabel = { title: 'Title', name_color: 'Name Color', name_bg: 'Background' };
        const typeIcon  = { title: 'fa-tag', name_color: 'fa-palette', name_bg: 'fa-fill-drip' };

        grid.innerHTML = filtered.map(item => {
            const isFire    = item.theme === 'fire';
            const canAfford = isGM || myCoins >= item.price;
            const fireClass = isFire ? 'theme-fire' : '';

            const fireBadge = isFire
                ? `<span class="fire-badge"><i class="fas fa-fire"></i> Fire</span>`
                : '';

            let btn = '';
            if (item.owned && item.equipped) {
                btn = `<button class="shop-btn equipped"><i class="fas fa-check-circle mr-1"></i>Equipped</button>`;
            } else if (item.owned) {
                btn = `<button class="shop-btn equip" data-equip="${item.id}"><i class="fas fa-wand-magic-sparkles mr-1"></i>Equip</button>`;
            } else {
                const label = isGM ? `<i class="fas fa-crown mr-1"></i>Get Free` : `<i class="fas fa-coins mr-1"></i>${Number(item.price).toLocaleString()} coins`;
                btn = `<button class="shop-btn buy ${canAfford ? '' : 'cant-afford'}" data-buy="${item.id}" ${canAfford ? '' : 'disabled'}>${label}</button>`;
            }

            const gmGift = isGM
                ? `<button class="shop-btn gm-gift" data-gift="${item.id}" data-name="${escHtml(item.name)}"><i class="fas fa-gift mr-1"></i>Gift to Player</button>`
                : '';

            return `
            <div class="shop-card ${fireClass} ${item.equipped ? 'is-equipped' : ''}">
                <div class="shop-card-type"><i class="fas ${typeIcon[item.type]} mr-1"></i>${typeLabel[item.type]}${fireBadge}</div>
                <div class="shop-preview" style="${item.preview_css ?? ''}">${escHtml(item.name)}</div>
                <div class="shop-card-name">${escHtml(item.name)}</div>
                <div class="shop-card-desc">${escHtml(item.description ?? '')}</div>
                ${btn}
                ${gmGift}
            </div>`;
        }).join('');

        grid.querySelectorAll('[data-buy]').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (btn.disabled) return;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Buying…';
                const r = await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=buy&item_id=${btn.dataset.buy}`
                }).then(r => r.json());
                if (r.error) { showToast(r.error, false); btn.disabled = false; loadShop(); return; }
                setBalance(r.coins);
                showToast(isGM ? '👑 Added to your collection!' : '🔥 Purchased! Equip it now.');
                loadShop();
            });
        });

        grid.querySelectorAll('[data-equip]').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=equip&item_id=${btn.dataset.equip}`
                });
                showToast('✅ Equipped!');
                loadShop();
            });
        });

        // GM gift buttons
        grid.querySelectorAll('[data-gift]').forEach(btn => {
            btn.addEventListener('click', () => {
                giftTargetItemId = btn.dataset.gift;
                document.getElementById('giftItemName').textContent = '🎁 Gifting: ' + btn.dataset.name;
                document.getElementById('giftSearch').value = '';
                document.getElementById('giftUserResults').innerHTML = '';
                document.getElementById('giftStatus').textContent = '';
                document.getElementById('giftModal').classList.remove('hidden');
            });
        });
    }

    document.querySelectorAll('.shop-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.shop-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentFilter = tab.dataset.filter;
            render();
        });
    });

    // ── Inventory ──────────────────────────────────────────────────────────────
    let invItems = [], invFilter = 'all';

    async function openInventory() {
        document.getElementById('inventoryModal').classList.remove('hidden');
        document.getElementById('invGrid').innerHTML = '<div style="text-align:center;padding:32px;color:#475569;"><i class="fas fa-spinner fa-spin" style="font-size:20px;"></i></div>';
        const items = await fetch(API + '?action=inventory').then(r => r.json());
        invItems = Array.isArray(items) ? items : [];
        renderInventory();
    }

    function renderInventory() {
        const filtered = invFilter === 'all' ? invItems : invItems.filter(i => i.type === invFilter);
        const typeLabel = { title: 'Title', name_color: 'Name Color', name_bg: 'Background' };
        const typeIcon  = { title: 'fa-tag', name_color: 'fa-palette', name_bg: 'fa-fill-drip' };

        if (!filtered.length) {
            document.getElementById('invGrid').innerHTML = '<p style="text-align:center;padding:32px;color:#475569;">No items yet. Go buy some!</p>';
            return;
        }

        document.getElementById('invGrid').innerHTML = filtered.map(item => {
            const isFire = item.theme === 'fire';
            const giftedTag = item.gifted_by_name
                ? `<span class="inv-gifted-tag"><i class="fas fa-gift mr-1"></i>Gifted by ${escHtml(item.gifted_by_name)}</span>`
                : '';
            const equipBtn = item.equipped
                ? `<button class="shop-btn equipped" style="font-size:11px;padding:6px 10px;"><i class="fas fa-check-circle mr-1"></i>Equipped</button>`
                : `<button class="inv-equip-btn" data-equip="${item.id}"><i class="fas fa-wand-magic-sparkles mr-1"></i>Equip</button>`;

            return `
            <div class="inv-card ${isFire ? 'theme-fire' : ''}">
                <div class="shop-card-type"><i class="fas ${typeIcon[item.type]} mr-1"></i>${typeLabel[item.type]}</div>
                <div class="shop-preview" style="${item.preview_css ?? ''}">${escHtml(item.name)}</div>
                <div class="shop-card-name">${escHtml(item.name)}</div>
                ${giftedTag}
                ${equipBtn}
            </div>`;
        }).join('');

        document.getElementById('invGrid').querySelectorAll('[data-equip]').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=equip&item_id=${btn.dataset.equip}`
                });
                showToast('✅ Equipped!');
                openInventory();
                loadShop();
            });
        });
    }

    document.getElementById('inventoryBtn').addEventListener('click', openInventory);
    document.getElementById('invClose').addEventListener('click', () => document.getElementById('inventoryModal').classList.add('hidden'));
    document.getElementById('inventoryModal').addEventListener('click', e => { if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden'); });

    document.querySelectorAll('.inv-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.inv-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            invFilter = tab.dataset.type;
            renderInventory();
        });
    });

    // ── GM Gift Modal ──────────────────────────────────────────────────────────
    document.getElementById('giftClose').addEventListener('click', () => document.getElementById('giftModal').classList.add('hidden'));
    document.getElementById('giftModal').addEventListener('click', e => { if (e.target === e.currentTarget) e.currentTarget.classList.add('hidden'); });

    let giftSearchTimer;
    document.getElementById('giftSearch').addEventListener('input', function() {
        clearTimeout(giftSearchTimer);
        const q = this.value.trim();
        if (!q) { document.getElementById('giftUserResults').innerHTML = ''; return; }
        giftSearchTimer = setTimeout(async () => {
            const users = await fetch(`${API}?action=search_users&q=${encodeURIComponent(q)}`).then(r => r.json());
            const list = document.getElementById('giftUserResults');
            if (!users.length) { list.innerHTML = '<p style="color:#475569;font-size:12px;padding:8px;">No users found.</p>'; return; }
            list.innerHTML = users.map(u =>
                `<button class="gm-user-row" data-uid="${u.id}" data-uname="${escHtml(u.full_name)}">
                    <i class="fas fa-user mr-2" style="color:#64748b;"></i>${escHtml(u.full_name)}
                </button>`
            ).join('');
            list.querySelectorAll('.gm-user-row').forEach(row => {
                row.addEventListener('click', async () => {
                    const status = document.getElementById('giftStatus');
                    status.style.color = '#94a3b8';
                    status.textContent = 'Gifting…';
                    const r = await fetch(API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=gift&item_id=${giftTargetItemId}&target_id=${row.dataset.uid}`
                    }).then(r => r.json());
                    if (r.error) {
                        status.style.color = '#f87171';
                        status.textContent = '✗ ' + r.error;
                    } else {
                        status.style.color = '#34d399';
                        status.textContent = `✓ Gifted to ${row.dataset.uname}!`;
                        showToast(`🎁 Gifted to ${row.dataset.uname}!`);
                        loadShop();
                    }
                });
            });
        }, 300);
    });

    loadBalance().then(loadShop);
})();
</script>
</body>
</html>
