<?php
/**
 * Efetivo do Tiro de Guerra: 150 militares, sendo 116 atiradores (001-116)
 * e 34 monitores (117-150). A numeração define a ordem da rotação.
 */

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!class_exists(\App\Config\Database::class)) {
    require BASE_PATH . '/app/Config/database.php';
}

use App\Config\Database;

const TOTAL_EFETIVO = 150;
const TOTAL_MONITORES = 34;

$pdo = Database::connection();

// Posição de onde cada função começou naquele dia: é o que torna a
// rotação sequencial estável mesmo refazendo uma escala antiga.
$colunas = array_column($pdo->query('PRAGMA table_info(escalas)')->fetchAll(), 'name');
if (!in_array('inicio_monitor', $colunas, true)) {
    $pdo->exec('ALTER TABLE escalas ADD COLUMN inicio_monitor INTEGER NOT NULL DEFAULT 0');
}
if (!in_array('inicio_atirador', $colunas, true)) {
    $pdo->exec('ALTER TABLE escalas ADD COLUMN inicio_atirador INTEGER NOT NULL DEFAULT 0');
}

$tropa = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil <> 'admin'")->fetchColumn();
if ($tropa === TOTAL_EFETIVO) {
    echo 'Efetivo de ' . TOTAL_EFETIVO . ' militares já cadastrado.' . PHP_EOL;
    return;
}

$sobrenomes = [
    'Almeida', 'Andrade', 'Araujo', 'Assis', 'Barbosa', 'Barros', 'Batista', 'Bezerra',
    'Braga', 'Camargo', 'Campos', 'Cardoso', 'Carvalho', 'Castro', 'Cavalcanti', 'Coelho',
    'Correia', 'Costa', 'Cunha', 'Dias', 'Duarte', 'Esteves', 'Farias', 'Fernandes',
    'Ferreira', 'Fonseca', 'Freitas', 'Garcia', 'Gomes', 'Gonçalves', 'Guimarães', 'Henriques',
    'Lima', 'Lopes', 'Machado', 'Macedo', 'Magalhães', 'Marques', 'Martins', 'Medeiros',
    'Melo', 'Mendes', 'Moraes', 'Moreira', 'Nascimento', 'Neves', 'Nogueira', 'Nunes',
    'Oliveira', 'Pacheco',
];

$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->beginTransaction();

// A tropa antiga sai inteira, junto com tudo que dependia dela
foreach (['faltas', 'trocas', 'marmitas', 'escala_postos', 'escalas'] as $tabela) {
    $pdo->exec('DELETE FROM ' . $tabela);
}
$pdo->exec("DELETE FROM usuarios WHERE perfil <> 'admin'");

$colunas = array_column($pdo->query('PRAGMA table_info(usuarios)')->fetchAll(), 'name');
if (!in_array('numero_monitor', $colunas, true)) {
    $pdo->exec('ALTER TABLE usuarios ADD COLUMN numero_monitor TEXT');
}

$hash = password_hash('123456', PASSWORD_BCRYPT);
$insert = $pdo->prepare(
    'INSERT INTO usuarios (nome, numero, numero_monitor, email, senha_hash, perfil) VALUES (?,?,?,?,?,?)'
);

$totalAtiradores = TOTAL_EFETIVO - TOTAL_MONITORES;
$numeroMonitor = 1;

for ($i = 0; $i < TOTAL_EFETIVO; $i++) {
    $monitor = $i >= $totalAtiradores;
    $sobrenome = $sobrenomes[$i % count($sobrenomes)];
    $inicial = chr(ord('A') + intdiv($i, count($sobrenomes)));
    $nome = ($monitor ? 'Sd. Monitor ' : 'Atirador ') . $inicial . '. ' . $sobrenome;

    $insert->execute([
        $nome,
        str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
        $monitor ? str_pad((string) $numeroMonitor++, 2, '0', STR_PAD_LEFT) : null,
        null,
        $hash,
        $monitor ? 'monitor' : 'atirador',
    ]);
}

$pdo->commit();
$pdo->exec('PRAGMA foreign_keys = ON');

echo 'Efetivo recriado: ' . $totalAtiradores . ' atiradores (001-'
    . str_pad((string) $totalAtiradores, 3, '0', STR_PAD_LEFT) . ') e '
    . TOTAL_MONITORES . ' monitores ('
    . str_pad((string) ($totalAtiradores + 1), 3, '0', STR_PAD_LEFT) . '-'
    . TOTAL_EFETIVO . ').' . PHP_EOL;
