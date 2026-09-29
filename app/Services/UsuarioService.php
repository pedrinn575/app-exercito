<?php
/**
 * UsuarioService — cadastro de militares e administradores.
 */

namespace App\Services;

use App\DTOs\UsuarioDTO;
use App\Entities\Usuario;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\UsuarioRepository;

final class UsuarioService
{
    public function __construct(
        private UsuarioRepository $repo = new UsuarioRepository()
    ) {}

    /** @return list<Usuario> */
    public function listar(): array
    {
        return $this->repo->all();
    }

    public function buscar(int $id): Usuario
    {
        $u = $this->repo->findById($id);
        if (!$u) {
            throw new NotFoundException('Militar não encontrado.');
        }
        return $u;
    }

    public function criar(UsuarioDTO $dto): int
    {
        if ($this->repo->findByNumero($dto->numero)) {
            throw new ValidationException('Número de atirador já cadastrado.');
        }
        $this->assertNumeroMonitor($dto, null);
        $senha = $dto->senha ?: '123456';
        $entity = new Usuario(
            id: null,
            nome: $dto->nome,
            numero: $dto->numero,
            numeroMonitor: $dto->numeroMonitor,
            email: $dto->email,
            senhaHash: password_hash($senha, PASSWORD_BCRYPT),
            perfil: $dto->perfil,
        );
        return $this->repo->create($entity);
    }

    public function atualizar(int $id, UsuarioDTO $dto): void
    {
        $atual = $this->buscar($id);
        $outro = $this->repo->findByNumero($dto->numero);
        if ($outro && $outro->id !== $id) {
            throw new ValidationException('Número de atirador já em uso por outro militar.');
        }
        $this->assertNumeroMonitor($dto, $id);

        $atual->nome = $dto->nome;
        $atual->numero = $dto->numero;
        $atual->numeroMonitor = $dto->numeroMonitor;
        $atual->email = $dto->email;
        $atual->perfil = $dto->perfil;
        $this->repo->update($atual);

        if ($dto->senha) {
            $this->repo->updateSenha($id, password_hash($dto->senha, PASSWORD_BCRYPT));
        }
    }

    private function assertNumeroMonitor(UsuarioDTO $dto, ?int $excetoId): void
    {
        if ($dto->perfil !== 'monitor' || $dto->numeroMonitor === null) {
            return;
        }
        $outro = $this->repo->findByNumeroMonitor($dto->numeroMonitor);
        if ($outro && $outro->id !== $excetoId) {
            throw new ValidationException('Número de monitor já em uso.');
        }
    }

    public function desativar(int $id): void
    {
        $this->buscar($id);
        $this->repo->softDelete($id);
    }
}
