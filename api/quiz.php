<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once '../config/db.php';

$me     = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ── Start session ─────────────────────────────────────────────────────────────
if ($action === 'start') {
    $subject = $_POST['subject'] ?? 'verbal';
    $limit   = min((int) ($_POST['limit'] ?? 10), 50);

    $valid = ['verbal', 'numerical', 'analytical', 'general'];
    if (!in_array($subject, $valid)) { echo json_encode(['error' => 'Invalid subject']); exit; }

    // Pick random questions for this subject
    $q = $pdo->prepare("SELECT id FROM questions WHERE subject = ? ORDER BY RAND() LIMIT $limit");
    $q->execute([$subject]);
    $ids = $q->fetchAll(PDO::FETCH_COLUMN);

    if (!$ids) { echo json_encode(['error' => 'No questions available for this subject yet.']); exit; }

    // Create session
    $pdo->prepare("INSERT INTO quiz_sessions (user_id, subject, total) VALUES (?,?,?)")
        ->execute([$me, $subject, count($ids)]);
    $sessionId = $pdo->lastInsertId();

    // Pre-insert answer rows (unanswered)
    $ins = $pdo->prepare("INSERT INTO quiz_answers (session_id, question_id) VALUES (?,?)");
    foreach ($ids as $qid) $ins->execute([$sessionId, $qid]);

    echo json_encode(['session_id' => $sessionId, 'total' => count($ids)]);
    exit;
}

// ── Get question ──────────────────────────────────────────────────────────────
if ($action === 'question') {
    $sessionId = (int) ($_GET['session_id'] ?? 0);
    $index     = (int) ($_GET['index'] ?? 0);   // 0-based

    // Verify session belongs to user
    $sess = $pdo->prepare("SELECT * FROM quiz_sessions WHERE id=? AND user_id=?");
    $sess->execute([$sessionId, $me]);
    $session = $sess->fetch();
    if (!$session) { echo json_encode(['error' => 'Invalid session']); exit; }

    // Get the nth answer row
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

    if (!$data) { echo json_encode(['done' => true]); exit; }

    // Never expose the answer
    unset($data['answer']);
    echo json_encode($data);
    exit;
}

// ── Submit answer ─────────────────────────────────────────────────────────────
if ($action === 'answer') {
    $answerId  = (int) ($_POST['answer_id'] ?? 0);
    $chosen    = strtolower(trim($_POST['chosen'] ?? ''));
    $hintUsed  = (int) ($_POST['hint_used'] ?? 0);

    if (!in_array($chosen, ['a','b','c','d'])) { echo json_encode(['error' => 'Invalid choice']); exit; }

    // Verify this answer row belongs to the user's session
    $row = $pdo->prepare("
        SELECT qa.*, q.answer, q.explanation
        FROM quiz_answers qa
        JOIN questions q     ON q.id  = qa.question_id
        JOIN quiz_sessions s ON s.id  = qa.session_id
        WHERE qa.id=? AND s.user_id=?
    ");
    $row->execute([$answerId, $me]);
    $data = $row->fetch();

    if (!$data) { echo json_encode(['error' => 'Invalid answer row']); exit; }
    if ($data['chosen'] !== null) { echo json_encode(['error' => 'Already answered']); exit; }

    $correct = ($chosen === $data['answer']);

    $pdo->prepare("UPDATE quiz_answers SET chosen=?, is_correct=?, hint_used=? WHERE id=?")
        ->execute([$chosen, $correct ? 1 : 0, $hintUsed, $answerId]);

    if ($correct) {
        $pdo->prepare("UPDATE quiz_sessions SET correct = correct + 1 WHERE id=?")
            ->execute([$data['session_id']]);
    }

    echo json_encode([
        'correct'     => $correct,
        'right_answer'=> $data['answer'],
        'explanation' => $data['explanation'],
    ]);
    exit;
}

// ── Finish session ────────────────────────────────────────────────────────────
if ($action === 'finish') {
    $sessionId = (int) ($_POST['session_id'] ?? 0);
    $pdo->prepare("UPDATE quiz_sessions SET finished=1 WHERE id=? AND user_id=?")
        ->execute([$sessionId, $me]);

    $sess = $pdo->prepare("SELECT subject, total, correct FROM quiz_sessions WHERE id=? AND user_id=?");
    $sess->execute([$sessionId, $me]);
    echo json_encode($sess->fetch());
    exit;
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
    echo json_encode($rows->fetchAll());
    exit;
}

echo json_encode(['error' => 'Unknown action']);
