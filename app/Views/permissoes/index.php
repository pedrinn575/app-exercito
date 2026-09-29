<?php
/** @var array<string, array<string, mixed>> $telas */
/** @var list<string> $perfis */
/** @var array<string, array<string, bool>> $matriz */

$rotulo = [
    'atirador' => 'Atirador',
    'monitor'  => 'Monitor',
];
?>
<form method="post" action="/permissoes" class="space-y-6">
  <section class="rounded-xl bg-olive-900 text-olive-100 p-5 sm:p-6 shadow-panel">
    <h3 class="font-display uppercase tracking-wide text-lg">Quem enxerga o quê</h3>
    <p class="text-sm text-olive-200/85 mt-1 max-w-2xl">
      Marque as telas liberadas para cada perfil. O Dashboard fica sempre visível e o administrador enxerga tudo.
    </p>
  </section>

  <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($perfis as $perfil): ?>
      <article class="rounded-xl bg-white/80 border border-olive-200/80 p-4 shadow-panel">
        <h4 class="font-display uppercase tracking-wide text-olive-900"><?= $rotulo[$perfil] ?? ucfirst($perfil) ?></h4>
        <ul class="mt-3 divide-y divide-olive-100">
          <?php foreach ($telas as $chave => $tela): ?>
            <li>
              <label class="flex items-center justify-between gap-3 py-2.5 cursor-pointer">
                <span>
                  <span class="text-sm text-olive-900"><?= htmlspecialchars($tela['label']) ?></span>
                  <span class="block text-xs text-olive-500"><?= htmlspecialchars($tela['rota']) ?></span>
                </span>
                <input type="checkbox" name="permissoes[<?= $perfil ?>][<?= $chave ?>]" value="1"
                       <?= !empty($matriz[$perfil][$chave]) ? 'checked' : '' ?>
                       class="w-5 h-5 shrink-0 rounded border-olive-300 text-olive-700 focus:ring-olive-400/40">
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      </article>
    <?php endforeach; ?>
  </section>

  <div class="flex flex-wrap gap-3">
    <button class="font-display tracking-wider uppercase text-sm px-5 py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition shadow-panel">
      Salvar permissões
    </button>
    <a href="/dashboard" class="text-sm px-4 py-2.5 text-olive-600 hover:text-olive-900">Cancelar</a>
  </div>
</form>
