<?php
/** @var list<array> $trocas */
/** @var list<\App\Entities\Usuario> $militares */
/** @var list<array{id:int,label:string}> $meusPostos */
use App\Utils\Auth;
use App\Utils\Calendar;

$admin = Auth::isAdmin();
$eu = (int) (Auth::user()['id'] ?? 0);
$statusLabel = [
    'pendente'       => 'Aguardando o colega',
    'aceito_destino' => 'Aceito, aguardando admin',
    'aprovado'       => 'Aprovado',
    'recusado'       => 'Recusado',
    'cancelado'      => 'Cancelado',
];
$statusBadge = [
    'pendente'       => 'bg-amber-100 text-amber-800',
    'aceito_destino' => 'bg-khaki-200 text-olive-900',
    'aprovado'       => 'bg-olive-100 text-olive-800',
    'recusado'       => 'bg-red-100 text-crimson-700',
    'cancelado'      => 'bg-olive-50 text-olive-400',
];
?>
<div class="<?= $admin ? '' : 'grid lg:grid-cols-[360px_1fr] gap-6' ?>">
  <?php if (!$admin): ?>
  <aside class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel h-fit">
    <h3 class="font-display uppercase tracking-wide text-olive-900">Pedir troca</h3>
    <p class="text-xs text-olive-600 mt-1">Informe seu serviço e o número do atirador. Ele aceita; o Subtenente aprova.</p>

    <?php if (!$meusPostos): ?>
      <p class="mt-4 text-sm text-olive-500">Você não possui serviços futuros escalados.</p>
    <?php else: ?>
      <form method="post" action="/trocas" class="mt-4 space-y-3">
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Meu serviço</label>
          <select name="escala_posto_id" required class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
            <?php foreach ($meusPostos as $mp): ?>
              <option value="<?= (int)$mp['id'] ?>"><?= htmlspecialchars($mp['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Trocar com</label>
          <select name="destino_id" required class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
            <?php foreach ($militares as $m):
              if ($m->perfil === 'admin') continue;
            ?>
              <option value="<?= (int)$m->id ?>"><?= htmlspecialchars($m->numero . ' · ' . $m->nome) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Motivo</label>
          <textarea name="motivo" rows="2" class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm"></textarea>
        </div>
        <button class="w-full font-display tracking-wider uppercase text-sm py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition">
          Enviar pedido
        </button>
      </form>
    <?php endif; ?>
  </aside>
  <?php else: ?>
  <section class="mb-6 rounded-xl bg-olive-900 text-olive-100 p-5 shadow-panel">
    <h3 class="font-display uppercase tracking-wide">Decisões dos militares</h3>
    <p class="text-sm text-olive-200/85 mt-1">O pedido só aparece aqui depois que o outro militar aceita ou recusa. Aprove apenas os aceitos.</p>
  </section>
  <?php endif; ?>

  <section class="rounded-xl bg-white/80 border border-olive-200/80 shadow-panel overflow-hidden">
    <div class="px-6 py-4 border-b border-olive-200 bg-olive-50/60">
      <h3 class="font-display uppercase tracking-wide text-olive-900"><?= $admin ? 'Para o administrador' : 'Pedidos' ?></h3>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm min-w-[720px]">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-olive-600">
            <th class="px-6 py-3">Data</th>
            <th class="px-3 py-3">De</th>
            <th class="px-3 py-3">Para</th>
            <th class="px-3 py-3">Status</th>
            <th class="px-6 py-3 text-right">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$trocas): ?>
            <tr><td colspan="5" class="px-6 py-10 text-center text-olive-500">Nenhuma troca registrada.</td></tr>
          <?php endif; ?>
          <?php foreach ($trocas as $t): ?>
            <tr class="border-t border-olive-100">
              <td class="px-6 py-3">
                <span class="uppercase text-xs text-olive-500"><?= htmlspecialchars($t['tipo']) ?></span><br>
                <?= Calendar::formatBr($t['data_servico']) ?>
                <span class="text-xs text-olive-500">· <?= htmlspecialchars($t['funcao']) ?></span>
              </td>
              <td class="px-3 py-3"><?= htmlspecialchars($t['solicitante_numero'] . ' · ' . $t['solicitante_nome']) ?></td>
              <td class="px-3 py-3"><?= htmlspecialchars($t['destino_numero'] . ' · ' . $t['destino_nome']) ?></td>
              <td class="px-3 py-3">
                <span class="text-xs px-2 py-0.5 rounded <?= $statusBadge[$t['status']] ?? 'bg-olive-100 text-olive-800' ?>">
                  <?= htmlspecialchars($statusLabel[$t['status']] ?? $t['status']) ?>
                </span>
              </td>
              <td class="px-6 py-3 text-right space-x-1">
                <?php if (!$admin && $t['status'] === 'pendente' && (int) $t['destino_id'] === $eu): ?>
                  <form method="post" action="/trocas/<?= (int)$t['id'] ?>/aceitar" class="inline">
                    <button class="text-xs px-2 py-1 rounded bg-olive-700 text-white hover:bg-olive-800">Aceitar</button>
                  </form>
                  <form method="post" action="/trocas/<?= (int)$t['id'] ?>/recusar" class="inline">
                    <button class="text-xs px-2 py-1 rounded border border-olive-300 text-olive-700">Recusar</button>
                  </form>
                <?php endif; ?>
                <?php if ($admin && $t['status'] === 'aceito_destino'): ?>
                  <form method="post" action="/trocas/<?= (int)$t['id'] ?>/aprovar" class="inline">
                    <button class="text-xs px-2 py-1 rounded bg-khaki-500 text-olive-950 hover:bg-khaki-400 font-medium">Aprovar</button>
                  </form>
                  <form method="post" action="/trocas/<?= (int)$t['id'] ?>/recusar" class="inline">
                    <button class="text-xs px-2 py-1 rounded border border-crimson-600 text-crimson-700">Recusar</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
