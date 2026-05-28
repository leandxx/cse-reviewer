<?php
// Usage: set $pageTitle and $extraCss (array of css paths relative to root) before including
$pageTitle = $pageTitle ?? 'CSEReviewer';
$extraCss  = $extraCss  ?? [];
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="icon" type="image/png" href="<?= $root ?>assets/img/logo.png">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= $root ?>assets/css/global.css">
<?php foreach ($extraCss as $css): ?>
<link rel="stylesheet" href="<?= $root . $css ?>">
<?php endforeach; ?>
