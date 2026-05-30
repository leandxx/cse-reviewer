<?php
ob_start();
ini_set('display_errors', '0');
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();

function send($data) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['user_id'])) send(['error' => 'Unauthorized']);

require_once '../config/db.php';

$me = (int) $_SESSION['user_id'];

// XP thresholds per level (level = floor(xp / 100) + 1, capped display)
function xpToLevel($xp) {
    return floor($xp / 100) + 1;
}

$action = $_GET['action'] ?? '';

if ($action === 'stats') {
    $u = $pdo->prepare("SELECT xp FROM users WHERE id=?");
    $u->execute([$me]);
    $user = $u->fetch();
    $xp   = (int) $user['xp'];

    $level       = xpToLevel($xp);
    $xpInLevel   = $xp % 100;
    $xpForNext   = 100;

    $history = $pdo->prepare("
        SELECT id, subject, total, correct, created_at
        FROM quiz_sessions
        WHERE user_id=? AND finished=1
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $history->execute([$me]);

    send([
        'xp'         => $xp,
        'level'      => $level,
        'xp_in_level'=> $xpInLevel,
        'xp_for_next'=> $xpForNext,
        'history'    => $history->fetchAll(),
    ]);
}

if ($action === 'leaderboard') {
    $rows = $pdo->query("
        SELECT id, full_name, xp
        FROM users
        ORDER BY xp DESC
        LIMIT 20
    ")->fetchAll();

    $board = array_map(function($r) use ($me) {
        return [
            'id'        => (int) $r['id'],
            'full_name' => $r['full_name'],
            'xp'        => (int) $r['xp'],
            'level'     => xpToLevel((int) $r['xp']),
            'is_me'     => (int) $r['id'] === $me,
        ];
    }, $rows);

    send($board);
}

send(['error' => 'Unknown action']);
