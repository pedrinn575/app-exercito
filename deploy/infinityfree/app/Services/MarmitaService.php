<?php
/**
 * MarmitaService — o militar pede; o administrador aprova, nega ou pede alteração.
 */

namespace App\Services;

use App\DTOs\MarmitaDTO;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Repositories\MarmitaRepository;

final class MarmitaService
{
    public function __construct(
        private MarmitaRepository $repo = new MarmitaRepository()
    ) {}

    public function solicitar(int $usuarioId, MarmitaDTO $dto): int
    {
        if ($this->repo->ativaNoDia($usuarioId, $dto->data)) {
            throw new ValidationException('Você já tem um pedido ativo nesta data.');
        }

        return $this->repo->create(
            $usuarioId,
            $dto->data,
            $dto->refeicao,
            $dto->quantidade,
            $dto->observacao
        );
    }

    /**
     * Reenvio do militar depois que o admin pediu alteração.
     */
    public function reenviar(int $id, int $usuarioId, MarmitaDTO $dto): void
    {
        $m = $this->require($id);
        if ((int) $m['usuario_id'] !== $usuarioId) {
            throw new UnauthorizedException('Você só altera os próprios pedidos.');
        }
        if (!in_array($m['status'], ['pendente', 'alteracao'], true)) {
            throw new ValidationException('Este pedido não pode mais ser alterado.');
        }

        $this->repo->update($id, $dto->data, $dto->refeicao, $dto->quantidade, $dto->observacao);
    }

    /** @return list<array<string,mixed>> */
    public function listar(?int $somenteUsuario = null): array
    {
        return $this->repo->listar($somenteUsuario);
    }

    public function buscar(int $id): array
    {
        return $this->require($id);
    }

    public function aprovar(int $id): void
    {
        $m = $this->require($id);
        $this->assertDecidivel($m);
        $this->repo->updateStatus($id, 'aprovado');
    }

    public function negar(int $id, ?string $motivo): void
    {
        $m = $this->require($id);
        $this->assertDecidivel($m);
        $this->repo->updateStatus($id, 'negado', $motivo);
    }

    public function pedirAlteracao(int $id, ?string $motivo): void
    {
        $m = $this->require($id);
        $this->assertDecidivel($m);
        if ($motivo === null || trim($motivo) === '') {
            throw new ValidationException('Diga o que o militar precisa alterar.');
        }
        $this->repo->updateStatus($id, 'alteracao', trim($motivo));
    }

    public function cancelar(int $id, int $usuarioId, bool $admin): void
    {
        $m = $this->require($id);
        if ($m['status'] === 'cancelado') {
            throw new ValidationException('Pedido já cancelado.');
        }
        if (!$admin && (int) $m['usuario_id'] !== $usuarioId) {
            throw new UnauthorizedException('Você só cancela os próprios pedidos.');
        }
        if (!$admin && !in_array($m['status'], ['pendente', 'alteracao'], true)) {
            throw new ValidationException('Pedido já decidido. Fale com o Subtenente.');
        }
        $this->repo->updateStatus($id, 'cancelado');
    }

    public function pendentes(): int
    {
        return $this->repo->countPendentes();
    }

    private function assertDecidivel(array $m): void
    {
        if (!in_array($m['status'], ['pendente', 'alteracao'], true)) {
            throw new ValidationException('Este pedido já foi decidido.');
        }
    }

    private function require(int $id): array
    {
        $m = $this->repo->findById($id);
        if (!$m) {
            throw new NotFoundException('Pedido de marmita não encontrado.');
        }
        return $m;
    }
}
