<?php
/** @var list<array<string,mixed>> $pedidos */
/** @var string $dataPref */
/** @var bool $admin */
use App\Utils\Auth;
use App\Utils\Calendar;

$refeicaoLabel = ['almoco' => 'Almoço', 'jantar' => 'Jantar', 'ambos' => 'Almoço e jantar'];
$statusLabel = [
    'pendente'  => 'Pendente',
    'aprovado'  => 'Aprovado',
    'negado'    => 'Negado',
    'alteracao' => 'Alteração pedida',
    'cancelado' => 'Cancelado',
];
$statusBadge = [
    'pendente'  => 'bg-amber-100 text-amber-800',
    'aprovado'  => 'bg-olive-100 text-olive-800',
    'negado'    => 'bg-red-100 text-crimson-700',
    'alteracao' => 'bg-khaki-200 text-olive-900',
    'cancelado' => 'bg-olive-50 text-olive-400',
];
$pendentes = array_values(array_filter($pedidos, fn($p) => in_array($p['status'], ['pendente', 'alteracao'], true)));
?>
<div class="<?= $admin ? '' : 'grid lg:grid-cols-[340px_1fr] gap-6' ?>">
  <?php if (!$admin): ?>
    <aside class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel h-fit">
      <h3 class="font-display uppercase tracking-wide text-olive-900">Pedir marmita</h3>
      <p class="text-xs text-olive-600 mt-1">O pedido fica pendente até a decisão do Subtenente.</p>
      <form method="post" action="/marmitas" class="mt-4 space-y-3">
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Data</label>
          <input type="date" name="data_pedido" required value="<?= htmlspecialchars($dataPref) ?>"
                 class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Refeição</label>
          <select name="refeicao" class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
            <option value="almoco">Almoço</option>
            <option value="jantar">Jantar</option>
            <option value="ambos">Almoço e jantar</option>
          </select>
        </div>
        <p class="text-xs text-olive-600">Almoço ou jantar conta 1 marmita. Os dois contam 2.</p>
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Observação</label>
          <textarea name="observacao" rows="2" class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm" placeholder="Ex.: serviço de 24h"></textarea>
        </div>
        <button class="w-full font-display tracking-wider uppercase text-sm py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition">
          Enviar pedido
        </button>
      </form>
    </aside>
  <?php endif; ?>

  <div class="space-y-6">
    <?php if ($admin): ?>
      <section class="rounded-xl bg-olive-900 text-olive-100 p-5 shadow-panel flex flex-wrap items-center justify-between gap-4">
        <div>
          <h3 class="font-display uppercase tracking-wide">Aguardando decisão</h3>
          <p class="text-sm text-olive-200/80 mt-1">Aprove, negue ou peça alteração ao militar.</p>
        </div>
        <p class="font-display text-4xl"><?= count($pendentes) ?></p>
      </section>
      <form method="get" action="/marmitas/imprimir" target="_blank" class="rounded-xl bg-white/80 border border-olive-200/80 p-4 shadow-panel flex flex-wrap items-end gap-3">
        <label class="block">
          <span class="block text-[10px] uppercase tracking-widest text-olive-600 mb-1">A partir de</span>
          <input type="date" name="inicio" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required class="rounded-md border border-olive-300 px-3 py-2 text-sm">
        </label>
        <label class="block">
          <span class="block text-[10px] uppercase tracking-widest text-olive-600 mb-1">Dias</span>
          <input type="number" name="dias" min="1" max="7" value="7" required class="w-20 rounded-md border border-olive-300 px-3 py-2 text-sm">
        </label>
        <button class="font-display tracking-wider uppercase text-sm px-4 py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition">
          Imprimir PDF
        </button>
      </form>
    <?php endif; ?>

    <section class="rounded-xl bg-white/80 border border-olive-200/80 shadow-panel overflow-hidden">
      <div class="px-6 py-4 border-b border-olive-200 bg-khaki-100/70">
        <h3 class="font-display uppercase tracking-wide text-olive-900">Pedidos</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[720px]">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-olive-600">
              <th class="px-6 py-3">Data</th>
              <th class="px-3 py-3">Militar</th>
              <th class="px-3 py-3">Refeição</th>
              <th class="px-3 py-3">Qtd</th>
              <th class="px-3 py-3">Status</th>
              <th class="px-6 py-3 text-right">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$pedidos): ?>
              <tr><td colspan="6" class="px-6 py-10 text-center text-olive-500">Nenhum pedido ainda.</td></tr>
            <?php endif; ?>
            <?php foreach ($pedidos as $p):
              $user = Auth::user();
              $status = $p['status'];
              $decidivel = in_array($status, ['pendente', 'alteracao'], true);
              $meu = (int) $p['usuario_id'] === (int) $user['id'];
            ?>
              <tr class="border-t border-olive-100 align-top <?= in_array($status, ['cancelado', 'negado'], true) ? 'opacity-70' : '' ?>">
                <td class="px-6 py-3"><?= Calendar::formatBr($p['data_pedido']) ?></td>
                <td class="px-3 py-3"><?= htmlspecialchars($p['numero'] . ' · ' . $p['nome']) ?></td>
                <td class="px-3 py-3"><?= htmlspecialchars($refeicaoLabel[$p['refeicao']] ?? $p['refeicao']) ?></td>
                <td class="px-3 py-3"><?= (int) $p['quantidade'] ?></td>
                <td class="px-3 py-3">
                  <span class="text-xs px-2 py-0.5 rounded <?= $statusBadge[$status] ?? 'bg-olive-100 text-olive-700' ?>">
                    <?= $statusLabel[$status] ?? htmlspecialchars($status) ?>
                  </span>
                  <?php if (!empty($p['observacao'])): ?>
                    <p class="text-[11px] text-olive-500 mt-1"><?= htmlspecialchars($p['observacao']) ?></p>
                  <?php endif; ?>
                  <?php if (!empty($p['retorno'])): ?>
                    <p class="text-[11px] text-crimson-700 mt-1">Subtenente: <?= htmlspecialchars($p['retorno']) ?></p>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-3">
                  <div class="flex flex-wrap justify-end gap-2">
                    <?php if ($admin && $decidivel): ?>
                      <form method="post" action="/marmitas/<?= (int) $p['id'] ?>/aprovar" class="inline">
                        <button class="text-xs px-2.5 py-1 rounded bg-olive-800 text-white hover:bg-olive-900">Aprovar</button>
                      </form>
                      <details class="relative">
                        <summary class="list-none cursor-pointer text-xs px-2.5 py-1 rounded border border-crimson-600 text-crimson-700 hover:bg-red-50">Negar</summary>
                        <div class="absolute right-0 mt-1 z-10 w-64 rounded-lg border border-olive-200 bg-white p-3 shadow-lg">
                          <form method="post" action="/marmitas/<?= (int) $p['id'] ?>/negar" class="space-y-2">
                            <label class="block text-xs text-olive-600">Motivo (opcional)</label>
                            <textarea name="retorno" rows="2" class="w-full text-sm border border-olive-300 rounded px-2 py-1.5"></textarea>
                            <button class="w-full text-xs py-1.5 rounded bg-crimson-700 text-white hover:bg-crimson-600">Confirmar recusa</button>
                          </form>
                        </div>
                      </details>
                      <details class="relative">
                        <summary class="list-none cursor-pointer text-xs px-2.5 py-1 rounded border border-khaki-500 text-olive-800 hover:bg-khaki-100">Pedir alteração</summary>
                        <div class="absolute right-0 mt-1 z-10 w-64 rounded-lg border border-olive-200 bg-white p-3 shadow-lg">
                          <form method="post" action="/marmitas/<?= (int) $p['id'] ?>/alteracao" class="space-y-2">
                            <label class="block text-xs text-olive-600">O que precisa mudar</label>
                            <textarea name="retorno" rows="2" required class="w-full text-sm border border-olive-300 rounded px-2 py-1.5"></textarea>
                            <button class="w-full text-xs py-1.5 rounded bg-khaki-500 text-olive-950 hover:bg-khaki-400 font-medium">Enviar</button>
                          </form>
                        </div>
                      </details>
                    <?php endif; ?>

                    <?php if (!$admin && $meu && $decidivel): ?>
                      <details class="relative">
                        <summary class="list-none cursor-pointer text-xs px-2.5 py-1 rounded border border-olive-400 text-olive-800 hover:bg-olive-50">Editar</summary>
                        <div class="absolute right-0 mt-1 z-10 w-64 rounded-lg border border-olive-200 bg-white p-3 shadow-lg text-left">
                          <form method="post" action="/marmitas/<?= (int) $p['id'] ?>/atualizar" class="space-y-2">
                            <input type="date" name="data_pedido" required value="<?= htmlspecialchars($p['data_pedido']) ?>"
                                   class="w-full text-sm border border-olive-300 rounded px-2 py-1.5">
                            <select name="refeicao" class="w-full text-sm border border-olive-300 rounded px-2 py-1.5">
                              <?php foreach ($refeicaoLabel as $valor => $texto): ?>
                                <option value="<?= $valor ?>" <?= $p['refeicao'] === $valor ? 'selected' : '' ?>><?= $texto ?></option>
                              <?php endforeach; ?>
                            </select>
                            <p class="text-[11px] text-olive-500">Almoço ou jantar: 1. Os dois: 2.</p>
                            <textarea name="observacao" rows="2" class="w-full text-sm border border-olive-300 rounded px-2 py-1.5"><?= htmlspecialchars($p['observacao'] ?? '') ?></textarea>
                            <button class="w-full text-xs py-1.5 rounded bg-olive-800 text-white hover:bg-olive-900">Reenviar</button>
                          </form>
                        </div>
                      </details>
                    <?php endif; ?>

                    <?php if ($status !== 'cancelado' && ($admin || ($meu && $decidivel))): ?>
                      <form method="post" action="/marmitas/<?= (int) $p['id'] ?>/cancelar" class="inline">
                        <button class="text-xs px-2.5 py-1 rounded border border-olive-300 text-olive-700">Cancelar</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>
