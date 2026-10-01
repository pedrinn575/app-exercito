<?php
namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Services\AuthService;
use App\Utils\View;

final class AuthController
{
    public function __construct(
        private AuthService $auth = new AuthService()
    ) {}

    public function showLogin(): void
    {
        View::render('auth/login', [], 'layouts/guest');
    }

    public function login(): void
    {
        try {
            $this->auth->login($_POST['numero'] ?? '', $_POST['senha'] ?? '');
            View::redirect('/dashboard', 'Bem-vindo de volta.');
        } catch (HttpException $e) {
            View::redirect('/login', null, $e->getMessage());
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        View::redirect('/login', 'Sessão encerrada.');
    }
}
