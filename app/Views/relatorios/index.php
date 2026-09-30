<?php
/** @var array $metricas */
/** @var list<\App\Entities\Escala> $pretas */
/** @var list<\App\Entities\Escala> $vermelhas */
/** @var list<array> $trocas */
/** @var list<array> $faltas */
/** @var array{presencas:int,justificadas:int,injustificadas:int,servicos:int,dias:list<array{data:string,presencas:int,faltas:int}>,atiradores:list<array{numero:string,nome:string,presencas:int,justificadas:int,injustificadas:int}>} $painel */
use App\Utils\Calendar;

$servicos = max(0, (int) $painel['servicos']);
$presencas = (int) $painel['presencas'];
$justificadas = (int) $painel['justificadas'];
$injustificadas = (int) $painel['injustificadas'];
$faltasTotal = $justificadas + $injustificadas;
$base = max(1, $servicos);
$raio = 42;
$circ = 2 * M_PI * $raio;
$fatias = [
    ['valor' => $presencas, 'cor' => '#3f5228', 'rotulo' => 'Presença'],
    ['valor' => $justificadas, 'cor' => '#b08f4f', 'rotulo' => 'Falta justificada'],
    ['valor' => $injustificadas, 'cor' => '#8c2f2f', 'rotulo' => 'Falta injustificada'],
];
$offset = 0;
$comFalta = array_values(array_filter(
    $painel['atiradores'],
    fn(array $a) => ($a['justificadas'] + $a['injustificadas']) > 0
));
$ranking = array_slice($comFalta, 0, 10);
$maiorFalta = 1;
foreach ($ranking as $a) {
    $maiorFalta = max($maiorFalta, $a['justificadas'] + $a['injustificadas']);
}
$maiorDia = 1;
foreach ($painel['dias'] as $dia) {
    $maiorDia = max($maiorDia, $dia['presencas'] + $dia['faltas']);
}
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
    <p class="text-sm text-olive-600">Aguardando o administrador: <strong><?= (int)$metricas['trocas_pendentes'] ?></strong></p>
    <p class="text-sm text-olive-600 mt-1">Faltas registradas: <strong><?= count($faltas) ?></strong></p>
  </article>
</div>

<section class="mt-6 grid lg:grid-cols-2 gap-6">
  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-olive-900">Presença dos atiradores</h3>
    <p class="text-xs text-olive-600 mt-1">Dia que já passou e não teve falta registrada conta como presença.</p>
    <div class="mt-5 flex flex-wrap items-center gap-6">
      <svg viewBox="0 0 120 120" class="w-36 h-36 shrink-0" role="img" aria-label="Distribuição de presença e falta">
        <circle cx="60" cy="60" r="<?= $raio ?>" fill="none" stroke="#e7eedb" stroke-width="14"></circle>
        <?php foreach ($fatias as $fatia):
          $arco = $fatia['valor'] / $base * $circ;
          if ($arco <= 0) { continue; }
        ?>
          <circle cx="60" cy="60" r="<?= $raio ?>" fill="none"
                  stroke="<?= $fatia['cor'] ?>" stroke-width="14"
                  stroke-dasharray="<?= round($arco, 2) ?> <?= round($circ - $arco, 2) ?>"
                  stroke-dashoffset="<?= round(-$offset, 2) ?>"
                  stroke-linecap="butt"
                  transform="rotate(-90 60 60)"></circle>
          <?php $offset += $arco; ?>
        <?php endforeach; ?>
        <text x="60" y="58" text-anchor="middle" class="fill-olive-900" font-size="18" font-family="Oswald, sans-serif"><?= $servicos ?></text>
        <text x="60" y="72" text-anchor="middle" fill="#4a5c32" font-size="8">serviços</text>
      </svg>
      <ul class="space-y-2 text-sm">
        <?php foreach ($fatias as $fatia): ?>
          <li class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-sm" style="background: <?= $fatia['cor'] ?>"></span>
            <span class="text-olive-800"><?= $fatia['rotulo'] ?></span>
            <strong class="text-olive-950"><?= (int) $fatia['valor'] ?></strong>
          </li>
        <?php endforeach; ?>
        <li class="text-xs text-olive-500 pt-1"><?= $servicos ? round($presencas / $base * 100) : 0 ?>% de presença</li>
      </ul>
    </div>
  </article>

  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-olive-900">Faltas por atirador</h3>
    <p class="text-xs text-olive-600 mt-1">Quem acumulou falta justificada ou injustificada.</p>
    <?php if (!$ranking): ?>
      <p class="mt-8 text-sm text-olive-500">Nenhuma falta de atirador nos dias que já passaram.</p>
    <?php else: ?>
      <ul class="mt-4 space-y-3">
        <?php foreach ($ranking as $a):
          $totalPessoa = $a['justificadas'] + $a['injustificadas'];
          $largura = max(8, (int) round($totalPessoa / $maiorFalta * 100));
        ?>
          <li>
            <div class="flex justify-between text-xs text-olive-700 mb-1">
              <span><?= htmlspecialchars($a['numero'] . ' · ' . $a['nome']) ?></span>
              <span><?= $totalPessoa ?> falta<?= $totalPessoa > 1 ? 's' : '' ?></span>
            </div>
            <div class="h-2.5 rounded-full bg-olive-100 overflow-hidden">
              <div class="h-full flex" style="width: <?= $largura ?>%">
                <span class="h-full bg-khaki-500" style="width: <?= (int) round($a['justificadas'] / $totalPessoa * 100) ?>%"></span>
                <span class="h-full bg-crimson-700" style="width: <?= (int) round($a['injustificadas'] / $totalPessoa * 100) ?>%"></span>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="mt-3 text-[11px] text-olive-500">
        <span class="inline-block w-2 h-2 rounded-sm bg-khaki-500 align-middle"></span> justificada
        <span class="inline-block w-2 h-2 rounded-sm bg-crimson-700 align-middle ml-3"></span> injustificada
      </p>
    <?php endif; ?>
  </article>

  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel lg:col-span-2">
    <h3 class="font-display uppercase tracking-wide text-olive-900">Dia a dia</h3>
    <p class="text-xs text-olive-600 mt-1"><?= $faltasTotal ?> falta<?= $faltasTotal === 1 ? '' : 's' ?> e <?= $presencas ?> presenças em dias encerrados.</p>
    <?php if (!$painel['dias']): ?>
      <p class="mt-6 text-sm text-olive-500">Ainda não há dia de escala encerrado.</p>
    <?php else: ?>
      <div class="mt-4 overflow-x-auto">
        <div class="flex items-end gap-1.5 min-w-max h-40">
          <?php foreach ($painel['dias'] as $dia):
            $alturaPres = (int) round($dia['presencas'] / $maiorDia * 100);
            $alturaFalta = (int) round($dia['faltas'] / $maiorDia * 100);
          ?>
            <div class="flex flex-col items-center justify-end h-full w-8">
              <div class="flex flex-col justify-end w-full flex-1">
                <span class="block w-full bg-crimson-700 rounded-t-sm" style="height: <?= $alturaFalta ?>%" title="<?= (int) $dia['faltas'] ?> faltas"></span>
                <span class="block w-full bg-olive-700 <?= $alturaFalta ? '' : 'rounded-t-sm' ?>" style="height: <?= $alturaPres ?>%" title="<?= (int) $dia['presencas'] ?> presenças"></span>
              </div>
              <span class="mt-1 text-[9px] text-olive-500"><?= date('d/m', strtotime($dia['data'])) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <p class="mt-3 text-[11px] text-olive-500">
        <span class="inline-block w-2 h-2 rounded-sm bg-olive-700 align-middle"></span> presença
        <span class="inline-block w-2 h-2 rounded-sm bg-crimson-700 align-middle ml-3"></span> falta
      </p>
    <?php endif; ?>
  </article>
</section>
