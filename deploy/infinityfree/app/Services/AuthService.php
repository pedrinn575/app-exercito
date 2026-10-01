<?php
/**
 * AuthService — regras de autenticação (BCrypt).
 */

namespace App\Services;

use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Repositories\UsuarioRepository;
use App\Utils\Auth;

final class AuthService
{
    public function __construct(
        private UsuarioRepository $usuarios = new UsuarioRepository()
    ) {}

    public function login(string $numero, string $senha): void
    {
        $numero = trim($numero);
        if ($numero === '' || $senha === '') {
            throw new ValidationException('Informe número e senha.');
        }

        $user = $this->usuarios->findByNumero($numero);
        if ($user === null || !password_verify($senha, $user->senhaHash)) {
            throw new UnauthorizedException('Credenciais inválidas.');
        }

        Auth::login($user->toArray());
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
