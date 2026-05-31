<?php
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pageTitle = 'Shop — CSEReviewer';
$extraCss  = ['assets/css/dashboard.css', 'assets/css/left-sidebar.css', 'assets/css/shop.css'];
$root      = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../includes/head.php'; ?></head>
<body class="bg-slate-950 min-h-screen">

<nav class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <button id="sbHamburger" class="md:hidden text-slate-400 hover:text-white mr-1" aria-label="Open sidebar">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <img src="../assets/img/logo.png" alt="CSE Reviewer Logo" class="w-9 h-9 rounded-xl object-contain">
        <span class="text-white font-bold text-lg">CSE<span class="gradient-text">Reviewer</span></span>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-1.5 bg-yellow-500/10 border border-yellow-500/20 px-3 py-1.5 rounded-xl">
            <i class="fas fa-coins text-yellow-400 text-sm"></i>
            <span id="coinBalance" class="text-yellow-400 font-bold text-sm">—</span>
        </div>
        <a href="dashboard.php" class="text-slate-400 hover:text-white text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-home"></i><span class="hidden sm:inline"> Dashboard</span>
        </a>
        <a href="../auth/logout.php" class="text-slate-400 hover:text-red-400 text-sm transition-colors flex items-center gap-1">
            <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline"> Logout</span>
        </a>
    </div>
</nav>

<?php include '../includes/left-sidebar.php'; ?>

<div class="dashboard-main">
    <div class="max-w-4xl mx-auto px-6 py-12">

        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white mb-3"><i class="fas fa-store text-yellow-400 mr-2"></i>Coin <span class="gradient-text">Shop</span></h1>
            <p class="text-slate-400">Spend your coins on titles, name colors, and backgrounds to flex on the leaderboard.</p>
        </div>

        <!-- Coin reward info -->
        <div class="shop-reward-banner mb-8">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-coins text-yellow-400"></i>
                <span class="text-white font-bold">How to Earn Coins</span>
            </div>
            <div class="reward-tiers">
                <div class="reward-tier"><span class="tier-pct">90–100%</span><span class="tier-coins">🪙 50 coins</span></div>
                <div class="reward-tier"><span class="tier-pct">75–89%</span><span class="tier-coins">🪙 30 coins</span></div>
                <div class="reward-tier"><span class="tier-pct">60–74%</span><span class="tier-coins">🪙 15 coins</span></div>
                <div class="reward-tier"><span class="tier-pct">50–59%</span><span class="tier-coins">🪙 8 coins</span></div>
                <div class="reward-tier"><span class="tier-pct">Below 50%</span><span class="tier-coins">🪙 3 coins</span></div>
                <div class="reward-tier bonus"><span class="tier-pct">Mock Exam bonus</span><span class="tier-coins">🪙 +10 coins</span></div>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="shop-tabs mb-6">
            <button class="shop-tab active" data-filter="all">All</button>
            <button class="shop-tab" data-filter="title"><i class="fas fa-tag mr-1"></i>Titles</button>
            <button class="shop-tab" data-filter="name_color"><i class="fas fa-palette mr-1"></i>Name Colors</button>
            <button class="shop-tab" data-filter="name_bg"><i class="fas fa-fill-drip mr-1"></i>Backgrounds</button>
        </div>

        <!-- Toast -->
        <div id="shopToast" class="shop-toast hidden"></div>

        <!-- Items grid -->
        <div id="shopGrid" class="shop-grid">
            <div class="text-slate-500 text-center py-12 col-span-full">
                <i class="fas fa-spinner fa-spin text-2xl mb-3"></i><br>Loading shop…
            </div>
        </div>

    </div>
</div>

<script>const ROOT = '../';</script>
<script src="../assets/js/left-sidebar.js"></script>
<script>
(() => {
    const API = ROOT + 'api/coins.php';
    let allItems = [], currentFilter = 'all', myCoins = 0;

    const grid    = document.getElementById('shopGrid');
    const balance = document.getElementById('coinBalance');
    const toast   = document.getElementById('shopToast');

    function showToast(msg, ok = true) {
        toast.textContent = msg;
        toast.className = 'shop-toast ' + (ok ? 'ok' : 'err');
        setTimeout(() => toast.classList.add('hidden'), 2800);
    }

    async function loadBalance() {
        const r = await fetch(API + '?action=balance').then(r => r.json());
        myCoins = r.coins;
        balance.textContent = myCoins.toLocaleString();
    }

    async function loadShop() {
        const items = await fetch(API + '?action=shop').then(r => r.json());
        allItems = items;
        render();
    }

    function render() {
        const filtered = currentFilter === 'all' ? allItems : allItems.filter(i => i.type === currentFilter);
        if (!filtered.length) { grid.innerHTML = '<p class="text-slate-500 text-center py-12 col-span-full">No items here.</p>'; return; }

        grid.innerHTML = filtered.map(item => {
            const canAfford = myCoins >= item.price;
            const typeLabel = { title: 'Title', name_color: 'Name Color', name_bg: 'Background' }[item.type];
            const typeIcon  = { title: 'fa-tag', name_color: 'fa-palette', name_bg: 'fa-fill-drip' }[item.type];

            let btn = '';
            if (item.owned && item.equipped) {
                btn = `<button class="shop-btn equipped" data-unequip="${item.id}"><i class="fas fa-check-circle mr-1"></i>Equipped</button>`;
            } else if (item.owned) {
                btn = `<button class="shop-btn equip" data-equip="${item.id}"><i class="fas fa-wand-magic-sparkles mr-1"></i>Equip</button>`;
            } else {
                btn = `<button class="shop-btn buy ${canAfford ? '' : 'cant-afford'}" data-buy="${item.id}" data-price="${item.price}" ${canAfford ? '' : 'disabled'}>
                    <i class="fas fa-coins mr-1"></i>${item.price} coins
                </button>`;
            }

            return `
            <div class="shop-card ${item.equipped ? 'is-equipped' : ''}" data-type="${item.type}">
                <div class="shop-card-type"><i class="fas ${typeIcon} mr-1"></i>${typeLabel}</div>
                <div class="shop-preview" style="${item.preview_css ?? ''}">${item.name}</div>
                <div class="shop-card-name">${item.name}</div>
                <div class="shop-card-desc">${item.description ?? ''}</div>
                ${btn}
            </div>`;
        }).join('');

        grid.querySelectorAll('[data-buy]').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (btn.disabled) return;
                btn.disabled = true;
                const r = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=buy&item_id=${btn.dataset.buy}` }).then(r => r.json());
                if (r.error) { showToast(r.error, false); btn.disabled = false; return; }
                myCoins = r.coins;
                balance.textContent = myCoins.toLocaleString();
                showToast('Purchased! You can now equip it.');
                loadShop();
            });
        });

        grid.querySelectorAll('[data-equip]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=equip&item_id=${btn.dataset.equip}` });
                showToast('Equipped!');
                loadShop();
            });
        });

        grid.querySelectorAll('[data-unequip]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=unequip&item_id=${btn.dataset.unequip}` });
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

    loadBalance().then(loadShop);
})();
</script>
</body>
</html>
