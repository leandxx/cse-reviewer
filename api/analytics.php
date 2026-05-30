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

// Accuracy per subject from finished quiz_sessions
$rows = $pdo->prepare("
    SELECT subject,
           SUM(total)   AS total,
           SUM(correct) AS correct
    FROM quiz_sessions
    WHERE user_id = ? AND finished = 1
    GROUP BY subject
");
$rows->execute([$me]);
$bySubject = $rows->fetchAll();

$accuracy = [];
foreach ($bySubject as $r) {
    $accuracy[$r['subject']] = [
        'total'    => (int) $r['total'],
        'correct'  => (int) $r['correct'],
        'accuracy' => $r['total'] > 0 ? round($r['correct'] / $r['total'] * 100, 1) : 0,
    ];
}

// Weakest / strongest
$weakest = $strongest = null;
foreach ($accuracy as $subj => $data) {
    if ($data['total'] === 0) continue;
    if ($weakest   === null || $data['accuracy'] < $accuracy[$weakest]['accuracy'])   $weakest   = $subj;
    if ($strongest === null || $data['accuracy'] > $accuracy[$strongest]['accuracy']) $strongest = $subj;
}

// Avg time spent per subject (seconds) — uses time_spent column if present
$timeRows = $pdo->prepare("
    SELECT qs.subject, AVG(qa.time_spent) AS avg_time
    FROM quiz_answers qa
    JOIN quiz_sessions qs ON qs.id = qa.session_id
    WHERE qs.user_id = ? AND qa.time_spent IS NOT NULL AND qa.time_spent > 0
    GROUP BY qs.subject
");
$timeRows->execute([$me]);
$timeData = [];
foreach ($timeRows->fetchAll() as $r) {
    $timeData[$r['subject']] = round((float) $r['avg_time'], 1);
}

send([
    'accuracy'  => $accuracy,
    'weakest'   => $weakest,
    'strongest' => $strongest,
    'time'      => $timeData,
]);
