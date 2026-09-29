<?php
/** @var Throwable $e */
$status = http_response_code() ?: 500;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Erro <?= (int)$status ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@600&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen flex items-center justify-center bg-[#f4f6f0] font-['Source_Sans_3'] p-6">
  <div class="max-w-md text-center">
    <p class="font-['Oswald'] text-6xl text-[#3a4829]"><?= (int)$status ?></p>
    <p class="mt-3 text-[#4a5c32]"><?= htmlspecialchars($e->getMessage()) ?></p>
    <a href="/dashboard" class="inline-block mt-6 text-sm uppercase tracking-wider text-[#5f7540] hover:underline">Voltar ao início</a>
  </div>
</body>
</html>
