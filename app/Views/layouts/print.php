<?php
/** @var string $content */
/** @var string $pageTitle */
$cfg = require APP_PATH . '/Config/app.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> · <?= htmlspecialchars($cfg['unit']) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: "Source Sans 3", Georgia, sans-serif; color: #1c2414; background: #f4f6f0; }
    .folha { max-width: 800px; margin: 0 auto; padding: 24px; }
    h1 { font-family: Oswald, sans-serif; letter-spacing: 0.04em; text-transform: uppercase; font-size: 22px; margin: 0; }
    h2 { font-family: Oswald, sans-serif; text-transform: uppercase; font-size: 16px; margin: 0 0 8px; }
    .topo { display: flex; justify-content: space-between; gap: 16px; align-items: flex-end; margin-bottom: 20px; }
    .meta { font-size: 13px; color: #3a4829; }
    button { font-family: Oswald, sans-serif; letter-spacing: 0.08em; text-transform: uppercase; background: #303b24; color: #f4f6f0; border: 0; border-radius: 6px; padding: 10px 16px; cursor: pointer; }
    section { background: #fff; border: 1px solid #c9d4b0; border-radius: 10px; padding: 14px 16px; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e4ead8; }
    th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #4a5c32; }
    .total { margin-top: 8px; font-weight: 700; }
    .vazio { color: #5f7540; font-size: 14px; }
    @media print {
      body { background: #fff; }
      .no-print { display: none; }
      section { break-inside: avoid; border-color: #ccc; }
    }
  </style>
</head>
<body>
  <?= $content ?>
</body>
</html>
