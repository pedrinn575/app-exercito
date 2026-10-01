<?php
namespace App\Controllers;

use App\DTOs\UsuarioDTO;
use App\Exceptions\HttpException;
use App\Services\UsuarioService;
use App\Utils\Auth;
use App\Utils\View;

final class MilitarController
{
    public function __construct(
        private UsuarioService $service = new UsuarioService()
    ) {}

    public function index(): void
    {
        Auth::requirePermissao('militares');
        View::render('militares/index', [
            'militares' => $this->service->listar(),
            'pageTitle' => 'Militares',
        ]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        View::render('militares/form', [
            'militar'   => null,
            'pageTitle' => 'Novo militar',
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        try {
            $dto = UsuarioDTO::fromRequest($_POST);
            $this->service->criar($dto);
            View::redirect('/militares', 'Militar cadastrado com sucesso.');
        } catch (HttpException $e) {
            View::redirect('/militares/criar', null, $e->getMessage());
        }
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $militar = $this->service->buscar((int) $id);
        View::render('militares/form', [
            'militar'   => $militar,
            'pageTitle' => 'Editar militar',
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();
        try {
            $dto = UsuarioDTO::fromRequest($_POST);
            $this->service->atualizar((int) $id, $dto);
            View::redirect('/militares', 'Dados atualizados.');
        } catch (HttpException $e) {
            View::redirect('/militares/' . $id . '/editar', null, $e->getMessage());
        }
    }

    public function atestados(): void
    {
        Auth::requireAdmin();
        View::render('atestados/index', [
            'atestados' => $this->service->listarAtestados(),
            'elegiveis' => $this->service->listarElegiveisAtestado(),
            'pageTitle' => 'Atestados',
        ]);
    }

    public function marcarAtestado(): void
    {
        Auth::requireAdmin();
        try {
            $this->service->definirAtestado(
                (int) ($_POST['usuario_id'] ?? 0),
                true,
                (string) ($_POST['inicio'] ?? ''),
                (int) ($_POST['dias'] ?? 0)
            );
            View::redirect('/atestados', 'Militar colocado de atestado. Ele fica fora da escala até o fim do prazo.');
        } catch (HttpException $e) {
            View::redirect('/atestados', null, $e->getMessage());
        }
    }

    public function tirarAtestado(string $id): void
    {
        Auth::requireAdmin();
        try {
            $this->service->definirAtestado((int) $id, false);
            View::redirect('/atestados', 'Atestado removido. O militar volta para a fila.');
        } catch (HttpException $e) {
            View::redirect('/atestados', null, $e->getMessage());
        }
    }

    public function destroy(string $id): void
    {
        Auth::requireAdmin();
        try {
            $this->service->desativar((int) $id);
            View::redirect('/militares', 'Militar desativado.');
        } catch (HttpException $e) {
            View::redirect('/militares', null, $e->getMessage());
        }
    }
}
