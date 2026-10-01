<?php
/** @var array $metricas */
/** @var \App\Entities\Escala|null $escalaHoje */
/** @var string $tipoHoje */
use App\Utils\Auth;
use App\Utils\Calendar;

$cards = [
  ['Escalas pretas (mês)', $metricas['escalas_preta_mes'], 'olive'],
  ['Escalas vermelhas (mês)', $metricas['escalas_vermelha_mes'], 'crimson'],
  ['Trocas pendentes', $metricas['trocas_pendentes'], 'khaki'],
  ['Marmitas pendentes', $metricas['marmitas_pendentes'], 'khaki'],
];
if (Auth::isAdmin()) {
  array_unshift($cards, ['Militares ativos', $metricas['militares'], 'olive']);
}
?>
<section class="grid gap-6 md:grid-cols-2 <?= Auth::isAdmin() ? 'xl:grid-cols-5' : 'xl:grid-cols-4' ?>">
  <?php
  foreach ($cards as $i => [$label, $value, $tone]):
    $delay = $i === 0 ? 'animate-rise' : ($i === 1 ? 'animate-rise-delay' : 'animate-rise-delay-2');
  ?>
    <article class="<?= $delay ?> rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
      <p class="text-xs uppercase tracking-wider text-olive-600"><?= $label ?></p>
      <p class="font-display text-4xl mt-2 text-olive-900 tracking-wide"><?= (int) $value ?></p>
      <?php if ($tone === 'crimson' && $metricas['faltas_injustificadas'] > 0): ?>
        <p class="text-xs text-crimson-600 mt-2"><?= (int) $metricas['faltas_injustificadas'] ?> falta(s) injustificada(s) no mês</p>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</section>

<section class="mt-8 grid lg:grid-cols-3 gap-6">
  <article class="lg:col-span-2 min-w-0 rounded-xl bg-white/80 border border-olive-200/80 p-4 sm:p-6 shadow-panel">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
      <div>
        <h3 class="font-display text-lg tracking-wide uppercase text-olive-900">Escala de hoje</h3>
        <p class="text-sm text-olive-600"><?= Calendar::weekdayBr($metricas['hoje']) ?>, <?= Calendar::formatBr($metricas['hoje']) ?> · sugerida: <?= htmlspecialchars($tipoHoje) ?></p>
      </div>
      <div class="flex gap-2">
        <a href="/escalas/preta?data=<?= $metricas['hoje'] ?>" class="text-xs uppercase tracking-wider px-3 py-1.5 rounded bg-olive-800 text-olive-50 hover:bg-olive-900 transition">Preta</a>
        <a href="/escalas/vermelha?data=<?= $metricas['hoje'] ?>" class="text-xs uppercase tracking-wider px-3 py-1.5 rounded bg-crimson-700 text-white hover:bg-crimson-600 transition">Vermelha</a>
      </div>
    </div>

    <?php if ($escalaHoje && count($escalaHoje->postos)): ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[520px]">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-olive-600 border-b border-olive-200">
              <th class="py-2 pr-3">Função</th>
              <th class="py-2 pr-3">Nº</th>
              <th class="py-2 pr-3">Nome</th>
              <th class="py-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($escalaHoje->postos as $p):
              $falta = $p['status'] === 'falta_injustificada';
            ?>
              <tr class="border-b border-olive-100 <?= $falta ? 'bg-red-50 text-crimson-700 font-semibold' : '' ?>">
                <td class="py-2.5 pr-3 capitalize"><?= htmlspecialchars($p['funcao']) ?></td>
                <td class="py-2.5 pr-3 font-medium"><?= htmlspecialchars($p['funcao'] === 'monitor' ? ($p['numero_monitor'] ?: $p['numero']) : $p['numero']) ?></td>
                <td class="py-2.5 pr-3"><?= htmlspecialchars($p['nome']) ?></td>
                <td class="py-2.5 capitalize text-xs"><?= str_replace('_', ' ', htmlspecialchars($p['status'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="mt-3 text-xs text-olive-500">Tipo: <strong class="uppercase"><?= htmlspecialchars($escalaHoje->tipo) ?></strong></p>
    <?php else: ?>
      <div class="rounded-lg border border-dashed border-olive-300 bg-olive-50/50 p-8 text-center">
        <p class="text-olive-700">Nenhuma escala gerada para hoje.</p>
        <p class="text-sm text-olive-500 mt-1">Gere o mês inteiro no calendário, ou só o dia nesta tela.</p>
      </div>
    <?php endif; ?>
  </article>

  <article class="min-w-0 rounded-xl bg-olive-900 text-olive-100 p-4 sm:p-6 shadow-panel relative overflow-hidden">
    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-khaki-500/20 blur-2xl"></div>
    <h3 class="font-display text-lg tracking-wide uppercase relative">Regras rápidas</h3>
    <ul class="mt-4 space-y-3 text-sm text-olive-200/90 relative">
      <li class="flex gap-2"><span class="text-khaki-400">▸</span> 11 por dia: 2 monitores e 9 atiradores</li>
      <li class="flex gap-2"><span class="text-khaki-400">▸</span> Intervalo mínimo de 48h</li>
      <li class="flex gap-2"><span class="text-khaki-400">▸</span> Vermelha = fim de semana / feriado (24h)</li>
      <li class="flex gap-2"><span class="text-khaki-400">▸</span> Troca exige aceite + admin</li>
      <li class="flex gap-2"><span class="text-khaki-400">▸</span> Marmita: pedido do militar, confirmação do admin</li>
    </ul>
  </article>
</section>
