<?php
/**
 * Layout convidado (login).
 * @var string $content
 * @var string|null $flashError
 * @var string|null $flashSuccess
 */
$cfg = require APP_PATH . '/Config/app.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acesso · <?= htmlspecialchars($cfg['name']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            olive: { 100:'#e4ead8',200:'#c9d4b0',400:'#7d9458',600:'#4a5c32',800:'#303b24',900:'#2a3221',950:'#151a10' },
            khaki: { 300:'#d4c29a',400:'#c4a86e',500:'#b08f4f' }
          },
          fontFamily: {
            display: ['Oswald', 'sans-serif'],
            sans: ['"Source Sans 3"', 'sans-serif'],
          },
          keyframes: {
            rise: { '0%':{opacity:0,transform:'translateY(16px)'}, '100%':{opacity:1,transform:'translateY(0)'} },
            drift: { '0%,100%':{transform:'translateY(0)'}, '50%':{transform:'translateY(-8px)'} }
          },
          animation: {
            rise: 'rise 0.7s ease-out both',
            drift: 'drift 8s ease-in-out infinite'
          }
        }
      }
    }
  </script>
</head>
<body class="font-sans min-h-screen text-olive-100 antialiased">
  <div class="min-h-screen grid lg:grid-cols-2">
    <!-- Painel visual full-bleed -->
    <section class="relative hidden lg:flex flex-col justify-end p-12 overflow-hidden"
             style="background:
               linear-gradient(160deg, rgba(21,26,16,0.55), rgba(21,26,16,0.92)),
               radial-gradient(ellipse at 30% 20%, rgba(176,143,79,0.25), transparent 50%),
               linear-gradient(145deg, #2a3221 0%, #151a10 55%, #0c1009 100%);">
      <div class="absolute inset-0 opacity-30"
           style="background-image: repeating-linear-gradient(45deg, transparent, transparent 12px, rgba(125,148,88,0.08) 12px, rgba(125,148,88,0.08) 24px);"></div>
      <div class="relative animate-drift max-w-md">
        <p class="font-display tracking-[0.35em] text-xs uppercase text-khaki-400 mb-3"><?= htmlspecialchars($cfg['unit']) ?></p>
        <h1 class="font-display text-5xl xl:text-6xl leading-none text-white tracking-wide">
          Sistema de<br>Escalas
        </h1>
        <p class="mt-5 text-olive-200/90 text-lg leading-relaxed">
          Preta, vermelha, trocas e faltas — sob comando único do Subtenente.
        </p>
      </div>
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-khaki-500 via-olive-400 to-transparent"></div>
    </section>

    <section class="flex items-center justify-center p-8 bg-[#f4f6f0]">
      <div class="w-full max-w-md animate-rise">
        <div class="lg:hidden mb-10">
          <p class="font-display tracking-[0.3em] text-xs uppercase text-olive-600"><?= htmlspecialchars($cfg['unit']) ?></p>
          <h1 class="font-display text-3xl text-olive-900 tracking-wide">Sistema de Escalas</h1>
        </div>

        <?php if ($flashSuccess): ?>
          <div class="mb-4 rounded-md border border-olive-300 bg-olive-100 px-4 py-3 text-olive-800 text-sm"><?= htmlspecialchars($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
          <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-red-800 text-sm"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>

        <?= $content ?>
      </div>
    </section>
  </div>
</body>
</html>
