<?php
/**
 * Atestado no militar e marca de troca no posto.
 * Pode rodar mais de uma vez.
 */

declare(strict_types=1);

use App\Config\Database;

$pdo = Database::connection();

$temUsuarios = (bool) $pdo->query(
    "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'usuarios'"
)->fetchColumn();

if (!$temUsuarios) {
    return;
}

$colunasUsuario = array_column($pdo->query('PRAGMA table_info(usuarios)')->fetchAll(), 'name');
if (!in_array('atestado', $colunasUsuario, true)) {
    $pdo->exec('ALTER TABLE usuarios ADD COLUMN atestado INTEGER NOT NULL DEFAULT 0');
}
if (!in_array('atestado_inicio', $colunasUsuario, true)) {
    $pdo->exec('ALTER TABLE usuarios ADD COLUMN atestado_inicio TEXT');
}
if (!in_array('atestado_dias', $colunasUsuario, true)) {
    $pdo->exec('ALTER TABLE usuarios ADD COLUMN atestado_dias INTEGER');
}

$temPostos = (bool) $pdo->query(
    "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'escala_postos'"
)->fetchColumn();

if ($temPostos) {
    $colunasPosto = array_column($pdo->query('PRAGMA table_info(escala_postos)')->fetchAll(), 'name');
    if (!in_array('trocado', $colunasPosto, true)) {
        $pdo->exec('ALTER TABLE escala_postos ADD COLUMN trocado INTEGER NOT NULL DEFAULT 0');
    }
}

if (PHP_SAPI === 'cli') {
    echo "Atestado e marca de troca conferidos." . PHP_EOL;
}
