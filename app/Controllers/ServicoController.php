<?php
namespace App\Controllers;

use App\Services\EscalaService;
use App\Utils\Auth;
use App\Utils\View;

final class ServicoController
{
    public function __construct(
        private EscalaService $escalas = new EscalaService()
    ) {}

    public function index(): void
    {
        $user = Auth::requirePermissao('meus_servicos');
        $hoje = date('Y-m-d');
        $todos = $this->escalas->doUsuario((int) $user['id']);

        $proximos = [];
        $passados = [];
        foreach ($todos as $p) {
            if ($p['data_servico'] >= $hoje && $p['status'] !== 'falta_justificada') {
                $proximos[] = $p;
            } else {
                $passados[] = $p;
            }
        }
        usort($proximos, fn($a, $b) => strcmp($a['data_servico'], $b['data_servico']));

        View::render('servicos/index', [
            'proximos'  => $proximos,
            'passados'  => $passados,
            'pageTitle' => 'Meus serviços',
        ]);
    }
}
