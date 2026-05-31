<?php
ob_start();
ini_set('display_errors', '0');
ini_set('session.cookie_path', '/');
session_start();

function send($data) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['user_id'])) send(['error' => 'Unauthorized']);

require_once '../config/db.php';

$me     = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ── Helper: check if current user is GM ─────────────────────────────────────
function isGM($pdo, $userId) {
    $s = $pdo->prepare("SELECT is_game_master FROM users WHERE id=?");
    $s->execute([$userId]);
    return (int) $s->fetchColumn() === 1;
}

// ── Helper: grant item to a user (no coin deduction, optional gifter) ────────
function grantItem($pdo, $userId, $itemId, $giftedBy = null) {
    $check = $pdo->prepare("SELECT id FROM user_cosmetics WHERE user_id=? AND item_id=?");
    $check->execute([$userId, $itemId]);
    if ($check->fetch()) return ['error' => 'Already owned'];
    $pdo->prepare("INSERT INTO user_cosmetics (user_id, item_id, gifted_by) VALUES (?,?,?)")
        ->execute([$userId, $itemId, $giftedBy]);
    return ['success' => true];
}

if ($action === 'balance') {
    // Auto-add is_game_master if missing
    try {
        $pdo->query("SELECT is_game_master FROM users LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_game_master TINYINT(1) NOT NULL DEFAULT 0");
    }
    $row = $pdo->prepare("SELECT coins, is_game_master FROM users WHERE id=?");
    $row->execute([$me]);
    $row = $row->fetch();
    send(['coins' => (int) $row['coins'], 'is_gm' => (int) $row['is_game_master'] === 1]);
}

if ($action === 'shop') {
    $items = $pdo->query("SELECT id, type, name, value, price, description, preview_css, theme FROM shop_items ORDER BY theme DESC, type, price")->fetchAll();
    $owned = $pdo->prepare("SELECT item_id, equipped FROM user_cosmetics WHERE user_id=?");
    $owned->execute([$me]);
    $ownedMap = [];
    foreach ($owned->fetchAll() as $r) $ownedMap[$r['item_id']] = (int) $r['equipped'];

    foreach ($items as &$item) {
        $item['owned']   = isset($ownedMap[$item['id']]);
        $item['equipped'] = ($ownedMap[$item['id']] ?? 0) === 1;
    }
    send($items);
}

if ($action === 'buy') {
    $itemId = (int) ($_POST['item_id'] ?? 0);

    $item = $pdo->prepare("SELECT * FROM shop_items WHERE id=?");
    $item->execute([$itemId]);
    $item = $item->fetch();
    if (!$item) send(['error' => 'Item not found']);

    $check = $pdo->prepare("SELECT id FROM user_cosmetics WHERE user_id=? AND item_id=?");
    $check->execute([$me, $itemId]);
    if ($check->fetch()) send(['error' => 'Already owned']);

    // GM buys for free
    $price = isGM($pdo, $me) ? 0 : $item['price'];

    $bal = $pdo->prepare("SELECT coins FROM users WHERE id=?");
    $bal->execute([$me]);
    $coins = (int) $bal->fetchColumn();
    if ($coins < $price) send(['error' => 'Not enough coins']);

    $pdo->beginTransaction();
    if ($price > 0) {
        $pdo->prepare("UPDATE users SET coins = coins - ? WHERE id=?")->execute([$price, $me]);
    }
    // gifted_by column may not exist yet — insert without it
    try {
        $pdo->query("SELECT gifted_by FROM user_cosmetics LIMIT 1");
        $pdo->prepare("INSERT INTO user_cosmetics (user_id, item_id, gifted_by) VALUES (?,?,NULL)")->execute([$me, $itemId]);
    } catch (Exception $e) {
        $pdo->prepare("INSERT INTO user_cosmetics (user_id, item_id) VALUES (?,?)")->execute([$me, $itemId]);
    }
    $pdo->commit();

    $newBal = $pdo->prepare("SELECT coins FROM users WHERE id=?");
    $newBal->execute([$me]);
    send(['success' => true, 'coins' => (int) $newBal->fetchColumn()]);
}

if ($action === 'equip') {
    $itemId = (int) ($_POST['item_id'] ?? 0);

    $item = $pdo->prepare("SELECT type FROM shop_items WHERE id=?");
    $item->execute([$itemId]);
    $item = $item->fetch();
    if (!$item) send(['error' => 'Item not found']);

    $check = $pdo->prepare("SELECT id FROM user_cosmetics WHERE user_id=? AND item_id=?");
    $check->execute([$me, $itemId]);
    if (!$check->fetch()) send(['error' => 'Not owned']);

    $pdo->prepare("
        UPDATE user_cosmetics uc
        JOIN shop_items si ON si.id = uc.item_id
        SET uc.equipped = 0
        WHERE uc.user_id = ? AND si.type = ?
    ")->execute([$me, $item['type']]);

    $pdo->prepare("UPDATE user_cosmetics SET equipped=1 WHERE user_id=? AND item_id=?")->execute([$me, $itemId]);
    send(['success' => true]);
}

if ($action === 'unequip') {
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $pdo->prepare("UPDATE user_cosmetics SET equipped=0 WHERE user_id=? AND item_id=?")->execute([$me, $itemId]);
    send(['success' => true]);
}

// ── GM: gift item to another user ────────────────────────────────────────────
if ($action === 'gift') {
    if (!isGM($pdo, $me)) send(['error' => 'Forbidden']);
    $itemId    = (int) ($_POST['item_id'] ?? 0);
    $targetId  = (int) ($_POST['target_id'] ?? 0);
    if (!$itemId || !$targetId) send(['error' => 'Missing params']);

    $target = $pdo->prepare("SELECT id FROM users WHERE id=?");
    $target->execute([$targetId]);
    if (!$target->fetch()) send(['error' => 'User not found']);

    $item = $pdo->prepare("SELECT id FROM shop_items WHERE id=?");
    $item->execute([$itemId]);
    if (!$item->fetch()) send(['error' => 'Item not found']);

    send(grantItem($pdo, $targetId, $itemId, $me));
}

// ── GM: search users to gift to ──────────────────────────────────────────────
if ($action === 'search_users') {
    if (!isGM($pdo, $me)) send(['error' => 'Forbidden']);
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    $s = $pdo->prepare("SELECT id, full_name FROM users WHERE full_name LIKE ? AND id != ? LIMIT 10");
    $s->execute([$q, $me]);
    send($s->fetchAll());
}

// ── Inventory: owned items for current user ───────────────────────────────────
if ($action === 'inventory') {
    // Check if gifted_by column exists, add it if missing
    try {
        $pdo->query("SELECT gifted_by FROM user_cosmetics LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE user_cosmetics ADD COLUMN gifted_by INT NULL");
    }

    $rows = $pdo->prepare("
        SELECT si.id, si.type, si.name, si.value, si.price, si.description, si.preview_css, si.theme,
               uc.equipped, uc.bought_at, uc.gifted_by,
               gm.full_name AS gifted_by_name
        FROM user_cosmetics uc
        JOIN shop_items si ON si.id = uc.item_id
        LEFT JOIN users gm ON gm.id = uc.gifted_by
        WHERE uc.user_id = ?
        ORDER BY uc.bought_at DESC
    ");
    $rows->execute([$me]);
    send($rows->fetchAll());
}

send(['error' => 'Unknown action']);
