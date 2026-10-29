<?php
$css = Url::to('/styles.css');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Multi-user personal finance manager — track income and expenses.">
    <title><?= htmlspecialchars($title ?? 'Money Tracker') ?> · Money Tracker</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💰</text></svg>">
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
</head>
<body>
    <?php require $viewFile; ?>
</body>
</html>
