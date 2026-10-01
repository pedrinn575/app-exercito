<?php
/**
 * DTO de criação/atualização de militar.
 */

namespace App\DTOs;

use App\Exceptions\ValidationException;

final class UsuarioDTO
{
    public function __construct(
        public string $nome,
        public string $numero,
        public ?string $numeroMonitor,
        public string $perfil,
        public ?string $email = null,
        public ?string $senha = null,
        public bool $atestado = false,
        public ?string $atestadoInicio = null,
        public ?int $atestadoDias = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        $nome = trim((string) ($data['nome'] ?? ''));
        $numero = trim((string) ($data['numero'] ?? ''));
        $numeroMonitor = trim((string) ($data['numero_monitor'] ?? ''));
        $perfil = trim((string) ($data['perfil'] ?? 'atirador'));
        $email = trim((string) ($data['email'] ?? '')) ?: null;
        $senha = (string) ($data['senha'] ?? '') ?: null;
        $atestado = !empty($data['atestado']);
        $atestadoInicio = null;
        $atestadoDias = null;
        if ($atestado) {
            $atestadoInicio = trim((string) ($data['atestado_inicio'] ?? '')) ?: date('Y-m-d');
            $atestadoDias = (int) ($data['atestado_dias'] ?? 0);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $atestadoInicio)) {
                throw new ValidationException('Informe o início do atestado.');
            }
            if ($atestadoDias < 1 || $atestadoDias > 365) {
                throw new ValidationException('A quantidade de dias do atestado deve ficar entre 1 e 365.');
            }
        }

        if ($nome === '' || $numero === '') {
            throw new ValidationException('Nome e número são obrigatórios.');
        }

        $allowed = ['admin', 'atirador', 'monitor'];
        if (!in_array($perfil, $allowed, true)) {
            throw new ValidationException('Perfil inválido.');
        }

        if ($perfil === 'monitor') {
            if ($numeroMonitor === '' || !ctype_digit($numeroMonitor)) {
                throw new ValidationException('Informe o número de monitor.');
            }
            $numeroMonitor = str_pad($numeroMonitor, 2, '0', STR_PAD_LEFT);
        } else {
            $numeroMonitor = null;
        }

        return new self($nome, $numero, $numeroMonitor, $perfil, $email, $senha, $atestado, $atestadoInicio, $atestadoDias);
    }
}
