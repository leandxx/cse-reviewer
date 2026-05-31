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

set_exception_handler(function($e) {
    send(['error' => $e->getMessage()]);
});

if (!isset($_SESSION['user_id'])) {
    send(['error' => 'Unauthorized']);
}

require_once '../config/db.php';

$me     = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'start') {
    $subject = $_POST['subject'] ?? 'verbal';
    $limit   = min((int) ($_POST['limit'] ?? 10), 100);

    $valid = ['verbal', 'numerical', 'analytical', 'general', 'general_information', 'all'];
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

if ($action === 'question') {
    $sessionId = (int) ($_GET['session_id'] ?? 0);
    $index     = (int) ($_GET['index'] ?? 0);

    $sess = $pdo->prepare("SELECT id FROM quiz_sessions WHERE id=? AND user_id=?");
    $sess->execute([$sessionId, $me]);
    if (!$sess->fetch()) send(['error' => 'Invalid session']);

    $sql = "
        SELECT qa.id AS answer_id, qa.chosen, qa.hint_used,
               q.id, q.question, q.choice_a, q.choice_b, q.choice_c, q.choice_d,
               q.hint, q.subject, q.difficulty
        FROM quiz_answers qa
        JOIN questions q ON q.id = qa.question_id
        WHERE qa.session_id = ?
        ORDER BY qa.id ASC
        LIMIT 1 OFFSET $index
    ";
    $row = $pdo->prepare($sql);
    $row->execute([$sessionId]);
    $data = $row->fetch();

    if (!$data) send(['done' => true]);

    send($data);
}

if ($action === 'answer') {
    $answerId  = (int) ($_POST['answer_id'] ?? 0);
    $chosen    = strtolower(trim($_POST['chosen'] ?? ''));
    $hintUsed  = (int) ($_POST['hint_used'] ?? 0);
    $timeSpent = isset($_POST['time_spent']) ? (int) $_POST['time_spent'] : null;

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

    $pdo->prepare("UPDATE quiz_answers SET chosen=?, is_correct=?, hint_used=?, time_spent=? WHERE id=?")
        ->execute([$chosen, $correct ? 1 : 0, $hintUsed, $timeSpent, $answerId]);

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

if ($action === 'finish') {
    $sessionId = (int) ($_POST['session_id'] ?? 0);
    $pdo->prepare("UPDATE quiz_sessions SET finished=1 WHERE id=? AND user_id=?")
        ->execute([$sessionId, $me]);

    $sess = $pdo->prepare("SELECT subject, total, correct FROM quiz_sessions WHERE id=? AND user_id=?");
    $sess->execute([$sessionId, $me]);
    $result = $sess->fetch();

    // Award XP: 10 per correct answer
    $xpEarned = $result['correct'] * 10;
    if ($xpEarned > 0) {
        $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id=?")->execute([$xpEarned, $me]);
    }
    $result['xp_earned'] = $xpEarned;

    // Award coins based on score %
    $pct = $result['total'] > 0 ? ($result['correct'] / $result['total']) * 100 : 0;
    if      ($pct >= 90) $coins = 50;
    elseif  ($pct >= 75) $coins = 30;
    elseif  ($pct >= 60) $coins = 15;
    elseif  ($pct >= 50) $coins = 8;
    else                 $coins = 3;
    // Bonus for full mock exam (100 questions)
    if ($result['total'] >= 100) $coins += 10;
    $pdo->prepare("UPDATE users SET coins = coins + ? WHERE id=?")->execute([$coins, $me]);
    $result['coins_earned'] = $coins;
    send($result);
}

if ($action === 'review') {
    $sessionId = (int) ($_GET['session_id'] ?? 0);

    $sess = $pdo->prepare("SELECT id, total, correct FROM quiz_sessions WHERE id=? AND user_id=? AND finished=1");
    $sess->execute([$sessionId, $me]);
    $session = $sess->fetch();
    if (!$session) send(['error' => 'Session not found']);

    $rows = $pdo->prepare("
        SELECT q.question, q.choice_a, q.choice_b, q.choice_c, q.choice_d, q.answer, q.explanation, qa.chosen
        FROM quiz_answers qa
        JOIN questions q ON q.id = qa.question_id
        WHERE qa.session_id = ? AND qa.is_correct = 0 AND qa.chosen IS NOT NULL
        ORDER BY qa.id ASC
    ");
    $rows->execute([$sessionId]);
    send([
        'total' => $session['total'],
        'wrong' => $session['total'] - $session['correct'],
        'items' => $rows->fetchAll(),
    ]);
}

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
