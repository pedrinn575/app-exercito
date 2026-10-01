<?php
/** @var list<\App\Entities\Usuario> $militares */
use App\Utils\Auth;
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
  <p class="text-sm text-olive-600"><?= count($militares) ?> militar(es) ativo(s)</p>
  <?php if (Auth::isAdmin()): ?>
    <a href="/militares/criar"
       class="inline-flex items-center font-display tracking-wider uppercase text-sm px-4 py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition shadow-panel">
      Novo militar
    </a>
  <?php endif; ?>
</div>

<div class="rounded-xl bg-white/80 border border-olive-200/80 shadow-panel overflow-x-auto">
  <table class="w-full text-sm min-w-[640px]">
    <thead>
      <tr class="text-left text-xs uppercase tracking-wider text-olive-600 bg-olive-50/80">
        <th class="px-6 py-3">Nº atirador</th>
        <th class="px-3 py-3">Nº monitor</th>
        <th class="px-3 py-3">Nome</th>
        <th class="px-3 py-3">Perfil</th>
        <th class="px-3 py-3">E-mail</th>
        <?php if (Auth::isAdmin()): ?><th class="px-6 py-3 text-right">Ações</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($militares as $m): ?>
        <tr class="border-t border-olive-100 hover:bg-olive-50/50">
          <td class="px-6 py-3 font-medium text-olive-900"><?= htmlspecialchars($m->numero) ?></td>
          <td class="px-3 py-3 text-olive-700"><?= htmlspecialchars($m->numeroMonitor ?? '—') ?></td>
          <td class="px-3 py-3"><?= htmlspecialchars($m->nome) ?></td>
          <td class="px-3 py-3">
            <span class="text-xs uppercase tracking-wider px-2 py-0.5 rounded bg-olive-100 text-olive-700"><?= htmlspecialchars($m->perfil) ?></span>
          </td>
          <td class="px-3 py-3 text-olive-600"><?= htmlspecialchars($m->email ?? '—') ?></td>
          <?php if (Auth::isAdmin()): ?>
            <td class="px-6 py-3 text-right space-x-2">
              <a href="/militares/<?= (int)$m->id ?>/editar" class="text-xs text-olive-700 hover:underline">Editar</a>
              <?php if ($m->perfil !== 'admin'): ?>
              <form method="post" action="/militares/<?= (int)$m->id ?>/desativar" class="inline" onsubmit="return confirm('Desativar este militar?')">
                <button class="text-xs text-crimson-600 hover:underline">Desativar</button>
              </form>
              <?php endif; ?>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
