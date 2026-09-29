<?php
/** @var list<array<string,mixed>> $proximos */
/** @var list<array<string,mixed>> $passados */
use App\Utils\Calendar;

$render = function (array $lista, bool $vazioFuturo) {
    if (!$lista) {
        echo '<p class="text-sm text-olive-500">' . ($vazioFuturo ? 'Nenhum serviço à frente.' : 'Sem histórico.') . '</p>';
        return;
    }
    echo '<ul class="divide-y divide-olive-100">';
    foreach ($lista as $p) {
        $vermelha = $p['tipo'] === 'vermelha';
        $injust = $p['status'] === 'falta_injustificada';
        echo '<li class="py-3 flex flex-wrap items-center justify-between gap-3">';
        echo '<div>';
        echo '<p class="font-medium ' . ($injust ? 'text-crimson-700' : 'text-olive-900') . '">' . Calendar::weekdayBr($p['data_servico']) . ', ' . Calendar::formatBr($p['data_servico']) . '</p>';
        echo '<p class="text-xs text-olive-600 capitalize">' . htmlspecialchars($p['funcao']) . ' · ' . str_replace('_', ' ', htmlspecialchars($p['status'])) . '</p>';
        echo '</div>';
        echo '<div class="flex items-center gap-2">';
        echo '<span class="text-[10px] uppercase tracking-wider px-2 py-1 rounded ' . ($vermelha ? 'bg-red-100 text-crimson-700' : 'bg-olive-100 text-olive-800') . '">' . htmlspecialchars($p['tipo']) . '</span>';
        if ($p['data_servico'] >= date('Y-m-d') && $p['status'] === 'escalado' && $p['funcao'] !== 'reserva') {
            echo '<a href="/marmitas?data=' . urlencode($p['data_servico']) . '" class="text-xs text-olive-700 hover:underline">Pedir marmita</a>';
        }
        echo '</div></li>';
    }
    echo '</ul>';
};
?>
<div class="grid lg:grid-cols-2 gap-6">
  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-6 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-olive-900 mb-4">Próximos</h3>
    <?php $render($proximos, true); ?>
  </article>
  <article class="rounded-xl bg-white/80 border border-olive-200/80 p-6 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-olive-900 mb-4">Histórico</h3>
    <?php $render($passados, false); ?>
  </article>
</div>
