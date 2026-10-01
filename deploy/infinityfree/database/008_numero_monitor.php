<?php
/**
 * Monitores têm dois números. O de atirador identifica a pessoa;
 * o de monitor (01 a 34) é o que entra na escala, do último ao primeiro.
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

$colunas = array_column($pdo->query('PRAGMA table_info(usuarios)')->fetchAll(), 'name');
if (!in_array('numero_monitor', $colunas, true)) {
    $pdo->exec('ALTER TABLE usuarios ADD COLUMN numero_monitor TEXT');
}
$pdo->exec(
    'CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_numero_monitor ON usuarios(numero_monitor)'
);

$faltam = (int) $pdo->query(
    "SELECT COUNT(*) FROM usuarios
     WHERE perfil = 'monitor' AND (numero_monitor IS NULL OR numero_monitor = '')"
)->fetchColumn();

if ($faltam === 0) {
    echo 'Números de monitor já atribuídos.' . PHP_EOL;
    return;
}

// Do menor número de atirador ao maior: 117 vira monitor 01, 150 vira 34.
$monitores = $pdo->query(
    "SELECT id FROM usuarios
     WHERE perfil = 'monitor' AND (numero_monitor IS NULL OR numero_monitor = '')
     ORDER BY CAST(numero AS INTEGER), numero"
)->fetchAll();

$ocupados = $pdo->query(
    "SELECT CAST(numero_monitor AS INTEGER) FROM usuarios
     WHERE numero_monitor IS NOT NULL AND numero_monitor <> ''"
)->fetchAll(PDO::FETCH_COLUMN);
$usado = array_fill_keys(array_map('intval', $ocupados), true);

$proximo = 1;
$update = $pdo->prepare('UPDATE usuarios SET numero_monitor = ? WHERE id = ?');

foreach ($monitores as $monitor) {
    while (isset($usado[$proximo])) {
        $proximo++;
    }
    $update->execute([str_pad((string) $proximo, 2, '0', STR_PAD_LEFT), $monitor['id']]);
    $usado[$proximo] = true;
    $proximo++;
}

echo 'Números de monitor atribuídos a ' . count($monitores) . ' monitores.' . PHP_EOL;
