<?php
/** @var array $metricas */
/** @var list<\App\Entities\Escala> $pretas */
/** @var list<\App\Entities\Escala> $vermelhas */
/** @var list<array> $trocas */
/** @var list<array> $faltas */
use App\Utils\Calendar;
?>
<section class="grid md:grid-cols-3 gap-4 mb-8">
  <article class="rounded-xl bg-olive-900 text-olive-50 p-5 shadow-panel">
    <p class="text-xs uppercase tracking-wider text-olive-300">Pretas no mês</p>
    <p class="font-display text-4xl mt-1"><?= (int)$metricas['escalas_preta_mes'] ?></p>
  </article>
  <article class="rounded-xl bg-crimson-700 text-white p-5 shadow-panel">
    <p class="text-xs uppercase tracking-wider text-red-100">Vermelhas no mês</p>
    <p class="font-display text-4xl mt-1"><?= (int)$metricas['escalas_vermelha_mes'] ?></p>
  </article>
  <article class="rounded-xl bg-white border border-olive-200 p-5 shadow-panel">
    <p class="text-xs uppercase tracking-wider text-olive-600">Faltas injustificadas</p>
    <p class="font-display text-4xl mt-1 text-crimson-700"><?= (int)$metricas['faltas_injustificadas'] ?></p>
  </article>
</section>

<div class="grid lg:grid-cols-2 gap-6">
  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-olive-900 mb-3">Resumo escalas pretas</h3>
    <ul class="space-y-2 text-sm max-h-72 overflow-y-auto">
      <?php foreach ($pretas as $e): ?>
        <li class="flex justify-between border-b border-olive-100 py-2">
          <a class="text-olive-800 hover:underline" href="/escalas/preta?data=<?= urlencode($e->dataServico) ?>">
            <?= Calendar::formatBr($e->dataServico) ?>
          </a>
          <span class="text-olive-500"><?= count($e->postos) ?> postos</span>
        </li>
      <?php endforeach; ?>
      <?php if (!$pretas): ?><li class="text-olive-500">Sem registros.</li><?php endif; ?>
    </ul>
  </article>

  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-olive-900 mb-3">Resumo escalas vermelhas</h3>
    <ul class="space-y-2 text-sm max-h-72 overflow-y-auto">
      <?php foreach ($vermelhas as $e): ?>
        <li class="flex justify-between border-b border-olive-100 py-2">
          <a class="text-crimson-700 hover:underline" href="/escalas/vermelha?data=<?= urlencode($e->dataServico) ?>">
            <?= Calendar::formatBr($e->dataServico) ?>
          </a>
          <span class="text-olive-500"><?= count($e->postos) ?> postos</span>
        </li>
      <?php endforeach; ?>
      <?php if (!$vermelhas): ?><li class="text-olive-500">Sem registros.</li><?php endif; ?>
    </ul>
  </article>

  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel lg:col-span-2">
    <h3 class="font-display uppercase tracking-wide text-olive-900 mb-3">Trocas · <?= count($trocas) ?> registro(s)</h3>
    <p class="text-sm text-olive-600">Pendentes de ação: <strong><?= (int)$metricas['trocas_pendentes'] ?></strong></p>
    <p class="text-sm text-olive-600 mt-1">Faltas totais listadas: <strong><?= count($faltas) ?></strong></p>
    <p class="text-xs text-olive-500 mt-4">Feature futura: pedido de marmitas.</p>
  </article>
</div>
