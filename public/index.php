<?php
/**
 * Front Controller — ponto único de entrada da aplicação.
 * Fluxo: Request → Router → Controller → Service → Repository → Database
 */

declare(strict_types=1);

session_start();

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require_once APP_PATH . '/Config/autoload.php';
require_once APP_PATH . '/Config/database.php';
require_once APP_PATH . '/Config/app.php';

use App\Config\Router;
use App\Exceptions\Handler;

try {
    $router = new Router();
    require APP_PATH . '/Config/routes.php';
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    Handler::handle($e);
}
