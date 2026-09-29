<?php
/**
 * Continuação: feriados, colunas de marmita e efetivo extra para a rotação.
 * Pode rodar sozinho ou a partir de migrate.php.
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

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS feriados (
        id        INTEGER PRIMARY KEY AUTOINCREMENT,
        data      TEXT NOT NULL UNIQUE,
        nome      TEXT NOT NULL,
        criado_em TEXT NOT NULL DEFAULT (datetime(\'now\',\'localtime\'))
    )'
);
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_feriados_data ON feriados(data)');

$colunas = array_column($pdo->query('PRAGMA table_info(marmitas)')->fetchAll(), 'name');
if (!in_array('refeicao', $colunas, true)) {
    $pdo->exec("ALTER TABLE marmitas ADD COLUMN refeicao TEXT NOT NULL DEFAULT 'almoco'");
}
if (!in_array('observacao', $colunas, true)) {
    $pdo->exec('ALTER TABLE marmitas ADD COLUMN observacao TEXT');
}

$hash = password_hash('123456', PASSWORD_BCRYPT);
$insert = $pdo->prepare(
    'INSERT OR IGNORE INTO usuarios (nome, numero, email, senha_hash, perfil) VALUES (?,?,?,?,?)'
);

$pessoas = [
    ['Sgt. Pereira', '050', 'monitor'],
    ['Sd. Monitor Reis', '090', 'monitor'],
    ['Sd. Monitor Nunes', '091', 'monitor'],
    ['Sd. Monitor Campos', '092', 'monitor'],
];

$sobrenomes = [
    'Moreira', 'Nascimento', 'Pinto', 'Queiroz', 'Ribeiro', 'Santos', 'Teixeira',
    'Uchoa', 'Vieira', 'Xavier', 'Zanetti', 'Araujo', 'Braga', 'Cunha', 'Duarte',
    'Esteves', 'Farias', 'Garcia', 'Holanda', 'Iglesias', 'Jardim', 'Keller', 'Luz', 'Machado',
];
$numero = 16;
foreach ($sobrenomes as $sobrenome) {
    $pessoas[] = ['Atirador ' . $sobrenome, str_pad((string) $numero, 3, '0', STR_PAD_LEFT), 'atirador'];
    $numero++;
}

foreach ($pessoas as [$nome, $num, $perfil]) {
    $insert->execute([$nome, $num, null, $hash, $perfil]);
}

echo "Continuação aplicada (feriados, marmitas, efetivo)." . PHP_EOL;
