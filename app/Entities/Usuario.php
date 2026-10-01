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
        public bool $atestado = false,
        public ?string $atestadoInicio = null,
        public ?int $atestadoDias = null,
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
            atestado: (bool) ($row['atestado'] ?? 0),
            atestadoInicio: ($row['atestado_inicio'] ?? '') !== '' ? $row['atestado_inicio'] : null,
            atestadoDias: isset($row['atestado_dias']) && $row['atestado_dias'] !== '' && $row['atestado_dias'] !== null
                ? (int) $row['atestado_dias']
                : null,
        );
    }

    /** Último dia coberto pelo atestado, inclusive. */
    public function fimAtestado(): ?string
    {
        if ($this->atestadoInicio === null || $this->atestadoDias === null || $this->atestadoDias < 1) {
            return null;
        }

        return date('Y-m-d', strtotime($this->atestadoInicio . ' +' . ($this->atestadoDias - 1) . ' days'));
    }

    /** Ainda vale hoje ou começa depois. Atestado antigo sem prazo continua valendo. */
    public function atestadoAberto(?string $hoje = null): bool
    {
        if (!$this->atestado) {
            return false;
        }
        $fim = $this->fimAtestado();
        if ($this->atestadoInicio === null || $fim === null) {
            return true;
        }

        return $fim >= ($hoje ?? date('Y-m-d'));
    }

    public function emAtestadoEm(string $data): bool
    {
        if (!$this->atestado) {
            return false;
        }
        $fim = $this->fimAtestado();
        if ($this->atestadoInicio === null || $fim === null) {
            return true;
        }

        return $data >= $this->atestadoInicio && $data <= $fim;
    }

    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'nome'   => $this->nome,
            'numero'         => $this->numero,
            'numero_monitor' => $this->numeroMonitor,
            'email'          => $this->email,
            'perfil'   => $this->perfil,
            'ativo'    => $this->ativo,
            'atestado' => $this->atestado,
            'atestado_inicio' => $this->atestadoInicio,
            'atestado_dias' => $this->atestadoDias,
        ];
    }
}
