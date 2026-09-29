<?php
/** @var string $tipo */
/** @var list<\App\Entities\Escala> $escalas */
/** @var \App\Entities\Escala|null $atual */
/** @var string $data */
/** @var list<\App\Entities\Usuario> $militares */
/** @var array{monitores:int,atiradores:int} $efetivo */
use App\Utils\Auth;
use App\Utils\Calendar;

$isVermelha = $tipo === 'vermelha';
$redirect = '/escalas/' . $tipo . '?data=' . urlencode($data);
$temEscala = $atual && count($atual->postos) > 0;
$admin = Auth::isAdmin();

$contagem = ['monitor' => 0, 'atirador' => 0, 'reserva' => 0];
if ($atual) {
    foreach ($atual->postos as $p) {
        $contagem[$p['funcao']] = ($contagem[$p['funcao']] ?? 0) + 1;
    }
}
$totalPostos = $atual ? count($atual->postos) : 0;

// Quem ainda não está na escala pode ser incluído
$rotuloMilitar = function (\App\Entities\Usuario $m): string {
    $numero = $m->numero;
    if ($m->perfil === 'monitor' && $m->numeroMonitor) {
        $numero .= ' (monitor ' . $m->numeroMonitor . ')';
    }
    return $numero . ' · ' . $m->nome;
};

$jaEscalados = [];
if ($atual) {
    foreach ($atual->postos as $p) {
        $jaEscalados[(int) $p['usuario_id']] = true;
    }
}
?>
<div class="grid lg:grid-cols-[320px_1fr] gap-6">
  <!-- Painel lateral -->
  <aside class="space-y-4">
    <div class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
      <h3 class="font-display uppercase tracking-wide text-olive-900">Consultar data</h3>
      <form method="get" action="/escalas/<?= htmlspecialchars($tipo) ?>" class="mt-3 space-y-3">
        <input type="date" name="data" value="<?= htmlspecialchars($data) ?>"
               class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm focus:ring-2 focus:ring-olive-400/30 outline-none">
        <button class="w-full rounded-md bg-olive-800 text-olive-50 py-2 text-sm font-display tracking-wider uppercase hover:bg-olive-900 transition">
          Abrir
        </button>
      </form>
      <p class="mt-3 text-xs text-olive-600">
        <?= Calendar::weekdayBr($data) ?> ·
        <?= Calendar::isEscalaVermelha($data) ? 'Sugestão: vermelha (24h)' : 'Sugestão: preta' ?>
      </p>
    </div>

    <?php if ($admin): ?>
    <div class="rounded-xl <?= $isVermelha ? 'bg-crimson-700' : 'bg-olive-900' ?> text-white p-5 shadow-panel">
      <h3 class="font-display uppercase tracking-wide"><?= $temEscala ? 'Refazer escala' : 'Gerar escala' ?></h3>
      <p class="text-sm opacity-80 mt-1">Escolha o efetivo do dia. A rotação respeita as 48h.</p>
      <form method="post" action="/escalas/gerar" class="mt-4 space-y-3">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
        <input type="date" name="data" value="<?= htmlspecialchars($data) ?>"
               class="w-full rounded-md border-0 px-3 py-2 text-sm text-olive-900">
        <div class="grid grid-cols-2 gap-2">
          <label class="block">
            <span class="block text-[10px] uppercase tracking-widest opacity-80 mb-1">Monitores</span>
            <input type="number" name="monitores" min="0" max="150"
                   value="<?= $temEscala ? (int) $contagem['monitor'] : (int) $efetivo['monitores'] ?>"
                   class="w-full rounded-md border-0 px-3 py-2 text-sm text-olive-900">
          </label>
          <label class="block">
            <span class="block text-[10px] uppercase tracking-widest opacity-80 mb-1">Atiradores</span>
            <input type="number" name="atiradores" min="0" max="150"
                   value="<?= $temEscala ? (int) $contagem['atirador'] : (int) $efetivo['atiradores'] ?>"
                   class="w-full rounded-md border-0 px-3 py-2 text-sm text-olive-900">
          </label>
        </div>
        <?php if ($temEscala): ?>
          <label class="flex items-center gap-2 text-xs opacity-90">
            <input type="checkbox" name="refazer" value="1" checked class="rounded">
            Apagar a escala atual e montar de novo
          </label>
        <?php endif; ?>
        <button class="w-full rounded-md bg-white/15 hover:bg-white/25 border border-white/20 py-2.5 text-sm font-display tracking-wider uppercase transition">
          <?= $temEscala ? 'Refazer' : 'Gerar' ?> <?= htmlspecialchars($tipo) ?>
        </button>
      </form>

      <?php if ($temEscala): ?>
        <form method="post" action="/escalas/excluir" class="mt-3"
              onsubmit="return confirm('Apagar a escala deste dia?')">
          <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
          <input type="hidden" name="data" value="<?= htmlspecialchars($data) ?>">
          <button class="w-full text-xs uppercase tracking-wider py-2 rounded-md border border-white/25 hover:bg-white/10 transition">
            Apagar escala do dia
          </button>
        </form>
      <?php endif; ?>
    </div>

    <?php if ($temEscala): ?>
      <div class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
        <h3 class="font-display uppercase tracking-wide text-olive-900 text-sm">Incluir militar</h3>
        <form method="post" action="/escalas/<?= (int) $atual->id ?>/postos" class="mt-3 space-y-2">
          <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
          <input type="hidden" name="data" value="<?= htmlspecialchars($data) ?>">
          <select name="usuario_id" required class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
            <?php foreach ($militares as $m):
              if ($m->perfil === 'admin' || isset($jaEscalados[(int) $m->id])) continue;
            ?>
              <option value="<?= (int) $m->id ?>"><?= htmlspecialchars($rotuloMilitar($m)) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="funcao" class="w-full rounded-md border border-olive-300 px-3 py-2 text-sm">
            <option value="atirador">Atirador</option>
            <option value="monitor">Monitor</option>
            <option value="reserva">Reserva</option>
          </select>
          <button class="w-full rounded-md bg-olive-800 text-olive-50 py-2 text-xs font-display tracking-wider uppercase hover:bg-olive-900 transition">
            Incluir
          </button>
        </form>
      </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="rounded-xl bg-white/80 border border-olive-200/80 p-5 shadow-panel">
      <h3 class="font-display uppercase tracking-wide text-olive-900 text-sm">Últimas geradas</h3>
      <ul class="mt-3 space-y-2 max-h-64 overflow-y-auto text-sm">
        <?php if (!$escalas): ?>
          <li class="text-olive-500">Nenhuma ainda.</li>
        <?php endif; ?>
        <?php foreach ($escalas as $e): ?>
          <li>
            <a class="flex justify-between hover:text-olive-700 <?= $e->dataServico === $data ? 'font-semibold text-olive-900' : 'text-olive-600' ?>"
               href="/escalas/<?= $tipo ?>?data=<?= urlencode($e->dataServico) ?>">
              <span><?= Calendar::formatBr($e->dataServico) ?></span>
              <span class="text-xs"><?= count($e->postos) ?> postos</span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </aside>

  <!-- Escala do dia -->
  <section class="rounded-xl bg-white/80 border border-olive-200/80 shadow-panel overflow-hidden">
    <div class="px-6 py-5 border-b border-olive-200 flex flex-wrap items-center justify-between gap-3
                <?= $isVermelha ? 'bg-gradient-to-r from-crimson-700 to-crimson-600 text-white' : 'bg-gradient-to-r from-olive-900 to-olive-700 text-olive-50' ?>">
      <div>
        <p class="text-xs uppercase tracking-[0.2em] opacity-80">Escala <?= htmlspecialchars($tipo) ?></p>
        <h2 class="font-display text-2xl tracking-wide"><?= Calendar::formatBr($data) ?></h2>
      </div>
      <span class="text-xs uppercase tracking-wider px-3 py-1 rounded-full bg-white/15 border border-white/20">
        <?php if ($temEscala): ?>
          <?= $isVermelha ? '24h · ' : '' ?><?= $totalPostos ?> no dia ·
          <?= (int) $contagem['monitor'] ?> mon · <?= (int) $contagem['atirador'] ?> ati
          <?= $contagem['reserva'] ? '· ' . (int) $contagem['reserva'] . ' res' : '' ?>
        <?php else: ?>
          <?= $isVermelha ? 'Serviço 24h' : 'Sem escala' ?>
        <?php endif; ?>
      </span>
    </div>

    <?php if (!$temEscala): ?>
      <div class="p-12 text-center text-olive-600">
        <p class="font-display uppercase tracking-wide text-olive-800">Sem escala nesta data</p>
        <p class="text-sm mt-2">O administrador escolhe o efetivo e gera a rotação ao lado.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[620px]">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-olive-600 bg-olive-50/80">
              <th class="px-6 py-3">Função</th>
              <th class="px-3 py-3">Nº</th>
              <th class="px-3 py-3">Nome</th>
              <th class="px-3 py-3">Status</th>
              <?php if ($admin): ?>
                <th class="px-6 py-3 text-right">Ações</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($atual->postos as $p):
              $injust = $p['status'] === 'falta_injustificada';
              $just = $p['status'] === 'falta_justificada';
              $escalado = $p['status'] === 'escalado';
            ?>
              <tr class="border-t border-olive-100 align-top <?= $injust ? 'bg-red-50' : ($just ? 'bg-amber-50/60' : 'hover:bg-olive-50/40') ?>">
                <td class="px-6 py-3 <?= $injust ? 'text-crimson-700' : 'text-olive-900' ?>">
                  <?php if ($admin): ?>
                    <form method="post" action="/postos/<?= (int) $p['id'] ?>/funcao" class="flex items-center gap-1">
                      <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                      <input type="hidden" name="data" value="<?= htmlspecialchars($data) ?>">
                      <select name="funcao" onchange="this.form.submit()"
                              class="text-xs capitalize border border-olive-200 rounded px-2 py-1 bg-white">
                        <?php foreach (['monitor' => 'Monitor', 'atirador' => 'Atirador', 'reserva' => 'Reserva'] as $valor => $texto): ?>
                          <option value="<?= $valor ?>" <?= $p['funcao'] === $valor ? 'selected' : '' ?>><?= $texto ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  <?php else: ?>
                    <span class="capitalize font-medium"><?= htmlspecialchars($p['funcao']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-3 <?= $injust ? 'text-crimson-700 font-bold' : '' ?>"><?= htmlspecialchars($p['funcao'] === 'monitor' ? ($p['numero_monitor'] ?: $p['numero']) : $p['numero']) ?></td>
                <td class="px-3 py-3 <?= $injust ? 'text-crimson-700 font-bold' : '' ?>"><?= htmlspecialchars($p['nome']) ?></td>
                <td class="px-3 py-3">
                  <span class="text-xs capitalize px-2 py-0.5 rounded
                    <?= $injust ? 'bg-red-100 text-crimson-700' : ($just ? 'bg-amber-100 text-amber-800' : 'bg-olive-100 text-olive-700') ?>">
                    <?= str_replace('_', ' ', htmlspecialchars($p['status'])) ?>
                  </span>
                </td>
                <?php if ($admin): ?>
                  <td class="px-6 py-3">
                    <div class="flex flex-wrap justify-end gap-2">
                      <?php if ($escalado && $p['funcao'] !== 'reserva'): ?>
                        <form method="post" action="/faltas/justificada" class="inline">
                          <input type="hidden" name="posto_id" value="<?= (int) $p['id'] ?>">
                          <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                          <button class="text-xs px-2.5 py-1 rounded border border-amber-400 text-amber-800 hover:bg-amber-50 transition" title="Falta justificada — sem substituto">
                            Justificada
                          </button>
                        </form>
                        <details class="relative">
                          <summary class="list-none cursor-pointer text-xs px-2.5 py-1 rounded border border-crimson-600 text-crimson-700 hover:bg-red-50 transition">
                            Injustificada
                          </summary>
                          <div class="absolute right-0 mt-1 z-10 w-64 rounded-lg border border-olive-200 bg-white p-3 shadow-lg">
                            <form method="post" action="/faltas/injustificada" class="space-y-2">
                              <input type="hidden" name="posto_id" value="<?= (int) $p['id'] ?>">
                              <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                              <label class="block text-xs text-olive-600">Puxar substituto / reserva</label>
                              <select name="substituto_id" class="w-full text-sm border border-olive-300 rounded px-2 py-1.5">
                                <option value="">— Sem substituto —</option>
                                <?php foreach ($militares as $m):
                                  if ($m->perfil === 'admin' || (int) $m->id === (int) $p['usuario_id']) continue;
                                ?>
                                  <option value="<?= (int) $m->id ?>"><?= htmlspecialchars($rotuloMilitar($m)) ?></option>
                                <?php endforeach; ?>
                              </select>
                              <button class="w-full text-xs py-1.5 rounded bg-crimson-700 text-white hover:bg-crimson-600">Confirmar falta</button>
                            </form>
                          </div>
                        </details>
                      <?php endif; ?>
                      <form method="post" action="/postos/<?= (int) $p['id'] ?>/remover" class="inline"
                            onsubmit="return confirm('Tirar <?= htmlspecialchars($p['nome'], ENT_QUOTES) ?> da escala?')">
                        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                        <input type="hidden" name="data" value="<?= htmlspecialchars($data) ?>">
                        <button class="text-xs px-2.5 py-1 rounded border border-olive-300 text-olive-700 hover:bg-olive-50">Remover</button>
                      </form>
                    </div>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($atual->observacao): ?>
        <p class="px-6 py-3 text-xs text-olive-500 border-t border-olive-100"><?= htmlspecialchars($atual->observacao) ?></p>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
