<?php
ob_start();
ini_set('session.cookie_path', '/');
session_start();

function send($data) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

set_exception_handler(function($e) {
    send(['error' => $e->getMessage()]);
});

if (!isset($_SESSION['user_id'])) {
    send(['error' => 'Unauthorized']);
}

require_once '../config/db.php';

$me     = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ── Start session ─────────────────────────────────────────────────────────────
if ($action === 'start') {
    $subject = $_POST['subject'] ?? 'verbal';
    $limit   = min((int) ($_POST['limit'] ?? 10), 100);

    $valid = ['verbal', 'numerical', 'analytical', 'general', 'all'];
    if (!in_array($subject, $valid)) send(['error' => 'Invalid subject']);

    if ($subject === 'all') {
        $q = $pdo->prepare("SELECT id FROM questions ORDER BY RAND() LIMIT $limit");
        $q->execute();
    } else {
        $q = $pdo->prepare("SELECT id FROM questions WHERE subject = ? ORDER BY RAND() LIMIT $limit");
        $q->execute([$subject]);
    }
    $ids = $q->fetchAll(PDO::FETCH_COLUMN);

    if (!$ids) send(['error' => 'No questions available for this subject yet.']);

    $pdo->prepare("INSERT INTO quiz_sessions (user_id, subject, total) VALUES (?,?,?)")
        ->execute([$me, $subject, count($ids)]);
    $sessionId = $pdo->lastInsertId();

    $ins = $pdo->prepare("INSERT INTO quiz_answers (session_id, question_id) VALUES (?,?)");
    foreach ($ids as $qid) $ins->execute([$sessionId, $qid]);

    send(['session_id' => $sessionId, 'total' => count($ids)]);
}

// ── Get question ──────────────────────────────────────────────────────────────
if ($action === 'question') {
    $sessionId = (int) ($_GET['session_id'] ?? 0);
    $index     = (int) ($_GET['index'] ?? 0);

    $sess = $pdo->prepare("SELECT id FROM quiz_sessions WHERE id=? AND user_id=?");
    $sess->execute([$sessionId, $me]);
    if (!$sess->fetch()) send(['error' => 'Invalid session']);

    $row = $pdo->prepare("
        SELECT qa.id AS answer_id, qa.chosen, qa.hint_used,
               q.id, q.question, q.choice_a, q.choice_b, q.choice_c, q.choice_d,
               q.hint, q.subject, q.difficulty
        FROM quiz_answers qa
        JOIN questions q ON q.id = qa.question_id
        WHERE qa.session_id = ?
        ORDER BY qa.id ASC
        LIMIT 1 OFFSET ?
    ");
    $row->execute([$sessionId, $index]);
    $data = $row->fetch();

    if (!$data) send(['done' => true]);

    send($data);
}

// ── Submit answer ─────────────────────────────────────────────────────────────
if ($action === 'answer') {
    $answerId = (int) ($_POST['answer_id'] ?? 0);
    $chosen   = strtolower(trim($_POST['chosen'] ?? ''));
    $hintUsed = (int) ($_POST['hint_used'] ?? 0);

    if (!in_array($chosen, ['a','b','c','d'])) send(['error' => 'Invalid choice']);

    $row = $pdo->prepare("
        SELECT qa.*, q.answer, q.explanation
        FROM quiz_answers qa
        JOIN questions q     ON q.id  = qa.question_id
        JOIN quiz_sessions s ON s.id  = qa.session_id
        WHERE qa.id=? AND s.user_id=?
    ");
    $row->execute([$answerId, $me]);
    $data = $row->fetch();

    if (!$data) send(['error' => 'Invalid answer row']);
    if ($data['chosen'] !== null) send(['error' => 'Already answered']);

    $correct = ($chosen === $data['answer']);

    $pdo->prepare("UPDATE quiz_answers SET chosen=?, is_correct=?, hint_used=? WHERE id=?")
        ->execute([$chosen, $correct ? 1 : 0, $hintUsed, $answerId]);

    if ($correct) {
        $pdo->prepare("UPDATE quiz_sessions SET correct = correct + 1 WHERE id=?")
            ->execute([$data['session_id']]);
    }

    send([
        'correct'      => $correct,
        'right_answer' => $data['answer'],
        'explanation'  => $data['explanation'],
    ]);
}

// ── Finish session ────────────────────────────────────────────────────────────
if ($action === 'finish') {
    $sessionId = (int) ($_POST['session_id'] ?? 0);
    $pdo->prepare("UPDATE quiz_sessions SET finished=1 WHERE id=? AND user_id=?")
        ->execute([$sessionId, $me]);

    $sess = $pdo->prepare("SELECT subject, total, correct FROM quiz_sessions WHERE id=? AND user_id=?");
    $sess->execute([$sessionId, $me]);
    send($sess->fetch());
}

// ── History ───────────────────────────────────────────────────────────────────
if ($action === 'history') {
    $rows = $pdo->prepare("
        SELECT id, subject, total, correct, created_at
        FROM quiz_sessions
        WHERE user_id=? AND finished=1
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $rows->execute([$me]);
    send($rows->fetchAll());
}

send(['error' => 'Unknown action']);
