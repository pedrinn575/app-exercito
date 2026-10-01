<?php
/** @var array{inicio:string,fim:string,dias:list<array{data:string,pedidos:list<array<string,mixed>>,total:int}>} $folha */
use App\Utils\Calendar;

$refeicaoLabel = ['almoco' => 'Almoço', 'jantar' => 'Jantar', 'ambos' => 'Almoço e jantar'];
$geral = 0;
foreach ($folha['dias'] as $dia) {
    $geral += $dia['total'];
}
$cfg = require APP_PATH . '/Config/app.php';
?>
<div class="folha">
  <div class="topo">
    <div>
      <p class="meta"><?= htmlspecialchars($cfg['unit']) ?></p>
      <h1>Pedidos de marmita</h1>
      <p class="meta"><?= Calendar::formatBr($folha['inicio']) ?> a <?= Calendar::formatBr($folha['fim']) ?> · <?= $geral ?> marmita(s)</p>
    </div>
    <button class="no-print" type="button" onclick="window.print()">Salvar PDF</button>
  </div>

  <?php foreach ($folha['dias'] as $dia): ?>
    <section>
      <h2><?= Calendar::weekdayBr($dia['data']) ?>, <?= Calendar::formatBr($dia['data']) ?></h2>
      <?php if (!$dia['pedidos']): ?>
        <p class="vazio">Nenhum pedido aprovado.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Nº</th>
              <th>Nome</th>
              <th>Refeição</th>
              <th>Qtd</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dia['pedidos'] as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['numero']) ?></td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($refeicaoLabel[$p['refeicao']] ?? $p['refeicao']) ?></td>
                <td><?= (int) $p['quantidade'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p class="total">Total do dia: <?= (int) $dia['total'] ?></p>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
</div>
