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
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; background: #e8e4dc; color: #1a1a1a; font-family: "Times New Roman", Times, serif; }
    .barra { max-width: 1100px; margin: 0 auto; padding: 16px 16px 0; display: flex; justify-content: flex-end; }
    button { font-family: "Times New Roman", Times, serif; background: #3a2418; color: #fff; border: 0; padding: 8px 14px; cursor: pointer; }
    .pagina { max-width: 1100px; margin: 0 auto; padding: 12px 16px 28px; }
    @page { size: A4 landscape; margin: 10mm; }
    @media print {
      body { background: #fff; }
      .no-print { display: none; }
      .pagina { max-width: none; padding: 0; }
    }
  </style>
</head>
<body>
  <?= $content ?>
</body>
</html>
