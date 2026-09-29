<?php
/**
 * TrocaService — pedido, aceite do destino e aprovação do admin (Subtenente).
 */

namespace App\Services;

use App\DTOs\TrocaDTO;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Repositories\EscalaRepository;
use App\Repositories\TrocaRepository;

final class TrocaService
{
    public function __construct(
        private TrocaRepository $trocas = new TrocaRepository(),
        private EscalaRepository $escalas = new EscalaRepository(),
    ) {}

    public function solicitar(TrocaDTO $dto): int
    {
        $posto = $this->escalas->findPosto($dto->escalaPostoId);
        if (!$posto) {
            throw new NotFoundException('Posto de escala não encontrado.');
        }
        if ((int) $posto['usuario_id'] !== $dto->solicitanteId) {
            throw new UnauthorizedException('Só o escalado pode solicitar a troca deste serviço.');
        }
        if ($posto['status'] !== 'escalado') {
            throw new ValidationException('Este posto não está disponível para troca.');
        }

        return $this->trocas->create(
            $dto->escalaPostoId,
            $dto->solicitanteId,
            $dto->destinoId,
            $dto->motivo
        );
    }

    public function aceitarDestino(int $trocaId, int $userId): void
    {
        $t = $this->requireTroca($trocaId);
        if ((int) $t['destino_id'] !== $userId) {
            throw new UnauthorizedException('Apenas o atirador de destino pode aceitar.');
        }
        if ($t['status'] !== 'pendente') {
            throw new ValidationException('Troca não está pendente.');
        }
        $this->trocas->updateStatus($trocaId, 'aceito_destino');
    }

    public function aprovarAdmin(int $trocaId): void
    {
        $t = $this->requireTroca($trocaId);
        if ($t['status'] === 'pendente') {
            throw new ValidationException('O outro militar ainda não respondeu a este pedido.');
        }
        if ($t['status'] !== 'aceito_destino') {
            throw new ValidationException('Troca não pode ser aprovada neste status.');
        }

        $posto = $this->escalas->findPosto((int) $t['escala_posto_id']);
        if ($this->escalas->findPostoNaEscala((int) $posto['escala_id'], (int) $t['destino_id'])) {
            throw new ValidationException('Esse militar já está escalado neste dia.');
        }

        // Efetiva: troca o usuário do posto, só neste dia
        $this->escalas->updatePostoUsuario((int) $t['escala_posto_id'], (int) $t['destino_id']);
        $this->trocas->updateStatus($trocaId, 'aprovado');
    }

    public function recusar(int $trocaId, int $userId, bool $admin): void
    {
        $t = $this->requireTroca($trocaId);

        if ($t['status'] === 'pendente') {
            if ((int) $t['destino_id'] !== $userId) {
                throw new UnauthorizedException('Só o militar indicado pode recusar este pedido.');
            }
            $this->trocas->updateStatus($trocaId, 'recusado');
            return;
        }

        if ($t['status'] === 'aceito_destino') {
            if (!$admin) {
                throw new UnauthorizedException('Só o administrador decide depois do aceite.');
            }
            $this->trocas->updateStatus($trocaId, 'recusado');
            return;
        }

        throw new ValidationException('Troca já finalizada.');
    }

    /**
     * O admin só vê o pedido depois que o outro militar aceitou ou recusou.
     *
     * @return list<array<string,mixed>>
     */
    public function listarParaAdmin(): array
    {
        return array_values(array_filter(
            $this->trocas->listar(),
            fn(array $t) => $t['status'] !== 'pendente'
        ));
    }

    /** @return list<array<string,mixed>> */
    public function doUsuario(int $userId): array
    {
        return $this->trocas->doUsuario($userId);
    }

    private function requireTroca(int $id): array
    {
        $t = $this->trocas->findById($id);
        if (!$t) {
            throw new NotFoundException('Pedido de troca não encontrado.');
        }
        return $t;
    }
}
