<?php
namespace App\Controllers;

use App\DTOs\MarmitaDTO;
use App\Exceptions\HttpException;
use App\Services\MarmitaService;
use App\Utils\Auth;
use App\Utils\View;

final class MarmitaController
{
    public function __construct(
        private MarmitaService $service = new MarmitaService()
    ) {}

    public function index(): void
    {
        $user = Auth::requirePermissao('marmitas');
        $admin = Auth::isAdmin();
        $lista = $admin
            ? $this->service->listar()
            : $this->service->listar((int) $user['id']);

        View::render('marmitas/index', [
            'pedidos'   => $lista,
            'dataPref'  => $_GET['data'] ?? date('Y-m-d'),
            'admin'     => $admin,
            'pageTitle' => 'Marmitas',
        ]);
    }

    public function imprimir(): void
    {
        Auth::requireAdmin();
        try {
            $inicio = (string) ($_GET['inicio'] ?? date('Y-m-d'));
            $dias = (int) ($_GET['dias'] ?? 7);
            $folha = $this->service->paraImpressao($inicio, $dias);
            View::render('marmitas/imprimir', [
                'folha'     => $folha,
                'pageTitle' => 'Marmitas',
            ], 'layouts/print');
        } catch (HttpException $e) {
            View::redirect('/marmitas', null, $e->getMessage());
        }
    }

    public function store(): void
    {
        $user = Auth::requireLogin();
        if (Auth::isAdmin()) {
            View::redirect('/marmitas', null, 'O administrador apenas decide os pedidos.');
        }
        try {
            $dto = MarmitaDTO::fromRequest($_POST);
            $this->service->solicitar((int) $user['id'], $dto);
            View::redirect('/marmitas', 'Pedido enviado. Aguarde a decisão do Subtenente.');
        } catch (HttpException $e) {
            View::redirect('/marmitas', null, $e->getMessage());
        }
    }

    public function atualizar(string $id): void
    {
        $user = Auth::requireLogin();
        try {
            $dto = MarmitaDTO::fromRequest($_POST);
            $this->service->reenviar((int) $id, (int) $user['id'], $dto);
            View::redirect('/marmitas', 'Pedido reenviado para análise.');
        } catch (HttpException $e) {
            View::redirect('/marmitas', null, $e->getMessage());
        }
    }

    public function aprovar(string $id): void
    {
        Auth::requireAdmin();
        try {
            $this->service->aprovar((int) $id);
            View::redirect('/marmitas', 'Pedido aprovado.');
        } catch (HttpException $e) {
            View::redirect('/marmitas', null, $e->getMessage());
        }
    }

    public function negar(string $id): void
    {
        Auth::requireAdmin();
        try {
            $this->service->negar((int) $id, $_POST['retorno'] ?? null);
            View::redirect('/marmitas', 'Pedido negado.');
        } catch (HttpException $e) {
            View::redirect('/marmitas', null, $e->getMessage());
        }
    }

    public function pedirAlteracao(string $id): void
    {
        Auth::requireAdmin();
        try {
            $this->service->pedirAlteracao((int) $id, $_POST['retorno'] ?? null);
            View::redirect('/marmitas', 'Alteração solicitada ao militar.');
        } catch (HttpException $e) {
            View::redirect('/marmitas', null, $e->getMessage());
        }
    }

    public function cancelar(string $id): void
    {
        $user = Auth::requireLogin();
        try {
            $this->service->cancelar((int) $id, (int) $user['id'], Auth::isAdmin());
            View::redirect('/marmitas', 'Pedido cancelado.');
        } catch (HttpException $e) {
            View::redirect('/marmitas', null, $e->getMessage());
        }
    }
}
