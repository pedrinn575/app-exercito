<?php
/**
 * Layout autenticado — navegação + shell visual.
 * @var string $content
 * @var array|null $user
 * @var string|null $flashError
 * @var string|null $flashSuccess
 * @var string $pageTitle
 */
$cfg = require APP_PATH . '/Config/app.php';
$appName = $cfg['name'];
$unit = $cfg['unit'];
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? $appName) ?> · <?= htmlspecialchars($appName) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            olive: {
              50:  '#f4f6f0',
              100: '#e4ead8',
              200: '#c9d4b0',
              300: '#a3b582',
              400: '#7d9458',
              500: '#5f7540',
              600: '#4a5c32',
              700: '#3a4829',
              800: '#303b24',
              900: '#2a3221',
              950: '#151a10',
            },
            khaki: {
              100: '#f3efe4',
              200: '#e6dcc4',
              300: '#d4c29a',
              400: '#c4a86e',
              500: '#b08f4f',
            },
            crimson: {
              500: '#b91c1c',
              600: '#991b1b',
              700: '#7f1d1d',
            }
          },
          fontFamily: {
            display: ['Oswald', 'sans-serif'],
            sans: ['"Source Sans 3"', 'system-ui', 'sans-serif'],
          },
          backgroundImage: {
            'parade': 'radial-gradient(ellipse at 20% 0%, rgba(125,148,88,0.18), transparent 50%), radial-gradient(ellipse at 80% 100%, rgba(176,143,79,0.12), transparent 45%), linear-gradient(160deg, #151a10 0%, #1f2818 40%, #24301c 100%)',
            'field': 'linear-gradient(180deg, #f4f6f0 0%, #e8ecdf 100%)',
          },
          boxShadow: {
            'panel': '0 1px 0 rgba(255,255,255,0.06) inset, 0 18px 40px -24px rgba(0,0,0,0.55)',
          },
          keyframes: {
            rise: {
              '0%': { opacity: '0', transform: 'translateY(12px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' },
            },
            sweep: {
              '0%': { transform: 'scaleX(0)' },
              '100%': { transform: 'scaleX(1)' },
            }
          },
          animation: {
            rise: 'rise 0.55s ease-out both',
            'rise-delay': 'rise 0.55s ease-out 0.12s both',
            'rise-delay-2': 'rise 0.55s ease-out 0.24s both',
            sweep: 'sweep 0.7s ease-out both',
          }
        }
      }
    }
  </script>
  <style>
    .camo-noise {
      background-image:
        repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.03) 2px, rgba(0,0,0,0.03) 4px),
        repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(255,255,255,0.02) 3px, rgba(255,255,255,0.02) 6px);
    }
    .nav-link.active {
      background: rgba(125, 148, 88, 0.18);
      color: #e4ead8;
      box-shadow: inset 3px 0 0 #7d9458;
    }
    /* Menu retrátil no celular, sem JavaScript */
    #menu-mobile:checked ~ .app-shell .sidebar { display: flex; }
    #menu-mobile:checked ~ .app-shell .icone-abrir { display: none; }
    #menu-mobile:checked ~ .app-shell .icone-fechar { display: block; }
    @media (min-width: 1024px) {
      .sidebar { display: flex !important; }
    }
    /* Itens de grid/flex nascem com largura mínima do conteúdo; sem isso um
       painel com tabela larga estica a página inteira e corta a tela. */
    /* Especificidade zero: quem declarar min-w no HTML continua mandando. */
    :where(main) :where(div, section, article, aside, form, ul, li, p, h1, h2, h3, h4) {
      min-width: 0;
    }
  </style>
</head>
<body class="font-sans bg-field text-olive-950 min-h-screen antialiased">
  <input type="checkbox" id="menu-mobile" class="hidden peer">

  <div class="app-shell min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
    <!-- Barra do celular -->
    <div class="lg:hidden sticky top-0 z-30 flex items-center justify-between gap-3 bg-olive-950 text-olive-100 px-4 py-3">
      <div class="min-w-0">
        <p class="font-display tracking-[0.2em] text-[10px] uppercase text-khaki-400 truncate"><?= htmlspecialchars($unit) ?></p>
        <h1 class="font-display text-lg leading-tight text-white truncate"><?= htmlspecialchars($pageTitle ?? $appName) ?></h1>
      </div>
      <label for="menu-mobile" class="shrink-0 cursor-pointer rounded-md border border-white/20 p-2 hover:bg-white/10 transition" aria-label="Abrir menu">
        <svg class="icone-abrir w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
        </svg>
        <svg class="icone-fechar hidden w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
        </svg>
      </label>
    </div>

    <!-- Menu lateral -->
    <aside class="sidebar hidden bg-parade text-olive-100 camo-noise relative flex-col lg:sticky lg:top-0 lg:h-screen">
      <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-khaki-500 via-olive-400 to-transparent origin-left animate-sweep"></div>
      <div class="p-6 border-b border-white/10">
        <p class="hidden lg:block font-display tracking-[0.25em] text-xs uppercase text-khaki-400"><?= htmlspecialchars($unit) ?></p>
        <h1 class="hidden lg:block font-display text-2xl tracking-wide text-white mt-1 leading-none"><?= htmlspecialchars($appName) ?></h1>
        <?php if ($user): ?>
          <p class="lg:mt-4 text-sm text-olive-200/80">
            <span class="text-khaki-300 font-medium"><?= htmlspecialchars($user['numero']) ?></span>
            · <?= htmlspecialchars($user['nome']) ?>
          </p>
          <span class="inline-block mt-2 text-[10px] uppercase tracking-widest px-2 py-0.5 rounded bg-olive-700/60 text-olive-100 border border-olive-500/30">
            <?= htmlspecialchars($user['perfil']) ?>
          </span>
        <?php endif; ?>
      </div>
      <nav class="p-3 space-y-1 text-sm flex-1 overflow-y-auto">
        <?php
        // Cada perfil enxerga o que estiver liberado na tela de Permissões
        $menu = (new \App\Services\PermissaoService())->menuDoPerfil($user['perfil'] ?? '');
        foreach ($menu as $item):
          $href = $item['rota'];
          $active = str_starts_with($current, $href) || ($href === '/dashboard' && ($current === '/' || $current === '/dashboard'));
        ?>
          <a href="<?= $href ?>" class="nav-link block px-3 py-2.5 rounded-md text-olive-200/90 hover:bg-white/5 hover:text-white transition <?= $active ? 'active' : '' ?>">
            <?= htmlspecialchars($item['label']) ?>
          </a>
        <?php endforeach; ?>
      </nav>
      <div class="p-4 border-t border-white/10">
        <form method="post" action="/logout">
          <button type="submit" class="w-full text-left text-sm text-olive-300 hover:text-white transition px-2 py-2">
            Encerrar sessão
          </button>
        </form>
      </div>
    </aside>

    <!-- Conteúdo -->
    <div class="flex flex-col min-h-screen min-w-0">
      <header class="hidden lg:flex sticky top-0 z-20 backdrop-blur-md bg-olive-50/80 border-b border-olive-200/80 px-6 py-4 items-center justify-between">
        <div>
          <h2 class="font-display text-xl tracking-wide text-olive-900 uppercase"><?= htmlspecialchars($pageTitle ?? '') ?></h2>
          <p class="text-xs text-olive-600/80 mt-0.5"><?= date('d/m/Y') ?> · Intervalo mínimo entre serviços: 48h</p>
        </div>
      </header>

      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <?php if ($flashSuccess): ?>
          <div class="mb-6 animate-rise rounded-md border border-olive-300 bg-olive-100 px-4 py-3 text-olive-800 text-sm">
            <?= htmlspecialchars($flashSuccess) ?>
          </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
          <div class="mb-6 animate-rise rounded-md border border-crimson-600/30 bg-red-50 px-4 py-3 text-crimson-700 text-sm">
            <?= htmlspecialchars($flashError) ?>
          </div>
        <?php endif; ?>

        <div class="animate-rise">
          <?= $content ?>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
