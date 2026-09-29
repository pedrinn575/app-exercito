<?php
/**
 * Global Exception Handler.
 */

namespace App\Exceptions;

use App\Utils\Auth;
use Throwable;

final class Handler
{
    public static function handle(Throwable $e): void
    {
        http_response_code($e instanceof HttpException ? $e->getStatus() : 500);

        $message = $e->getMessage();
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['erro' => $message]);
            return;
        }

        // Sem permissão: quem já está logado volta ao painel; os demais vão ao login
        if ($e instanceof UnauthorizedException) {
            $_SESSION['flash_error'] = $message;
            header('Location: ' . (Auth::check() ? '/dashboard' : '/login'));
            exit;
        }

        require APP_PATH . '/Views/errors/error.php';
    }
}
