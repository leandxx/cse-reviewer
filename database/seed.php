<?php
/**
 * One-time seeder — run once to load cse_exam_questions.json into the DB.
 * Access: yourdomain.com/database/seed.php
 * Delete or restrict this file after running.
 */
require_once '../config/db.php';

$file = __DIR__ . '/cse_exam_questions.json';
if (!file_exists($file)) {
    die('JSON file not found.');
}

$questions = json_decode(file_get_contents($file), true);
if (!$questions) {
    die('Failed to parse JSON.');
}

// Map general_information -> general to match DB ENUM
$subjectMap = ['general_information' => 'general'];
$validSubjects = ['verbal', 'numerical', 'analytical', 'general'];
$validDiffs    = ['easy', 'medium', 'hard'];

$ins = $pdo->prepare("
    INSERT IGNORE INTO questions
        (subject, difficulty, question, choice_a, choice_b, choice_c, choice_d, answer, hint, explanation)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$saved = 0;
$skipped = 0;

foreach ($questions as $q) {
    $subject = $subjectMap[$q['subject']] ?? $q['subject'];

    if (!in_array($subject, $validSubjects) || !in_array($q['difficulty'], $validDiffs)) {
        $skipped++;
        continue;
    }

    try {
        $ins->execute([
            $subject,
            $q['difficulty'],
            $q['question'],
            $q['choice_a'],
            $q['choice_b'],
            $q['choice_c'],
            $q['choice_d'],
            $q['answer'],
            $q['hint']        ?? '',
            $q['explanation'] ?? '',
        ]);
        $saved++;
    } catch (PDOException $e) {
        $skipped++;
    }
}

echo "Done! Saved: $saved | Skipped: $skipped | Total: " . count($questions);
