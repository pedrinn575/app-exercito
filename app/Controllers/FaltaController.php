<?php
namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Services\FaltaService;
use App\Services\UsuarioService;
use App\Utils\Auth;
use App\Utils\View;

final class FaltaController
{
    public function __construct(
        private FaltaService $service = new FaltaService(),
        private UsuarioService $usuarios = new UsuarioService(),
    ) {}

    public function index(): void
    {
        Auth::requirePermissao('faltas');
        View::render('faltas/index', [
            'faltas'    => $this->service->listar(),
            'militares' => $this->usuarios->listar(),
            'pageTitle' => 'Controle de faltas',
        ]);
    }

    public function justificada(): void
    {
        $admin = Auth::requireAdmin();
        try {
            $this->service->registrarJustificada(
                (int) ($_POST['posto_id'] ?? 0),
                (int) $admin['id'],
                $_POST['observacao'] ?? null
            );
            $back = $_POST['redirect'] ?? '/faltas';
            View::redirect($back, 'Falta justificada registrada. Nenhum substituto foi puxado.');
        } catch (HttpException $e) {
            View::redirect($_POST['redirect'] ?? '/faltas', null, $e->getMessage());
        }
    }

    public function injustificada(): void
    {
        $admin = Auth::requireAdmin();
        try {
            $sub = !empty($_POST['substituto_id']) ? (int) $_POST['substituto_id'] : null;
            $this->service->registrarInjustificada(
                (int) ($_POST['posto_id'] ?? 0),
                (int) $admin['id'],
                $sub,
                $_POST['observacao'] ?? null
            );
            $back = $_POST['redirect'] ?? '/faltas';
            View::redirect($back, 'Falta injustificada registrada. Nome marcado em vermelho.');
        } catch (HttpException $e) {
            View::redirect($_POST['redirect'] ?? '/faltas', null, $e->getMessage());
        }
    }
}
