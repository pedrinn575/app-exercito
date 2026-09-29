<?php
namespace App\Controllers;

use App\Services\DashboardService;
use App\Services\EscalaService;
use App\Services\FaltaService;
use App\Services\TrocaService;
use App\Utils\Auth;
use App\Utils\View;

final class RelatorioController
{
    public function __construct(
        private DashboardService $dashboard = new DashboardService(),
        private EscalaService $escalas = new EscalaService(),
        private TrocaService $trocas = new TrocaService(),
        private FaltaService $faltas = new FaltaService(),
    ) {}

    public function index(): void
    {
        Auth::requirePermissao('relatorios');
        View::render('relatorios/index', [
            'metricas'  => $this->dashboard->metricas(),
            'pretas'    => $this->escalas->listar('preta'),
            'vermelhas' => $this->escalas->listar('vermelha'),
            'trocas'    => $this->trocas->listar(),
            'faltas'    => $this->faltas->listar(),
            'pageTitle' => 'Relatórios',
        ]);
    }
}
