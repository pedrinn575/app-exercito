<?php
/**
 * Entrada de um pedido de marmita.
 */

namespace App\DTOs;

use App\Exceptions\ValidationException;

final class MarmitaDTO
{
    public function __construct(
        public string $data,
        public string $refeicao,
        public int $quantidade,
        public ?string $observacao,
    ) {}

    public static function fromRequest(array $data): self
    {
        $dia = trim((string) ($data['data_pedido'] ?? ''));
        $refeicao = trim((string) ($data['refeicao'] ?? 'almoco'));
        $obs = trim((string) ($data['observacao'] ?? '')) ?: null;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia)) {
            throw new ValidationException('Informe a data da marmita.');
        }
        if ($dia < date('Y-m-d')) {
            throw new ValidationException('Não é possível pedir marmita para um dia que já passou.');
        }
        if (!in_array($refeicao, ['almoco', 'jantar', 'ambos'], true)) {
            throw new ValidationException('Refeição inválida.');
        }
        $qtd = $refeicao === 'ambos' ? 2 : 1;

        return new self($dia, $refeicao, $qtd, $obs);
    }
}
