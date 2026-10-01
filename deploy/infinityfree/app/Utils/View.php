<?php
/**
 * Renderização de views PHP + flash messages.
 */

namespace App\Utils;

final class View
{
    /**
     * Renderiza uma view dentro do layout principal.
     *
     * @param array<string,mixed> $data
     */
    public static function render(string $view, array $vars = [], string $layout = 'layouts/main'): void
    {
        extract($vars, EXTR_SKIP);
        $user = Auth::user();
        $flashError = $_SESSION['flash_error'] ?? null;
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $contentView = APP_PATH . '/Views/' . $view . '.php';
        ob_start();
        require $contentView;
        $content = ob_get_clean();

        require APP_PATH . '/Views/' . $layout . '.php';
    }

    public static function redirect(string $path, ?string $success = null, ?string $error = null): never
    {
        if ($success) {
            $_SESSION['flash_success'] = $success;
        }
        if ($error) {
            $_SESSION['flash_error'] = $error;
        }
        header('Location: ' . $path);
        exit;
    }
}
