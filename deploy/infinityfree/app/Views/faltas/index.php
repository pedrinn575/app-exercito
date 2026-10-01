<?php
/** @var list<array> $faltas */
use App\Utils\Calendar;
?>
<div class="rounded-xl bg-white/80 border border-olive-200/80 shadow-panel overflow-hidden">
  <div class="px-6 py-4 border-b border-olive-200 bg-olive-50/60 flex flex-wrap justify-between gap-2">
    <div>
      <h3 class="font-display uppercase tracking-wide text-olive-900">Histórico de faltas</h3>
      <p class="text-xs text-olive-600 mt-0.5">Injustificada = nome em vermelho na escala + opção de puxar reserva. Justificada = sem substituto.</p>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[700px]">
      <thead>
        <tr class="text-left text-xs uppercase tracking-wider text-olive-600">
          <th class="px-6 py-3">Data serviço</th>
          <th class="px-3 py-3">Militar</th>
          <th class="px-3 py-3">Tipo</th>
          <th class="px-3 py-3">Substituto</th>
          <th class="px-6 py-3">Obs.</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$faltas): ?>
          <tr><td colspan="5" class="px-6 py-10 text-center text-olive-500">Nenhuma falta registrada.</td></tr>
        <?php endif; ?>
        <?php foreach ($faltas as $f):
          $inj = $f['tipo'] === 'injustificada';
        ?>
          <tr class="border-t border-olive-100 <?= $inj ? 'bg-red-50/70' : '' ?>">
            <td class="px-6 py-3">
              <span class="uppercase text-xs text-olive-500"><?= htmlspecialchars($f['tipo_escala'] ?? '') ?></span>
              <?= Calendar::formatBr($f['data_servico']) ?>
            </td>
            <td class="px-3 py-3 <?= $inj ? 'text-crimson-700 font-bold' : '' ?>">
              <?= htmlspecialchars($f['numero'] . ' · ' . $f['nome']) ?>
            </td>
            <td class="px-3 py-3">
              <span class="text-xs capitalize px-2 py-0.5 rounded <?= $inj ? 'bg-red-100 text-crimson-700' : 'bg-amber-100 text-amber-800' ?>">
                <?= htmlspecialchars($f['tipo']) ?>
              </span>
            </td>
            <td class="px-3 py-3 text-olive-700"><?= htmlspecialchars($f['substituto_nome'] ?? '—') ?></td>
            <td class="px-6 py-3 text-olive-600 text-xs"><?= htmlspecialchars($f['observacao'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
