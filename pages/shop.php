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

<!-- Ember particle field -->
<div class="ember-field" id="emberField"></div>

<nav class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <button id="sbHamburger" class="md:hidden text-slate-400 hover:text-white mr-1" aria-label="Open sidebar">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <img src="../assets/img/logo.png" alt="CSE Reviewer Logo" class="w-9 h-9 rounded-xl object-contain">
        <span class="text-white font-bold text-lg">CSE<span class="gradient-text">Reviewer</span></span>
    </div>
    <div class="flex items-center gap-4">
        <div class="shop-coin-pill" style="margin-bottom:0;">
            <i class="fas fa-coins"></i>
            <span id="coinBalance">—</span> coins
        </div>
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

<!-- Toast -->
<div id="shopToast" class="shop-toast hidden"></div>

<script>const ROOT = '../';</script>
<script src="../assets/js/left-sidebar.js"></script>
<script>
// ── Ember particles ──────────────────────────────────────────────────────────
(() => {
    const field = document.getElementById('emberField');
    const COUNT = 38;
    for (let i = 0; i < COUNT; i++) {
        const e = document.createElement('div');
        e.className = 'ember';
        const size = Math.random() * 4 + 2;
        const left = Math.random() * 100;
        const delay = Math.random() * 8;
        const dur   = Math.random() * 6 + 5;
        const drift = (Math.random() - 0.5) * 120;
        e.style.cssText = `
            width:${size}px; height:${size}px;
            left:${left}%;
            animation-duration:${dur}s;
            animation-delay:${delay}s;
            --drift:${drift}px;
        `;
        field.appendChild(e);
    }
})();

// ── Shop logic ───────────────────────────────────────────────────────────────
(() => {
    const API = ROOT + 'api/coins.php';
    let allItems = [], currentFilter = 'all', myCoins = 0;

    const grid        = document.getElementById('shopGrid');
    const balNav      = document.getElementById('coinBalance');
    const balHero     = document.getElementById('coinBalanceHero');
    const toast       = document.getElementById('shopToast');

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

    async function loadBalance() {
        const r = await fetch(API + '?action=balance').then(r => r.json());
        setBalance(r.coins ?? 0);
    }

    async function loadShop() {
        const items = await fetch(API + '?action=shop').then(r => r.json());
        allItems = Array.isArray(items) ? items : [];
        render();
    }

    function render() {
        let filtered;
        if (currentFilter === 'all')   filtered = allItems;
        else if (currentFilter === 'fire') filtered = allItems.filter(i => i.theme === 'fire');
        else filtered = allItems.filter(i => i.type === currentFilter);

        if (!filtered.length) {
            grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:48px 0;color:#78350f;">No items here.</p>';
            return;
        }

        const typeLabel = { title: 'Title', name_color: 'Name Color', name_bg: 'Background' };
        const typeIcon  = { title: 'fa-tag', name_color: 'fa-palette', name_bg: 'fa-fill-drip' };

        grid.innerHTML = filtered.map(item => {
            const isFire    = item.theme === 'fire';
            const canAfford = myCoins >= item.price;
            const fireClass = isFire ? 'theme-fire' : '';

            const fireBadge = isFire
                ? `<span class="fire-badge"><i class="fas fa-fire"></i> Fire</span>`
                : '';

            // Generate spark elements for fire cards
            const sparks = isFire ? `<div class="fire-sparks">${
                Array.from({length: 8}, (_, i) => {
                    const left  = 10 + i * 11;
                    const dur   = (1.5 + Math.random() * 1.5).toFixed(2);
                    const delay = (Math.random() * 2).toFixed(2);
                    const sx    = ((Math.random() - 0.5) * 30).toFixed(1);
                    return `<div class="fire-spark" style="left:${left}%;animation-duration:${dur}s;animation-delay:${delay}s;--sx:${sx}px;"></div>`;
                }).join('')
            }</div>` : '';

            let btn = '';
            if (item.owned && item.equipped) {
                btn = `<button class="shop-btn equipped"><i class="fas fa-check-circle mr-1"></i>Equipped</button>`;
            } else if (item.owned) {
                btn = `<button class="shop-btn equip" data-equip="${item.id}"><i class="fas fa-wand-magic-sparkles mr-1"></i>Equip</button>`;
            } else {
                btn = `<button class="shop-btn buy ${canAfford ? '' : 'cant-afford'}" data-buy="${item.id}" ${canAfford ? '' : 'disabled'}>
                    <i class="fas fa-coins mr-1"></i>${Number(item.price).toLocaleString()} coins
                </button>`;
            }

            return `
            <div class="shop-card ${fireClass} ${item.equipped ? 'is-equipped' : ''}">
                ${sparks}
                <div class="shop-card-type"><i class="fas ${typeIcon[item.type]} mr-1"></i>${typeLabel[item.type]}${fireBadge}</div>
                <div class="shop-preview" style="${item.preview_css ?? ''}">${escHtml(item.name)}</div>
                <div class="shop-card-name">${escHtml(item.name)}</div>
                <div class="shop-card-desc">${escHtml(item.description ?? '')}</div>
                ${btn}
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
                showToast('🔥 Purchased! Equip it now.');
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

        grid.querySelectorAll('[data-unequip]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=unequip&item_id=${btn.dataset.unequip}`
                });
                showToast('Unequipped.');
                loadShop();
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

    const escHtml = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

    loadBalance().then(loadShop);
})();
</script>
</body>
</html>
