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

if ($action === 'balance') {
    $row = $pdo->prepare("SELECT coins FROM users WHERE id=?");
    $row->execute([$me]);
    send(['coins' => (int) $row->fetchColumn()]);
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

    // Check already owned
    $check = $pdo->prepare("SELECT id FROM user_cosmetics WHERE user_id=? AND item_id=?");
    $check->execute([$me, $itemId]);
    if ($check->fetch()) send(['error' => 'Already owned']);

    // Check balance
    $bal = $pdo->prepare("SELECT coins FROM users WHERE id=?");
    $bal->execute([$me]);
    $coins = (int) $bal->fetchColumn();
    if ($coins < $item['price']) send(['error' => 'Not enough coins']);

    $pdo->beginTransaction();
    $pdo->prepare("UPDATE users SET coins = coins - ? WHERE id=?")->execute([$item['price'], $me]);
    $pdo->prepare("INSERT INTO user_cosmetics (user_id, item_id) VALUES (?,?)")->execute([$me, $itemId]);
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

    // Verify owned
    $check = $pdo->prepare("SELECT id FROM user_cosmetics WHERE user_id=? AND item_id=?");
    $check->execute([$me, $itemId]);
    if (!$check->fetch()) send(['error' => 'Not owned']);

    // Unequip all of same type first
    $pdo->prepare("
        UPDATE user_cosmetics uc
        JOIN shop_items si ON si.id = uc.item_id
        SET uc.equipped = 0
        WHERE uc.user_id = ? AND si.type = ?
    ")->execute([$me, $item['type']]);

    // Equip selected
    $pdo->prepare("UPDATE user_cosmetics SET equipped=1 WHERE user_id=? AND item_id=?")->execute([$me, $itemId]);
    send(['success' => true]);
}

if ($action === 'unequip') {
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $pdo->prepare("UPDATE user_cosmetics SET equipped=0 WHERE user_id=? AND item_id=?")->execute([$me, $itemId]);
    send(['success' => true]);
}

send(['error' => 'Unknown action']);
