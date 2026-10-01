<?php
/**
 * Script de migração: aplica schema + seed no SQLite.
 * Uso: php database/migrate.php
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Config/database.php';

use App\Config\Database;

$pdo = Database::connection();

$files = [
    BASE_PATH . '/database/001_schema.sql',
    BASE_PATH . '/database/002_seed.sql',
];

foreach ($files as $file) {
    echo "Aplicando: " . basename($file) . PHP_EOL;
    $sql = file_get_contents($file);
    $pdo->exec($sql);
}

require BASE_PATH . '/database/003_continue.php';
require BASE_PATH . '/database/004_marmitas_status.php';
require BASE_PATH . '/database/005_permissoes.php';
require BASE_PATH . '/database/006_perfis.php';
require BASE_PATH . '/database/007_efetivo.php';
require BASE_PATH . '/database/008_numero_monitor.php';

// Garante senha conhecida (password) para o admin e demais
$hash = password_hash('123456', PASSWORD_BCRYPT);
$pdo->prepare('UPDATE usuarios SET senha_hash = ?')->execute([$hash]);

echo "Migração concluída. Login: ADM001 / 123456" . PHP_EOL;
