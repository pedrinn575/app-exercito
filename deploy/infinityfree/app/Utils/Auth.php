<?php
/**
 * Helpers de autenticação e sessão.
 */

namespace App\Utils;

use App\Exceptions\UnauthorizedException;
use App\Services\PermissaoService;

final class Auth
{
    private static function key(): string
    {
        $cfg = require APP_PATH . '/Config/app.php';
        return $cfg['session_key'];
    }

    public static function login(array $user): void
    {
        $_SESSION[self::key()] = [
            'id'     => (int) $user['id'],
            'nome'   => $user['nome'],
            'numero' => $user['numero'],
            'perfil' => $user['perfil'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION[self::key()]);
    }

    public static function user(): ?array
    {
        return $_SESSION[self::key()] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if ($user === null) {
            throw new UnauthorizedException('Faça login para continuar.');
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireLogin();
        if ($user['perfil'] !== 'admin') {
            throw new UnauthorizedException('Apenas administradores.');
        }
        return $user;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['perfil'] ?? '') === 'admin';
    }

    /**
     * O perfil logado tem acesso a esta tela?
     */
    public static function pode(string $chave): bool
    {
        $perfil = self::user()['perfil'] ?? null;
        if ($perfil === null) {
            return false;
        }
        return (new PermissaoService())->permite($perfil, $chave);
    }

    /**
     * Exige login e permissão para a tela.
     *
     * @return array<string,mixed>
     */
    public static function requirePermissao(string $chave): array
    {
        $user = self::requireLogin();
        if (!self::pode($chave)) {
            throw new UnauthorizedException('Você não tem acesso a esta área.');
        }
        return $user;
    }
}
