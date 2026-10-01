<?php
namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Services\EscalaService;
use App\Services\UsuarioService;
use App\Utils\Auth;
use App\Utils\Calendar;
use App\Utils\View;

final class EscalaController
{
    public function __construct(
        private EscalaService $service = new EscalaService(),
        private UsuarioService $usuarios = new UsuarioService(),
    ) {}

    public function preta(): void
    {
        $this->mostrar('preta', 'Escala Preta');
    }

    public function vermelha(): void
    {
        $this->mostrar('vermelha', 'Escala Vermelha');
    }

    public function show(string $tipo, string $data): void
    {
        Auth::requireLogin();
        View::redirect('/escalas/' . $tipo . '?data=' . urlencode($data));
    }

    public function gerar(): void
    {
        $user = Auth::requireAdmin();
        $tipo = $_POST['tipo'] ?? 'preta';
        $data = $_POST['data'] ?? date('Y-m-d');
        $destino = '/escalas/' . $tipo . '?data=' . urlencode($data);

        try {
            [$monitores, $atiradores] = $this->efetivoDoPost();
            $refazer = !empty($_POST['refazer']);

            if ($refazer) {
                $this->service->regerar($tipo, $data, (int) $user['id'], $monitores, $atiradores);
                $msg = 'Escala ' . $tipo . ' refeita para ' . Calendar::formatBr($data) . '.';
            } else {
                $this->service->gerar($tipo, $data, (int) $user['id'], $monitores, $atiradores);
                $msg = 'Escala ' . $tipo . ' gerada para ' . Calendar::formatBr($data) . '.';
            }

            View::redirect($destino, $msg);
        } catch (HttpException $e) {
            View::redirect($destino, null, $e->getMessage());
        }
    }

    public function adicionarPosto(string $id): void
    {
        Auth::requireAdmin();
        $destino = $this->destinoDoPost();
        try {
            $this->service->adicionarMilitar(
                (int) $id,
                (int) ($_POST['usuario_id'] ?? 0),
                $_POST['funcao'] ?? 'atirador'
            );
            View::redirect($destino, 'Militar incluído na escala.');
        } catch (HttpException $e) {
            View::redirect($destino, null, $e->getMessage());
        }
    }

    public function alterarFuncao(string $id): void
    {
        Auth::requireAdmin();
        $destino = $this->destinoDoPost();
        try {
            $this->service->alterarFuncao((int) $id, $_POST['funcao'] ?? 'atirador');
            View::redirect($destino, 'Função atualizada.');
        } catch (HttpException $e) {
            View::redirect($destino, null, $e->getMessage());
        }
    }

    public function removerPosto(string $id): void
    {
        Auth::requireAdmin();
        $destino = $this->destinoDoPost();
        try {
            $posto = $this->service->removerMilitar((int) $id);
            View::redirect($destino, $posto['nome'] . ' saiu da escala.');
        } catch (HttpException $e) {
            View::redirect($destino, null, $e->getMessage());
        }
    }

    public function excluir(): void
    {
        Auth::requireAdmin();
        $tipo = $_POST['tipo'] ?? 'preta';
        $data = $_POST['data'] ?? date('Y-m-d');
        $destino = '/escalas/' . $tipo . '?data=' . urlencode($data);
        try {
            $this->service->excluir($tipo, $data);
            View::redirect($destino, 'Escala apagada.');
        } catch (HttpException $e) {
            View::redirect($destino, null, $e->getMessage());
        }
    }

    private function mostrar(string $tipo, string $titulo): void
    {
        // Quem enxerga o calendário chega às escalas pelos dias do mês
        if (!Auth::pode('escala_' . $tipo)) {
            Auth::requirePermissao('calendario');
        }
        Auth::requireLogin();

        $data = $_GET['data'] ?? date('Y-m-d');
        $atual = $this->service->obter($tipo, $data);

        View::render('escalas/lista', [
            'tipo'      => $tipo,
            'escalas'   => $this->service->listar($tipo),
            'atual'     => $atual,
            'data'      => $data,
            'militares' => $this->usuarios->listar(),
            'efetivo'   => $this->service->efetivoPadrao(),
            'pageTitle' => $titulo,
        ]);
    }

    /** @return array{0:?int,1:?int} */
    private function efetivoDoPost(): array
    {
        $monitores = isset($_POST['monitores']) && $_POST['monitores'] !== ''
            ? (int) $_POST['monitores'] : null;
        $atiradores = isset($_POST['atiradores']) && $_POST['atiradores'] !== ''
            ? (int) $_POST['atiradores'] : null;

        return [$monitores, $atiradores];
    }

    private function destinoDoPost(): string
    {
        $tipo = $_POST['tipo'] ?? 'preta';
        $data = $_POST['data'] ?? date('Y-m-d');
        return '/escalas/' . $tipo . '?data=' . urlencode($data);
    }
}
