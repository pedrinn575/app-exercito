<?php
/**
 * DashboardService — métricas agregadas.
 */

namespace App\Services;

use App\Repositories\EscalaRepository;
use App\Repositories\FaltaRepository;
use App\Repositories\MarmitaRepository;
use App\Repositories\TrocaRepository;
use App\Repositories\UsuarioRepository;

final class DashboardService
{
    public function __construct(
        private UsuarioRepository $usuarios = new UsuarioRepository(),
        private EscalaRepository $escalas = new EscalaRepository(),
        private TrocaRepository $trocas = new TrocaRepository(),
        private FaltaRepository $faltas = new FaltaRepository(),
        private MarmitaRepository $marmitas = new MarmitaRepository(),
    ) {}

    /** @return array<string,mixed> */
    public function metricas(): array
    {
        $mes = date('Y-m');
        return [
            'militares'            => $this->usuarios->countAtivos(),
            'escalas_preta_mes'    => $this->escalas->countMes('preta', $mes),
            'escalas_vermelha_mes' => $this->escalas->countMes('vermelha', $mes),
            'trocas_pendentes'     => $this->trocas->countPendentes(),
            'faltas_injustificadas'=> $this->faltas->countInjustificadasMes($mes),
            'marmitas_pendentes'   => $this->marmitas->countPendentes(),
            'hoje'                 => date('Y-m-d'),
        ];
    }
}
