<?php
/**
 * Só existem monitores e atiradores: cabo e comandante saem do sistema.
 * O SQLite não altera CHECK, então usuarios e escala_postos são recriados.
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

$schemaUsuarios = (string) $pdo->query(
    "SELECT sql FROM sqlite_master WHERE type='table' AND name='usuarios'"
)->fetchColumn();

if (!str_contains($schemaUsuarios, "'cabo'")) {
    echo "Perfis já reduzidos a monitor/atirador." . PHP_EOL;
    return;
}

$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->beginTransaction();

// Cabo e comandante integravam a equipe de instrução: viram monitores
$pdo->exec(
    "CREATE TABLE usuarios_novo (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        nome          TEXT    NOT NULL,
        numero        TEXT    NOT NULL UNIQUE,
        email         TEXT    UNIQUE,
        senha_hash    TEXT    NOT NULL,
        perfil        TEXT    NOT NULL CHECK (perfil IN ('admin', 'atirador', 'monitor')),
        ativo         INTEGER NOT NULL DEFAULT 1,
        criado_em     TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
        atualizado_em TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
    )"
);

$pdo->exec(
    "INSERT INTO usuarios_novo (id, nome, numero, email, senha_hash, perfil, ativo, criado_em, atualizado_em)
     SELECT id,
            REPLACE(REPLACE(nome, 'Cabo ', 'Sd. Monitor '), 'Comandante ', 'Sd. Monitor '),
            numero,
            REPLACE(email, 'cabo@', 'monitor1@'),
            senha_hash,
            CASE WHEN perfil IN ('cabo', 'comandante') THEN 'monitor' ELSE perfil END,
            ativo, criado_em, atualizado_em
     FROM usuarios"
);

$pdo->exec('DROP TABLE usuarios');
$pdo->exec('ALTER TABLE usuarios_novo RENAME TO usuarios');
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_usuarios_numero ON usuarios(numero)');
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_usuarios_perfil ON usuarios(perfil)');

$pdo->exec(
    "CREATE TABLE escala_postos_novo (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        escala_id  INTEGER NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
        usuario_id INTEGER NOT NULL REFERENCES usuarios(id),
        funcao     TEXT    NOT NULL CHECK (funcao IN ('monitor', 'atirador', 'reserva')),
        status     TEXT    NOT NULL DEFAULT 'escalado'
                   CHECK (status IN ('escalado', 'falta_injustificada', 'falta_justificada', 'substituido')),
        UNIQUE (escala_id, usuario_id)
    )"
);

$pdo->exec(
    "INSERT INTO escala_postos_novo (id, escala_id, usuario_id, funcao, status)
     SELECT id, escala_id, usuario_id,
            CASE WHEN funcao IN ('cabo', 'comandante') THEN 'monitor' ELSE funcao END,
            status
     FROM escala_postos"
);

$pdo->exec('DROP TABLE escala_postos');
$pdo->exec('ALTER TABLE escala_postos_novo RENAME TO escala_postos');
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_postos_escala ON escala_postos(escala_id)');
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_postos_usuario ON escala_postos(usuario_id)');

$pdo->exec("DELETE FROM permissoes WHERE perfil IN ('cabo', 'comandante')");

$pdo->commit();
$pdo->exec('PRAGMA foreign_keys = ON');

echo "Perfis reduzidos a monitor/atirador." . PHP_EOL;
