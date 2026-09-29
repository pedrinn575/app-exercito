<?php
/** @var \App\Entities\Usuario|null $militar */
$edit = $militar !== null;
?>
<div class="max-w-xl">
  <form method="post" action="<?= $edit ? '/militares/' . (int)$militar->id : '/militares' ?>"
        class="rounded-xl bg-white/80 border border-olive-200/80 p-6 shadow-panel space-y-4">
    <div>
      <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Nome</label>
      <input name="nome" required value="<?= htmlspecialchars($militar->nome ?? '') ?>"
             class="w-full rounded-md border border-olive-300 px-3 py-2.5 outline-none focus:ring-2 focus:ring-olive-400/30">
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Número de atirador</label>
        <input name="numero" required value="<?= htmlspecialchars($militar->numero ?? '') ?>"
               class="w-full rounded-md border border-olive-300 px-3 py-2.5 outline-none focus:ring-2 focus:ring-olive-400/30">
        <span class="block text-[11px] text-olive-500 mt-1">É o login do militar.</span>
      </div>
      <div>
        <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Perfil</label>
        <select name="perfil" class="w-full rounded-md border border-olive-300 px-3 py-2.5 outline-none focus:ring-2 focus:ring-olive-400/30">
          <?php foreach (['atirador','monitor','admin'] as $p): ?>
            <option value="<?= $p ?>" <?= ($militar->perfil ?? 'atirador') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div>
      <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">Número de monitor</label>
      <input name="numero_monitor" inputmode="numeric" value="<?= htmlspecialchars($militar->numeroMonitor ?? '') ?>"
             class="w-full rounded-md border border-olive-300 px-3 py-2.5 outline-none focus:ring-2 focus:ring-olive-400/30"
             placeholder="Só para monitor">
      <span class="block text-[11px] text-olive-500 mt-1">Na escala de monitor vale este número, do último ao primeiro.</span>
    </div>
    <div>
      <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">E-mail</label>
      <input name="email" type="email" value="<?= htmlspecialchars($militar->email ?? '') ?>"
             class="w-full rounded-md border border-olive-300 px-3 py-2.5 outline-none focus:ring-2 focus:ring-olive-400/30">
    </div>
    <div>
      <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1">
        Senha <?= $edit ? '(deixe em branco para manter)' : '' ?>
      </label>
      <input name="senha" type="password" <?= $edit ? '' : 'required' ?>
             class="w-full rounded-md border border-olive-300 px-3 py-2.5 outline-none focus:ring-2 focus:ring-olive-400/30"
             placeholder="<?= $edit ? '••••••••' : '123456' ?>">
    </div>
    <div class="flex gap-3 pt-2">
      <button class="font-display tracking-wider uppercase text-sm px-5 py-2.5 rounded-md bg-olive-800 text-olive-50 hover:bg-olive-900 transition">
        <?= $edit ? 'Salvar' : 'Cadastrar' ?>
      </button>
      <a href="/militares" class="text-sm px-4 py-2.5 text-olive-600 hover:text-olive-900">Cancelar</a>
    </div>
  </form>
</div>
