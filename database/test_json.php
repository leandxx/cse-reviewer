<?php
/**
 * JSON vs Database Test
 * Visit: http://localhost/cse-reviewer/cse-reviewer/database/test_json.php
 * DELETE this file after testing!
 */
require_once '../config/db.php';

$jsonPath = __DIR__ . '/cse_exam_questions.json';
$jsonData = json_decode(file_get_contents($jsonPath), true);
$jsonCount = count($jsonData);

// DB counts
$dbTotal = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$dbBySubject = $pdo->query("SELECT subject, COUNT(*) as cnt FROM questions GROUP BY subject")->fetchAll(PDO::FETCH_KEY_PAIR);

// Sample 5 random DB questions
$dbSample = $pdo->query("SELECT id, subject, difficulty, LEFT(question,80) as question FROM questions ORDER BY RAND() LIMIT 5")->fetchAll();

// Check if any JSON questions are missing from DB (match by question text)
$missing = [];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE question = ?");
foreach ($jsonData as $q) {
    $stmt->execute([$q['question']]);
    if ($stmt->fetchColumn() == 0) {
        $missing[] = $q['question'];
    }
}

header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head><title>JSON vs DB Test</title>
<style>
  body { font-family: monospace; background:#1e1e2e; color:#cdd6f4; padding:20px; }
  h2 { color:#89b4fa; }
  .ok { color:#a6e3a1; }
  .warn { color:#f9e2af; }
  .err { color:#f38ba8; }
  table { border-collapse:collapse; width:100%; margin-top:10px; }
  td,th { border:1px solid #45475a; padding:6px 10px; text-align:left; }
  th { background:#313244; color:#89b4fa; }
  .badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:12px; }
</style>
</head>
<body>
<h2>🧪 JSON vs Database Test</h2>

<h3>📊 Counts</h3>
<table>
  <tr><th>Source</th><th>Count</th><th>Status</th></tr>
  <tr>
    <td>cse_exam_questions.json</td>
    <td><?= $jsonCount ?></td>
    <td class="ok">✔ Loaded</td>
  </tr>
  <tr>
    <td>Database (questions table)</td>
    <td><?= $dbTotal ?></td>
    <td class="<?= $dbTotal == $jsonCount ? 'ok' : 'warn' ?>">
      <?= $dbTotal == $jsonCount ? '✔ Matches JSON' : '⚠ Mismatch with JSON' ?>
    </td>
  </tr>
</table>

<h3>📂 DB Questions by Subject</h3>
<table>
  <tr><th>Subject</th><th>Count in DB</th></tr>
  <?php foreach ($dbBySubject as $subj => $cnt): ?>
  <tr><td><?= htmlspecialchars($subj) ?></td><td><?= $cnt ?></td></tr>
  <?php endforeach; ?>
</table>

<h3>🔍 JSON Questions by Subject</h3>
<table>
  <tr><th>Subject</th><th>Count in JSON</th></tr>
  <?php
  $jsonBySubject = array_count_values(array_column($jsonData, 'subject'));
  foreach ($jsonBySubject as $subj => $cnt):
  ?>
  <tr><td><?= htmlspecialchars($subj) ?></td><td><?= $cnt ?></td></tr>
  <?php endforeach; ?>
</table>

<h3>🎲 5 Random Questions Currently in DB</h3>
<table>
  <tr><th>ID</th><th>Subject</th><th>Difficulty</th><th>Question (truncated)</th></tr>
  <?php foreach ($dbSample as $row): ?>
  <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['subject'] ?></td>
    <td><?= $row['difficulty'] ?></td>
    <td><?= htmlspecialchars($row['question']) ?>…</td>
  </tr>
  <?php endforeach; ?>
</table>

<h3>❌ JSON Questions NOT Found in DB (<?= count($missing) ?>)</h3>
<?php if (empty($missing)): ?>
  <p class="ok">✔ All JSON questions exist in the database.</p>
<?php else: ?>
  <p class="err">⚠ These <?= count($missing) ?> questions from the JSON are missing in the DB — they need to be imported!</p>
  <table>
    <tr><th>#</th><th>Question</th></tr>
    <?php foreach (array_slice($missing, 0, 20) as $i => $q): ?>
    <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($q) ?></td></tr>
    <?php endforeach; ?>
    <?php if (count($missing) > 20): ?>
    <tr><td colspan="2" class="warn">… and <?= count($missing) - 20 ?> more</td></tr>
    <?php endif; ?>
  </table>

  <br>
  <form method="post" action="">
    <input type="hidden" name="do_import" value="1">
    <button type="submit" style="background:#89b4fa;color:#1e1e2e;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">
      ⬆ Import Missing Questions into DB
    </button>
  </form>
<?php endif; ?>

<?php
// Handle import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_import'])) {
    $ins = $pdo->prepare("
        INSERT INTO questions (subject, difficulty, question, choice_a, choice_b, choice_c, choice_d, answer, hint, explanation)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $saved = 0;
    foreach ($jsonData as $q) {
        try {
            $ins->execute([
                $q['subject'], $q['difficulty'], $q['question'],
                $q['choice_a'], $q['choice_b'], $q['choice_c'], $q['choice_d'],
                $q['answer'], $q['hint'] ?? '', $q['explanation'] ?? ''
            ]);
            $saved++;
        } catch (PDOException $e) { /* skip duplicates */ }
    }
    echo "<p class='ok' style='font-size:18px'>✔ Imported $saved questions! Refresh to verify.</p>";
}
?>

<br><p class="warn">⚠ Delete this file after testing: <code>database/test_json.php</code></p>
</body>
</html>
