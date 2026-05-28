<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once '../config/db.php';

$me     = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ── Heartbeat ─────────────────────────────────────────────────────────────────
if ($action === 'ping') {
    $pdo->prepare("UPDATE users SET is_online=1, last_seen=NOW() WHERE id=?")
        ->execute([$me]);
    echo json_encode(['ok' => true]); exit;
}

// ── List friends ──────────────────────────────────────────────────────────────
if ($action === 'list') {
    $pdo->exec("UPDATE users SET is_online=0 WHERE is_online=1 AND last_seen < DATE_SUB(NOW(), INTERVAL 90 SECOND)");

    // Only WHERE user_id=me — avoids duplicates from bidirectional rows
    $rows = $pdo->prepare("
        SELECT u.id, u.full_name, u.is_online, u.last_seen
        FROM friends f
        JOIN users u ON u.id = f.friend_id
        WHERE f.user_id = ?
        ORDER BY u.is_online DESC, u.full_name ASC
    ");
    $rows->execute([$me]);
    echo json_encode($rows->fetchAll()); exit;
}

// ── Pending requests sent TO me ───────────────────────────────────────────────
if ($action === 'pending') {
    $rows = $pdo->prepare("
        SELECT fr.id AS request_id, u.id, u.full_name
        FROM friend_requests fr
        JOIN users u ON u.id = fr.sender_id
        WHERE fr.receiver_id=? AND fr.status='pending'
    ");
    $rows->execute([$me]);
    echo json_encode($rows->fetchAll()); exit;
}

// ── Search users ──────────────────────────────────────────────────────────────
if ($action === 'search') {
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    $rows = $pdo->prepare("
        SELECT u.id, u.full_name,
            CASE
                WHEN f.id IS NOT NULL THEN 'friend'
                WHEN fr_sent.id IS NOT NULL THEN 'sent'
                WHEN fr_recv.id IS NOT NULL THEN 'received'
                ELSE 'none'
            END AS rel
        FROM users u
        LEFT JOIN friends f
            ON f.user_id=? AND f.friend_id=u.id
        LEFT JOIN friend_requests fr_sent
            ON fr_sent.sender_id=? AND fr_sent.receiver_id=u.id AND fr_sent.status='pending'
        LEFT JOIN friend_requests fr_recv
            ON fr_recv.receiver_id=? AND fr_recv.sender_id=u.id AND fr_recv.status='pending'
        WHERE u.id != ? AND u.full_name LIKE ?
        LIMIT 10
    ");
    $rows->execute([$me, $me, $me, $me, $q]);
    echo json_encode($rows->fetchAll()); exit;
}

// ── Suggestions (users not yet friends or pending) ──────────────────────────
if ($action === 'suggestions') {
    $rows = $pdo->prepare("
        SELECT u.id, u.full_name, u.is_online
        FROM users u
        WHERE u.id != ?
          AND u.id NOT IN (
              SELECT friend_id FROM friends WHERE user_id = ?
          )
          AND u.id NOT IN (
              SELECT receiver_id FROM friend_requests WHERE sender_id = ? AND status = 'pending'
          )
          AND u.id NOT IN (
              SELECT sender_id FROM friend_requests WHERE receiver_id = ? AND status = 'pending'
          )
        ORDER BY u.full_name ASC
    ");
    $rows->execute([$me, $me, $me, $me]);
    echo json_encode($rows->fetchAll()); exit;
}

// ── Send friend request ───────────────────────────────────────────────────────
if ($action === 'add') {
    $to = (int) ($_POST['to'] ?? 0);
    if (!$to) { echo json_encode(['error' => 'Invalid user']); exit; }
    try {
        $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?,?)")
            ->execute([$me, $to]);
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Already sent']);
    }
    exit;
}

// ── Accept request ────────────────────────────────────────────────────────────
if ($action === 'accept') {
    $reqId = (int) ($_POST['request_id'] ?? 0);
    try {
        $pdo->beginTransaction();

        $row = $pdo->prepare("SELECT * FROM friend_requests WHERE id=? AND receiver_id=? AND status='pending' FOR UPDATE");
        $row->execute([$reqId, $me]);
        $req = $row->fetch();

        if (!$req) {
            $pdo->rollBack();
            echo json_encode(['ok' => true]); exit; // already accepted
        }

        $pdo->prepare("UPDATE friend_requests SET status='accepted' WHERE id=?")->execute([$reqId]);

        // Insert only one direction each — INSERT IGNORE handles any race
        $ins = $pdo->prepare("INSERT IGNORE INTO friends (user_id, friend_id) VALUES (?,?)");
        $ins->execute([$me, $req['sender_id']]);
        $ins->execute([$req['sender_id'], $me]);

        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ── Decline request ───────────────────────────────────────────────────────────
if ($action === 'decline') {
    $reqId = (int) ($_POST['request_id'] ?? 0);
    $pdo->prepare("UPDATE friend_requests SET status='declined' WHERE id=? AND receiver_id=?")
        ->execute([$reqId, $me]);
    echo json_encode(['ok' => true]); exit;
}

// ── Remove friend ─────────────────────────────────────────────────────────────
if ($action === 'remove') {
    $fid = (int) ($_POST['friend_id'] ?? 0);
    $pdo->prepare("DELETE FROM friends WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?)")
        ->execute([$me, $fid, $fid, $me]);
    echo json_encode(['ok' => true]); exit;
}

echo json_encode(['error' => 'Unknown action']);
