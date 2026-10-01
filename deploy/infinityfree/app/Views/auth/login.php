<?php
/** @var string|null $flashError */
?>
<form method="post" action="/login" class="space-y-5">
  <div>
    <h2 class="font-display text-2xl tracking-wide text-olive-900 uppercase">Acesso</h2>
    <p class="text-sm text-olive-600 mt-1">Entre com seu número de identificação.</p>
  </div>

  <div>
    <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1.5" for="numero">Número</label>
    <input id="numero" name="numero" required autofocus
           class="w-full rounded-md border border-olive-300 bg-white px-3 py-2.5 text-olive-900 outline-none focus:border-olive-500 focus:ring-2 focus:ring-olive-400/30 transition"
           placeholder="Ex: ADM001" value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>">
  </div>

  <div>
    <label class="block text-xs uppercase tracking-wider text-olive-700 mb-1.5" for="senha">Senha</label>
    <input id="senha" name="senha" type="password" required
           class="w-full rounded-md border border-olive-300 bg-white px-3 py-2.5 text-olive-900 outline-none focus:border-olive-500 focus:ring-2 focus:ring-olive-400/30 transition"
           placeholder="••••••••">
  </div>

  <button type="submit"
          class="w-full font-display tracking-wider uppercase bg-olive-800 hover:bg-olive-900 text-olive-50 py-3 rounded-md transition shadow-lg shadow-olive-900/20">
    Entrar
  </button>

  <p class="text-xs text-olive-500 text-center pt-2">
    Demo: <strong>ADM001</strong> / <strong>123456</strong>
  </p>
</form>
