<?php
/**
 * Permissões por perfil: quais telas cada patente enxerga.
 */

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!class_exists(\App\Config\Database::class)) {
    require BASE_PATH . '/app/Config/database.php';
}

use App\Config\Database;

$pdo = Database::connection();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS permissoes (
        id        INTEGER PRIMARY KEY AUTOINCREMENT,
        perfil    TEXT    NOT NULL,
        chave     TEXT    NOT NULL,
        permitido INTEGER NOT NULL DEFAULT 0,
        UNIQUE (perfil, chave)
    )'
);
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_permissoes_perfil ON permissoes(perfil)');

$menu = require APP_PATH . '/Config/menu.php';
$perfis = ['atirador', 'monitor'];

// Padrão da tropa: acompanha as escalas pelo calendário
$liberadasPorPadrao = ['meus_servicos', 'calendario', 'trocas', 'faltas', 'marmitas'];

$insert = $pdo->prepare(
    'INSERT OR IGNORE INTO permissoes (perfil, chave, permitido) VALUES (?,?,?)'
);

foreach ($perfis as $perfil) {
    foreach ($menu as $chave => $item) {
        if (!empty($item['fixo']) || !empty($item['so_admin'])) {
            continue;
        }
        $insert->execute([$perfil, $chave, in_array($chave, $liberadasPorPadrao, true) ? 1 : 0]);
    }
}

echo "Permissões por perfil aplicadas." . PHP_EOL;
