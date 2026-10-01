<?php
namespace App\Controllers;

use App\Services\DashboardService;
use App\Services\EscalaService;
use App\Utils\Auth;
use App\Utils\View;

final class DashboardController
{
    public function __construct(
        private DashboardService $dashboard = new DashboardService(),
        private EscalaService $escalas = new EscalaService(),
    ) {}

    public function index(): void
    {
        Auth::requireLogin();
        $metricas = $this->dashboard->metricas();
        $hoje = $metricas['hoje'];
        $tipoHoje = $this->escalas->tipoSugerido($hoje);
        $escalaHoje = $this->escalas->obter($tipoHoje, $hoje);

        View::render('dashboard/index', [
            'metricas'   => $metricas,
            'escalaHoje' => $escalaHoje,
            'tipoHoje'   => $tipoHoje,
            'pageTitle'  => 'Dashboard',
        ]);
    }
}
