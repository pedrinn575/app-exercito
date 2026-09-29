<?php
namespace App\Entities;

final class Escala
{
    public function __construct(
        public readonly ?int $id,
        public string $tipo,
        public string $dataServico,
        public ?string $observacao = null,
        public ?int $criadoPor = null,
        /** @var array<int,array<string,mixed>> */
        public array $postos = [],
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            tipo: $row['tipo'],
            dataServico: $row['data_servico'],
            observacao: $row['observacao'] ?? null,
            criadoPor: isset($row['criado_por']) ? (int) $row['criado_por'] : null,
        );
    }
}
