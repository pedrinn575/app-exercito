<?php
/** @var string $mes */
/** @var array<string, list<array{tipo:string,total:int}>> $mapa */
/** @var array<string, string> $tipos */
/** @var array{monitores:int,atiradores:int} $efetivo */
/** @var array<string, array{tipo:string,funcao:string}> $meusDias */
/** @var list<array{id:int,data:string,nome:string}> $feriados */
use App\Utils\Auth;
use App\Utils\Calendar;
$inicio = strtotime($mes . '-01');
$diasNoMes = (int) date('t', $inicio);
$offset = (int) date('N', $inicio) - 1;
$hoje = date('Y-m-d');
$feriadosPorData = [];
foreach ($feriados as $f) {
    $feriadosPorData[$f['data']] = $f['nome'];
}
$semana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
  <div class="flex items-center gap-3">
    <a href="/calendario?mes=<?= Calendar::shiftMonth($mes, -1) ?>"
       class="px-3 py-2 rounded-md border border-olive-300 text-olive-800 hover:bg-white text-sm">←</a>
    <h3 class="font-display text-xl sm:text-2xl tracking-wide uppercase text-olive-900"><?= Calendar::monthBr($mes) ?></h3>
    <a href="/calendario?mes=<?= Calendar::shiftMonth($mes, 1) ?>"
       class="px-3 py-2 rounded-md border border-olive-300 text-olive-800 hover:bg-white text-sm">→</a>
  </div>
  <?php if (Auth::isAdmin()): ?>
    <form method="post" action="/calendario/gerar" class="flex flex-wrap items-end gap-3 rounded-xl bg-white/80 border border-olive-200/80 p-3 shadow-panel">
      <input type="hidden" name="mes" value="<?= htmlspecialchars($mes) ?>">
      <label class="block">
        <span class="block text-[10px] uppercase tracking-widest text-olive-600 mb-1">Monitores/dia</span>
        <input type="number" name="monitores" min="0" max="150" value="<?= (int) $efetivo['monitores'] ?>"
               class="w-24 rounded-md border border-olive-300 px-3 py-2 text-sm">
      </label>
      <label class="block">
        <span class="block text-[10px] uppercase tracking-widest text-olive-600 mb-1">Atiradores/dia</span>
        <input type="number" name="atiradores" min="0" max="150" value="<?= (int) $efetivo['atiradores'] ?>"
               class="w-24 rounded-md border border-olive-300 px-3 py-2 text-sm">
      </label>
      <label class="flex items-center gap-2 text-xs text-olive-700 pb-2.5">
        <input type="checkbox" name="substituir" value="1" class="rounded">
        Refazer dias já gerados
      </label>
      <button class="font-display tracking-wider uppercase text-sm px-4 py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition">
        Gerar mês
      </button>
    </form>
  <?php endif; ?>
</div>

<?php if ($meusDias): ?>
  <div class="mb-5 rounded-xl border-2 border-khaki-500 bg-khaki-200 px-4 py-4 shadow-panel">
    <p class="font-display uppercase tracking-[0.14em] text-olive-950 text-sm sm:text-base">Seu dia de escala</p>
    <p class="text-sm text-olive-800 mt-1">Estes são os dias em que você está na escala neste mês.</p>
    <div class="mt-3 flex flex-wrap gap-2">
      <?php foreach ($meusDias as $dataMeu => $info): ?>
        <a href="/escalas/<?= htmlspecialchars($info['tipo']) ?>?data=<?= urlencode($dataMeu) ?>"
           class="inline-flex items-center gap-2 rounded-full bg-olive-950 text-khaki-100 px-3 py-1.5 text-sm font-display tracking-wide hover:bg-olive-800 transition">
          <span><?= Calendar::formatBr($dataMeu) ?></span>
          <span class="text-[10px] uppercase tracking-wider text-khaki-300"><?= $info['tipo'] === 'vermelha' ? 'Vermelha' : 'Preta' ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php elseif (!Auth::isAdmin()): ?>
  <div class="mb-5 rounded-xl border border-olive-200 bg-white/80 px-4 py-3 text-sm text-olive-700">
    Você não está escalado em nenhum dia deste mês.
  </div>
<?php endif; ?>

<div class="rounded-xl bg-white/80 border border-olive-200/80 shadow-panel p-2 sm:p-4 overflow-x-auto">
  <div class="grid grid-cols-7 gap-1.5 sm:gap-2 min-w-[560px] lg:min-w-[720px]">
    <?php foreach ($semana as $dia): ?>
      <div class="text-[10px] sm:text-[11px] uppercase tracking-widest text-olive-500 px-1 sm:px-2 py-1"><?= $dia ?></div>
    <?php endforeach; ?>

    <?php for ($i = 0; $i < $offset; $i++): ?>
      <div class="min-h-[72px] sm:min-h-[96px] rounded-lg bg-olive-50/40"></div>
    <?php endfor; ?>

    <?php for ($d = 1; $d <= $diasNoMes; $d++):
      $data = sprintf('%s-%02d', $mes, $d);
      $sugerido = $tipos[$data] ?? 'preta';
      $itens = array_values(array_filter(
          $mapa[$data] ?? [],
          fn(array $item) => (int) $item['total'] > 0
      ));
      // O clique abre a escala que existe. A sugestão só vale se o dia ainda está vazio.
      $destino = count($itens) === 1 ? $itens[0]['tipo'] : (count($itens) === 0 ? $sugerido : null);
      $vermelha = $destino === 'vermelha' || ($destino === null && $sugerido === 'vermelha');
      $isHoje = $data === $hoje;
      $nomeFeriado = $feriadosPorData[$data] ?? null;
      $meu = $meusDias[$data] ?? null;
      if ($meu) {
          $destino = $meu['tipo'];
      }
      $classeDia = 'rounded-lg border p-1.5 sm:p-2 flex flex-col transition hover:-translate-y-0.5 hover:shadow-md '
          . ($meu
              ? 'min-h-[104px] sm:min-h-[124px] bg-khaki-300 border-2 border-olive-950 ring-2 ring-khaki-500 shadow-md'
              : ($destino === null
                  ? 'min-h-[72px] sm:min-h-[96px] bg-gradient-to-br from-red-50/90 to-olive-50 border-red-200'
                  : ('min-h-[72px] sm:min-h-[96px] ' . ($vermelha ? 'bg-red-50/80 border-red-200 hover:border-crimson-600' : 'bg-olive-50/70 border-olive-200 hover:border-olive-500'))))
          . ($isHoje && !$meu ? ' ring-2 ring-khaki-500' : '');
    ?>
      <?php if ($destino !== null): ?>
      <a href="/escalas/<?= $destino ?>?data=<?= $data ?>" class="<?= $classeDia ?>">
      <?php else: ?>
      <div class="<?= $classeDia ?>">
      <?php endif; ?>
        <?php if ($meu): ?>
          <span class="mb-1 inline-block self-start max-w-full rounded bg-olive-950 px-1.5 py-1 text-[9px] sm:text-[10px] font-display uppercase tracking-wide text-khaki-200 leading-tight">
            Seu dia de escala
          </span>
        <?php endif; ?>
        <div class="flex items-start justify-between gap-1">
          <span class="font-display text-base sm:text-lg leading-none <?= $meu ? 'text-olive-950' : ($vermelha || $destino === null ? 'text-crimson-700' : 'text-olive-900') ?>"><?= $d ?></span>
          <span class="text-[9px] sm:text-[10px] uppercase tracking-wider text-right <?= $meu ? 'text-olive-900 font-semibold' : ($vermelha ? 'text-crimson-600' : 'text-olive-600') ?>">
            <?php if ($destino === null): ?>
              Preta e vermelha
            <?php else: ?>
              <?= $destino === 'vermelha' ? 'Verm' : 'Preta' ?>
            <?php endif; ?>
          </span>
        </div>
        <?php if ($nomeFeriado): ?>
          <p class="mt-1 text-[10px] leading-tight text-crimson-700"><?= htmlspecialchars($nomeFeriado) ?></p>
        <?php endif; ?>
        <div class="mt-auto pt-1.5 sm:pt-2 space-y-0.5">
          <?php if (!$itens): ?>
            <p class="text-[9px] sm:text-[10px] text-olive-400">Sem escala</p>
          <?php else: foreach ($itens as $item): ?>
            <?php if ($destino === null): ?>
              <a href="/escalas/<?= $item['tipo'] ?>?data=<?= $data ?>"
                 class="block text-[10px] sm:text-[11px] font-medium underline-offset-2 hover:underline <?= $item['tipo'] === 'vermelha' ? 'text-crimson-700' : 'text-olive-800' ?>">
                <?= $item['tipo'] === 'vermelha' ? 'Vermelha' : 'Preta' ?> · <?= (int) $item['total'] ?>
              </a>
            <?php else: ?>
              <p class="text-[10px] sm:text-[11px] font-medium <?= $item['tipo'] === 'vermelha' ? 'text-crimson-700' : 'text-olive-800' ?>">
                <?= (int) $item['total'] ?> postos
              </p>
            <?php endif; ?>
          <?php endforeach; endif; ?>
        </div>
      <?php if ($destino !== null): ?>
      </a>
      <?php else: ?>
      </div>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
</div>

<section class="mt-6 grid lg:grid-cols-2 gap-6">
  <article class="rounded-xl bg-olive-900 text-olive-100 p-5 shadow-panel">
    <h3 class="font-display uppercase tracking-wide">Como o mês é montado</h3>
    <ul class="mt-3 space-y-2 text-sm text-olive-200/90">
      <li>Você escolhe quantos monitores e atiradores entram por dia.</li>
      <li>Dia útil fica na preta. Sábado, domingo e feriado viram vermelha de 24h.</li>
      <li>A fila é uma só. Quem serviu não volta antes de completar as 48h.</li>
      <li>Para mudar um dia específico, abra a escala e edite posto a posto.</li>
    </ul>
  </article>

  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-olive-900">Feriados da unidade</h3>
    <p class="text-xs text-olive-600 mt-1">Além dos feriados nacionais fixos. O dia cadastrado entra na vermelha.</p>

    <?php if (Auth::isAdmin()): ?>
      <form method="post" action="/feriados" class="mt-4 grid gap-2 sm:grid-cols-[1fr_1.4fr_auto]">
        <input type="date" name="data" required class="w-full rounded-md border border-olive-300 px-2 py-2 text-sm">
        <input type="text" name="nome" required placeholder="Nome do feriado" class="w-full rounded-md border border-olive-300 px-2 py-2 text-sm">
        <button class="px-3 py-2 rounded-md bg-crimson-700 text-white text-xs uppercase tracking-wider hover:bg-crimson-600">Incluir</button>
      </form>
    <?php endif; ?>

    <ul class="mt-4 divide-y divide-olive-100 text-sm">
      <?php if (!$feriados): ?>
        <li class="py-3 text-olive-500">Nenhum feriado extra cadastrado.</li>
      <?php endif; ?>
      <?php foreach ($feriados as $f): ?>
        <li class="py-2.5 flex items-center justify-between gap-3">
          <span>
            <strong class="text-olive-900"><?= Calendar::formatBr($f['data']) ?></strong>
            <span class="text-olive-600"> · <?= htmlspecialchars($f['nome']) ?></span>
          </span>
          <?php if (Auth::isAdmin()): ?>
            <form method="post" action="/feriados/<?= (int) $f['id'] ?>/remover">
              <input type="hidden" name="mes" value="<?= htmlspecialchars($mes) ?>">
              <button class="text-xs text-crimson-700 hover:underline">Remover</button>
            </form>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </article>
</section>
