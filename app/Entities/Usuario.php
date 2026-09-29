<?php
/**
 * Entidade Usuario.
 */

namespace App\Entities;

final class Usuario
{
    public function __construct(
        public readonly ?int $id,
        public string $nome,
        public string $numero,
        public ?string $numeroMonitor,
        public ?string $email,
        public string $senhaHash,
        public string $perfil,
        public bool $ativo = true,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            nome: $row['nome'],
            numero: $row['numero'],
            numeroMonitor: ($row['numero_monitor'] ?? '') !== '' ? $row['numero_monitor'] : null,
            email: $row['email'] ?? null,
            senhaHash: $row['senha_hash'],
            perfil: $row['perfil'],
            ativo: (bool) $row['ativo'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'nome'   => $this->nome,
            'numero'         => $this->numero,
            'numero_monitor' => $this->numeroMonitor,
            'email'          => $this->email,
            'perfil' => $this->perfil,
            'ativo'  => $this->ativo,
        ];
    }
}
