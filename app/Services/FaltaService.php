<?php
/**
 * FaltaService — falta justificada / injustificada + substituição.
 *
 * Injustificada: nome em vermelho + botão para puxar reserva/substituto.
 * Justificada: nenhum atirador puxado; faltante não é prejudicado.
 */

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\EscalaRepository;
use App\Repositories\FaltaRepository;

final class FaltaService
{
    public function __construct(
        private FaltaRepository $faltas = new FaltaRepository(),
        private EscalaRepository $escalas = new EscalaRepository(),
    ) {}

    public function registrarJustificada(int $postoId, int $adminId, ?string $obs = null): void
    {
        $posto = $this->requirePosto($postoId);
        if ($posto['status'] !== 'escalado') {
            throw new ValidationException('Posto já tratado.');
        }

        $this->escalas->updatePostoStatus($postoId, 'falta_justificada');
        $this->faltas->registrar($postoId, 'justificada', null, $adminId, $obs);
    }

    public function registrarInjustificada(
        int $postoId,
        int $adminId,
        ?int $substitutoId,
        ?string $obs = null
    ): void {
        $posto = $this->requirePosto($postoId);
        if ($posto['status'] !== 'escalado') {
            throw new ValidationException('Posto já tratado.');
        }

        $this->escalas->updatePostoStatus($postoId, 'falta_injustificada');

        if ($substitutoId) {
            $escalaId = (int) $posto['escala_id'];
            $existente = $this->escalas->findPostoNaEscala($escalaId, $substitutoId);
            if ($existente) {
                // Reserva (ou outro posto) sobe para a função do faltante
                $this->escalas->updatePostoFuncaoStatus((int) $existente['id'], $posto['funcao'], 'escalado');
            } else {
                $this->escalas->addPosto($escalaId, $substitutoId, $posto['funcao']);
            }
        }

        $this->faltas->registrar($postoId, 'injustificada', $substitutoId, $adminId, $obs);
    }

    /** @return list<array<string,mixed>> */
    public function listar(): array
    {
        return $this->faltas->listar();
    }

    private function requirePosto(int $id): array
    {
        $p = $this->escalas->findPosto($id);
        if (!$p) {
            throw new NotFoundException('Posto não encontrado.');
        }
        // precisa do escala_id — findPosto já traz via join, mas escala_id está em ep.*
        return $p;
    }
}
