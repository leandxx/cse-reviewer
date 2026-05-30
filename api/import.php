<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once '../config/db.php';
require_once '../lib/PdfExtractor.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ── Upload & Extract ──────────────────────────────────────────────────────────
if ($action === 'upload') {
    if (empty($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'No file uploaded or upload error.']); exit;
    }

    $file = $_FILES['pdf'];
    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        echo json_encode(['error' => 'Only PDF files are allowed.']); exit;
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['error' => 'File too large. Max 10MB.']); exit;
    }

    try {
        $text = PdfExtractor::extract($file['tmp_name']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'PDF extraction failed: ' . $e->getMessage()]); exit;
    }

    if (strlen(trim($text)) < 100) {
        echo json_encode(['error' => 'Could not extract readable text from this PDF. It may be a scanned/image-based PDF.']); exit;
    }

    // Limit text to avoid huge API calls — take first ~12000 chars
    $text = substr($text, 0, 12000);

    echo json_encode(['ok' => true, 'text' => $text, 'length' => strlen($text)]);
    exit;
}

// ── Analyze with Gemini ───────────────────────────────────────────────────────
if ($action === 'analyze') {
    $text    = trim($_POST['text'] ?? '');
    $subject = $_POST['subject'] ?? 'verbal';

    if (!$text) { echo json_encode(['error' => 'No text provided.']); exit; }

    $apiKey = getenv('GEMINI_API_KEY');
    if (!$apiKey || $apiKey === 'your_gemini_api_key_here') {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (!str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                if (trim($k) === 'GEMINI_API_KEY') { $apiKey = trim($v); break; }
            }
        }
    }
    if (!$apiKey || $apiKey === 'your_gemini_api_key_here') {
        echo json_encode(['error' => 'Gemini API key not configured. Add GEMINI_API_KEY to your .env file.']); exit;
    }

    $prompt = <<<PROMPT
You are a Philippine Civil Service Exam (CSE) question analyzer.

From the text below, extract as many multiple-choice questions as you can find (up to 30).
For each question:
1. Identify the question text
2. Identify the 4 choices (A, B, C, D)
3. Determine the correct answer
4. Write a short hint (one sentence, does not give away the answer)
5. Write a clear explanation of why the answer is correct (2-3 sentences)
6. Classify the subject as one of: verbal, numerical, analytical, general
7. Classify difficulty as: easy, medium, hard

Return ONLY a valid JSON array. No markdown, no extra text. Format:
[
  {
    "question": "...",
    "choice_a": "...",
    "choice_b": "...",
    "choice_c": "...",
    "choice_d": "...",
    "answer": "a",
    "hint": "...",
    "explanation": "...",
    "subject": "verbal",
    "difficulty": "medium"
  }
]

TEXT:
$text
PROMPT;

    $payload = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature'     => 0.2,
            'maxOutputTokens' => 8192,
        ]
    ]);

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 60,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response || $httpCode !== 200) {
        $errDetail = json_decode($response, true);
        $errMsg = $errDetail['error']['message'] ?? ('HTTP ' . $httpCode);
        echo json_encode(['error' => 'Gemini API error: ' . $errMsg]); exit;
    }

    $gemini = json_decode($response, true);
    $raw    = $gemini['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // Strip markdown code fences if present
    $raw = preg_replace('/^```json\s*/i', '', trim($raw));
    $raw = preg_replace('/\s*```$/', '', $raw);

    $questions = json_decode($raw, true);
    if (!is_array($questions)) {
        echo json_encode(['error' => 'Gemini returned invalid JSON. Try a different PDF or subject.', 'raw' => substr($raw, 0, 500)]); exit;
    }

    // Validate & sanitize each question
    $valid    = ['a','b','c','d'];
    $subjects = ['verbal','numerical','analytical','general'];
    $diffs    = ['easy','medium','hard'];
    $clean    = [];

    foreach ($questions as $q) {
        if (empty($q['question']) || empty($q['choice_a']) || empty($q['choice_b']) ||
            empty($q['choice_c']) || empty($q['choice_d']) || empty($q['answer'])) continue;
        if (!in_array(strtolower($q['answer']), $valid)) continue;

        $clean[] = [
            'question'   => trim($q['question']),
            'choice_a'   => trim($q['choice_a']),
            'choice_b'   => trim($q['choice_b']),
            'choice_c'   => trim($q['choice_c']),
            'choice_d'   => trim($q['choice_d']),
            'answer'     => strtolower($q['answer']),
            'hint'       => trim($q['hint'] ?? ''),
            'explanation'=> trim($q['explanation'] ?? ''),
            'subject'    => in_array($q['subject'] ?? '', $subjects) ? $q['subject'] : $subject,
            'difficulty' => in_array($q['difficulty'] ?? '', $diffs) ? $q['difficulty'] : 'medium',
        ];
    }

    if (!$clean) {
        echo json_encode(['error' => 'No valid questions found in this PDF. Try a different file.']); exit;
    }

    echo json_encode(['ok' => true, 'questions' => $clean]);
    exit;
}

// ── Save to DB ────────────────────────────────────────────────────────────────
if ($action === 'save') {
    $raw = $_POST['questions'] ?? '';
    $questions = json_decode($raw, true);

    if (!is_array($questions) || !$questions) {
        echo json_encode(['error' => 'No questions to save.']); exit;
    }

    $ins = $pdo->prepare("
        INSERT INTO questions (subject, difficulty, question, choice_a, choice_b, choice_c, choice_d, answer, hint, explanation)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $saved = 0;
    foreach ($questions as $q) {
        try {
            $ins->execute([
                $q['subject'], $q['difficulty'], $q['question'],
                $q['choice_a'], $q['choice_b'], $q['choice_c'], $q['choice_d'],
                $q['answer'], $q['hint'], $q['explanation']
            ]);
            $saved++;
        } catch (PDOException $e) {
            // Skip duplicates or bad rows
        }
    }

    echo json_encode(['ok' => true, 'saved' => $saved]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
