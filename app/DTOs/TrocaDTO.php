<?php
namespace App\DTOs;

use App\Exceptions\ValidationException;

final class TrocaDTO
{
    public function __construct(
        public int $escalaPostoId,
        public int $solicitanteId,
        public int $destinoId,
        public ?string $motivo = null,
    ) {}

    public static function fromRequest(array $data, int $solicitanteId): self
    {
        $posto = (int) ($data['escala_posto_id'] ?? 0);
        $destino = (int) ($data['destino_id'] ?? 0);
        $motivo = trim((string) ($data['motivo'] ?? '')) ?: null;

        if ($posto <= 0 || $destino <= 0) {
            throw new ValidationException('Informe o serviço e o atirador de destino.');
        }
        if ($destino === $solicitanteId) {
            throw new ValidationException('Não é possível trocar consigo mesmo.');
        }

        return new self($posto, $solicitanteId, $destino, $motivo);
    }
}
