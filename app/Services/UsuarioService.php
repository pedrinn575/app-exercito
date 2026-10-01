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
            atestado: $dto->atestado,
            atestadoInicio: $dto->atestado ? $dto->atestadoInicio : null,
            atestadoDias: $dto->atestado ? $dto->atestadoDias : null,
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
        $atual->atestado = $dto->atestado;
        $atual->atestadoInicio = $dto->atestado ? $dto->atestadoInicio : null;
        $atual->atestadoDias = $dto->atestado ? $dto->atestadoDias : null;
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

    /** @return list<Usuario> */
    public function listarAtestados(): array
    {
        return $this->repo->comAtestado();
    }

    /** @return list<Usuario> */
    public function listarElegiveisAtestado(): array
    {
        return array_values(array_filter(
            $this->repo->all(),
            fn(Usuario $u) => $u->perfil !== 'admin' && !$u->atestadoAberto()
        ));
    }

    public function definirAtestado(int $id, bool $atestado, ?string $inicio = null, ?int $dias = null): void
    {
        $usuario = $this->buscar($id);
        if ($usuario->perfil === 'admin') {
            throw new ValidationException('Administrador não entra na escala.');
        }
        if (!$atestado) {
            $usuario->atestado = false;
            $usuario->atestadoInicio = null;
            $usuario->atestadoDias = null;
            $this->repo->update($usuario);
            return;
        }

        $inicio = trim((string) $inicio) ?: date('Y-m-d');
        $dias = (int) $dias;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio)) {
            throw new ValidationException('Informe o início do atestado.');
        }
        if ($dias < 1 || $dias > 365) {
            throw new ValidationException('A quantidade de dias do atestado deve ficar entre 1 e 365.');
        }

        $usuario->atestado = true;
        $usuario->atestadoInicio = $inicio;
        $usuario->atestadoDias = $dias;
        $this->repo->update($usuario);
    }

    public function desativar(int $id): void
    {
        $this->buscar($id);
        $this->repo->softDelete($id);
    }
}
