<?php
namespace App\Controllers;

use App\DTOs\TrocaDTO;
use App\Exceptions\HttpException;
use App\Services\EscalaService;
use App\Services\TrocaService;
use App\Services\UsuarioService;
use App\Utils\Auth;
use App\Utils\View;

final class TrocaController
{
    public function __construct(
        private TrocaService $service = new TrocaService(),
        private UsuarioService $usuarios = new UsuarioService(),
        private EscalaService $escalas = new EscalaService(),
    ) {}

    public function index(): void
    {
        $user = Auth::requirePermissao('trocas');
        $lista = Auth::isAdmin()
            ? $this->service->listarParaAdmin()
            : $this->service->doUsuario((int) $user['id']);

        // Postos do usuário logado (próximos serviços) para solicitar troca
        $meusPostos = [];
        foreach (['preta', 'vermelha'] as $tipo) {
            foreach ($this->escalas->listar($tipo) as $esc) {
                foreach ($esc->postos as $p) {
                    if ((int) $p['usuario_id'] === (int) $user['id'] && $p['status'] === 'escalado' && $p['funcao'] !== 'reserva') {
                        $meusPostos[] = [
                            'id' => $p['id'],
                            'label' => strtoupper($esc->tipo) . ' · ' . date('d/m/Y', strtotime($esc->dataServico)) . ' · ' . $p['funcao'],
                        ];
                    }
                }
            }
        }

        View::render('trocas/index', [
            'trocas'     => $lista,
            'militares'  => $this->usuarios->listar(),
            'meusPostos' => $meusPostos,
            'pageTitle'  => 'Trocas de serviço',
        ]);
    }

    public function store(): void
    {
        $user = Auth::requireLogin();
        try {
            $dto = TrocaDTO::fromRequest($_POST, (int) $user['id']);
            $this->service->solicitar($dto);
            View::redirect('/trocas', 'Pedido enviado ao militar. Ele precisa aceitar ou recusar.');
        } catch (HttpException $e) {
            View::redirect('/trocas', null, $e->getMessage());
        }
    }

    public function aceitar(string $id): void
    {
        $user = Auth::requireLogin();
        try {
            $this->service->aceitarDestino((int) $id, (int) $user['id']);
            View::redirect('/trocas', 'Você aceitou a troca. Aguardando o Subtenente.');
        } catch (HttpException $e) {
            View::redirect('/trocas', null, $e->getMessage());
        }
    }

    public function aprovar(string $id): void
    {
        Auth::requireAdmin();
        try {
            $this->service->aprovarAdmin((int) $id);
            View::redirect('/trocas', 'Troca aprovada e escala atualizada.');
        } catch (HttpException $e) {
            View::redirect('/trocas', null, $e->getMessage());
        }
    }

    public function recusar(string $id): void
    {
        $user = Auth::requireLogin();
        try {
            $this->service->recusar((int) $id, (int) $user['id'], Auth::isAdmin());
            View::redirect('/trocas', 'Troca recusada.');
        } catch (HttpException $e) {
            View::redirect('/trocas', null, $e->getMessage());
        }
    }
}
