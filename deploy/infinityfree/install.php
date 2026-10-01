<?php
/**
 * Cria o banco na primeira visita. Apague este arquivo depois.
 */

declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');

require_once APP_PATH . '/Config/autoload.php';
require_once APP_PATH . '/Config/database.php';

use App\Config\Database;

header('Content-Type: text/plain; charset=UTF-8');

$lock = BASE_PATH . '/storage/.installed';
if (is_file($lock)) {
    exit("O banco já foi criado. Apague o arquivo install.php no gerenciador.\n");
}

try {
    $pdo = Database::connection();
    $pdo->exec((string) file_get_contents(BASE_PATH . '/database/001_schema.sql'));
    $pdo->exec((string) file_get_contents(BASE_PATH . '/database/002_seed.sql'));

    require BASE_PATH . '/database/003_continue.php';
    require BASE_PATH . '/database/004_marmitas_status.php';
    require BASE_PATH . '/database/005_permissoes.php';
    require BASE_PATH . '/database/006_perfis.php';
    require BASE_PATH . '/database/007_efetivo.php';
    require BASE_PATH . '/database/008_numero_monitor.php';
    require BASE_PATH . '/database/009_atestado_troca.php';

    $hash = password_hash('123456', PASSWORD_BCRYPT);
    $pdo->prepare('UPDATE usuarios SET senha_hash = ?')->execute([$hash]);

    file_put_contents($lock, date('c'));
    echo "\nInstalação concluída. Entre com ADM001 / 123456\n";
    echo "Agora apague o arquivo install.php.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Falhou: ' . $e->getMessage() . "\n";
}
