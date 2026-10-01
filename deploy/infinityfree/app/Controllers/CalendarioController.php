<?php
namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use App\Repositories\FeriadoRepository;
use App\Services\EscalaService;
use App\Utils\Auth;
use App\Utils\Calendar;
use App\Utils\View;

final class CalendarioController
{
    public function __construct(
        private EscalaService $escalas = new EscalaService(),
        private FeriadoRepository $feriados = new FeriadoRepository(),
    ) {}

    public function index(): void
    {
        Auth::requirePermissao('calendario');
        $mes = $_GET['mes'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = date('Y-m');
        }

        $feriados = $this->feriados->listar();
        $extras = [];
        foreach ($feriados as $f) {
            $extras[$f['data']] = true;
        }

        $tipos = [];
        $dias = (int) date('t', strtotime($mes . '-01'));
        for ($d = 1; $d <= $dias; $d++) {
            $data = sprintf('%s-%02d', $mes, $d);
            $tipos[$data] = (Calendar::isEscalaVermelha($data) || isset($extras[$data])) ? 'vermelha' : 'preta';
        }

        View::render('calendario/index', [
            'mes'       => $mes,
            'mapa'      => $this->escalas->mapaMes($mes),
            'feriados'  => $feriados,
            'tipos'     => $tipos,
            'efetivo'   => $this->escalas->efetivoPadrao(),
            'pageTitle' => 'Calendário',
        ]);
    }

    public function gerarMes(): void
    {
        $user = Auth::requireAdmin();
        $mes = $_POST['mes'] ?? date('Y-m');
        try {
            $monitores = ($_POST['monitores'] ?? '') !== '' ? (int) $_POST['monitores'] : null;
            $atiradores = ($_POST['atiradores'] ?? '') !== '' ? (int) $_POST['atiradores'] : null;
            $substituir = !empty($_POST['substituir']);

            $r = $this->escalas->gerarMes($mes, (int) $user['id'], $monitores, $atiradores, $substituir);

            $msg = $r['criadas'] . ' escala(s) gerada(s)';
            if ($r['refeitas'] > 0) {
                $msg .= ', ' . $r['refeitas'] . ' refeita(s)';
            }
            if ($r['puladas'] > 0) {
                $msg .= ', ' . $r['puladas'] . ' dia(s) mantido(s)';
            }

            View::redirect('/calendario?mes=' . urlencode($mes), $msg . '.');
        } catch (HttpException $e) {
            View::redirect('/calendario?mes=' . urlencode($mes), null, $e->getMessage());
        }
    }

    public function salvarFeriado(): void
    {
        Auth::requireAdmin();
        $data = trim((string) ($_POST['data'] ?? ''));
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $mes = preg_match('/^\d{4}-\d{2}/', $data) ? substr($data, 0, 7) : date('Y-m');

        try {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || $nome === '') {
                throw new ValidationException('Informe a data e o nome do feriado.');
            }
            if ($this->feriados->exists($data)) {
                throw new ValidationException('Este dia já está cadastrado como feriado.');
            }
            $this->feriados->create($data, $nome);
            View::redirect('/calendario?mes=' . urlencode($mes), 'Feriado cadastrado. Esse dia entra na escala vermelha.');
        } catch (HttpException $e) {
            View::redirect('/calendario?mes=' . urlencode($mes), null, $e->getMessage());
        }
    }

    public function removerFeriado(string $id): void
    {
        Auth::requireAdmin();
        $this->feriados->delete((int) $id);
        $mes = $_POST['mes'] ?? date('Y-m');
        View::redirect('/calendario?mes=' . urlencode($mes), 'Feriado removido.');
    }
}
