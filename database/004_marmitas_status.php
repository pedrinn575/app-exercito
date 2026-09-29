<?php
/**
 * Novos status de marmita: aprovado, negado e alteracao.
 * O SQLite não altera CHECK, então a tabela é recriada preservando os dados.
 */

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!class_exists(\App\Config\Database::class)) {
    require BASE_PATH . '/app/Config/database.php';
}

use App\Config\Database;

$pdo = Database::connection();

$schema = (string) $pdo->query(
    "SELECT sql FROM sqlite_master WHERE type='table' AND name='marmitas'"
)->fetchColumn();

if (str_contains($schema, "'aprovado'")) {
    echo "Status de marmita já atualizados." . PHP_EOL;
    return;
}

$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->beginTransaction();

$pdo->exec(
    "CREATE TABLE marmitas_novo (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id  INTEGER NOT NULL REFERENCES usuarios(id),
        data_pedido TEXT    NOT NULL,
        refeicao    TEXT    NOT NULL DEFAULT 'almoco',
        quantidade  INTEGER NOT NULL DEFAULT 1,
        observacao  TEXT,
        retorno     TEXT,
        status      TEXT    NOT NULL DEFAULT 'pendente'
                    CHECK (status IN ('pendente', 'aprovado', 'negado', 'alteracao', 'cancelado')),
        criado_em   TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
    )"
);

$pdo->exec(
    "INSERT INTO marmitas_novo (id, usuario_id, data_pedido, refeicao, quantidade, observacao, status, criado_em)
     SELECT id, usuario_id, data_pedido, refeicao, quantidade, observacao,
            CASE status WHEN 'confirmado' THEN 'aprovado' ELSE status END,
            criado_em
     FROM marmitas"
);

$pdo->exec('DROP TABLE marmitas');
$pdo->exec('ALTER TABLE marmitas_novo RENAME TO marmitas');
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_marmitas_data ON marmitas(data_pedido)');
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_marmitas_status ON marmitas(status)');

$pdo->commit();
$pdo->exec('PRAGMA foreign_keys = ON');

echo "Marmitas: status aprovado/negado/alteracao aplicados." . PHP_EOL;
