<?php
/** @var list<\App\Entities\Usuario> $atestados */
/** @var list<\App\Entities\Usuario> $elegiveis */
use App\Utils\Calendar;
?>
<div class="grid lg:grid-cols-[340px_1fr] gap-6">
  <aside class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel h-fit">
    <h3 class="font-display uppercase tracking-wide text-olive-900">Marcar atestado</h3>
    <p class="text-xs text-olive-600 mt-1">Informe o início e quantos dias. Nesse período ele não entra no serviço.</p>
    <?php if (!$elegiveis): ?>
      <p class="mt-4 text-sm text-olive-500">Não há militar disponível para marcar.</p>
    <?php else: ?>
      <form method="post" action="/atestados" class="mt-4 space-y-3">
        <select name="usuario_id" required class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
          <?php foreach ($elegiveis as $m): ?>
            <option value="<?= (int) $m->id ?>"><?= htmlspecialchars($m->numero . ' · ' . $m->nome) ?></option>
          <?php endforeach; ?>
        </select>
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Início</label>
          <input type="date" name="inicio" required value="<?= htmlspecialchars(date('Y-m-d')) ?>"
                 class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Quantidade de dias</label>
          <input type="number" name="dias" required min="1" max="365" value="1"
                 class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
        </div>
        <button class="w-full font-display tracking-wider uppercase text-sm py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition">
          Colocar de atestado
        </button>
      </form>
    <?php endif; ?>
  </aside>

  <section class="rounded-xl bg-white/80 border border-olive-200/80 shadow-panel overflow-hidden">
    <div class="px-6 py-4 border-b border-olive-200 bg-amber-50">
      <h3 class="font-display uppercase tracking-wide text-olive-900">De atestado</h3>
      <p class="text-xs text-olive-600 mt-1"><?= count($atestados) ?> militar(es) fora da escala</p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm min-w-[720px]">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-olive-600">
            <th class="px-6 py-3">Nº</th>
            <th class="px-3 py-3">Nome</th>
            <th class="px-3 py-3">Perfil</th>
            <th class="px-3 py-3">Início</th>
            <th class="px-3 py-3">Dias</th>
            <th class="px-3 py-3">Termina em</th>
            <th class="px-6 py-3 text-right">Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$atestados): ?>
            <tr><td colspan="7" class="px-6 py-10 text-center text-olive-500">Ninguém está de atestado.</td></tr>
          <?php endif; ?>
          <?php foreach ($atestados as $m): ?>
            <?php $fim = $m->fimAtestado(); ?>
            <tr class="border-t border-olive-100">
              <td class="px-6 py-3 font-medium"><?= htmlspecialchars($m->numero) ?></td>
              <td class="px-3 py-3"><?= htmlspecialchars($m->nome) ?></td>
              <td class="px-3 py-3 capitalize"><?= htmlspecialchars($m->perfil) ?></td>
              <td class="px-3 py-3"><?= $m->atestadoInicio ? Calendar::formatBr($m->atestadoInicio) : '—' ?></td>
              <td class="px-3 py-3"><?= $m->atestadoDias ? (int) $m->atestadoDias : '—' ?></td>
              <td class="px-3 py-3 font-medium text-olive-900"><?= $fim ? Calendar::formatBr($fim) : '—' ?></td>
              <td class="px-6 py-3 text-right">
                <form method="post" action="/atestados/<?= (int) $m->id ?>/tirar" class="inline">
                  <button class="text-xs px-2.5 py-1 rounded border border-olive-400 text-olive-800 hover:bg-olive-50">Tirar atestado</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
