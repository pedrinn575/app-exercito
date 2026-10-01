<?php
/** @var array{modo:string,blocos:list<list<array{data:string,cmt:?string,cb:?string,sentinelas:list<string>}>>} $folha */
use App\Utils\Calendar;

$cfg = require APP_PATH . '/Config/app.php';
$hoje = date('Y-m-d');
?>
<div class="barra no-print">
  <button type="button" onclick="window.print()">Salvar PDF</button>
</div>
<div class="pagina">
  <style>
    h1 { margin: 0 0 14px; text-align: center; color: #7a1c1c; font-size: 18px; letter-spacing: 0.12em; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
    h2 { margin: 0 0 8px; text-align: center; font-size: 16px; font-weight: 700; }
    .bloco { margin-bottom: 22px; break-inside: avoid; }
    .bloco + .bloco { break-before: page; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #111; text-align: center; vertical-align: middle; padding: 4px 3px; font-size: 11px; }
    thead th { font-weight: 700; padding: 6px 3px; }
    .dia { display: block; }
    .sem { display: block; font-weight: 700; }
    .cargo { width: 58px; font-weight: 700; line-height: 1.15; }
    .nome { font-weight: 700; line-height: 1.2; }
    .sentinelas { vertical-align: top; padding-top: 6px; }
    .sentinelas div { font-weight: 700; line-height: 1.25; margin-bottom: 1px; }
    .fiscal { font-weight: 700; letter-spacing: 0.08em; padding: 8px 4px; }
    .local { margin: 8px 0 0; font-size: 13px; }
    .assinatura { margin-top: 36px; text-align: center; }
    .assinatura strong { display: block; letter-spacing: 0.02em; }
    .assinatura span { display: block; font-size: 13px; }
  </style>

  <?php foreach ($folha['blocos'] as $bloco): ?>
    <section class="bloco">
      <h1>PREVISÃO DA ESCALA DE SERVIÇO</h1>
      <h2>PREVISÃO DA Escala de Serviço para os dias:</h2>
      <table>
        <thead>
          <tr>
            <th class="cargo"></th>
            <?php foreach ($bloco as $dia): ?>
              <th>
                <span class="dia"><?= htmlspecialchars(Calendar::diaDocumento($dia['data'])) ?></span>
                <span class="sem">(<?= Calendar::weekdayShort($dia['data']) ?>)</span>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th class="cargo">Cmt<br>Gd</th>
            <?php foreach ($bloco as $dia): ?>
              <td class="nome"><?= $dia['cmt'] ? htmlspecialchars($dia['cmt']) : '' ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <th class="cargo">Cb<br>Gd</th>
            <?php foreach ($bloco as $dia): ?>
              <td class="nome"><?= $dia['cb'] ? htmlspecialchars($dia['cb']) : '' ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <th class="cargo">Sentinelas</th>
            <?php foreach ($bloco as $dia): ?>
              <td class="sentinelas">
                <?php foreach ($dia['sentinelas'] as $linha): ?>
                  <div><?= htmlspecialchars($linha) ?></div>
                <?php endforeach; ?>
              </td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <th class="cargo">Fisc<br>Sv</th>
            <td class="fiscal" colspan="<?= count($bloco) ?>"><?= htmlspecialchars($cfg['fiscal_servico']) ?></td>
          </tr>
        </tbody>
      </table>
        <p class="local">Quartel em <?= htmlspecialchars($cfg['quartel']) ?>, <?= Calendar::dataExtenso($hoje) ?>.</p>
        <div class="assinatura">
          <strong><?= htmlspecialchars($cfg['assinatura']) ?></strong>
          <span><?= htmlspecialchars($cfg['cargo_assinatura']) ?></span>
        </div>
    </section>
  <?php endforeach; ?>
</div>
