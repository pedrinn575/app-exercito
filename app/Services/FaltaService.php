<?php
/**
 * FaltaService — falta justificada / injustificada + substituição.
 *
 * Injustificada: nome em vermelho + botão para puxar reserva/substituto.
 * Justificada: nenhum atirador puxado; faltante não é prejudicado.
 */

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\EscalaRepository;
use App\Repositories\FaltaRepository;

final class FaltaService
{
    public function __construct(
        private FaltaRepository $faltas = new FaltaRepository(),
        private EscalaRepository $escalas = new EscalaRepository(),
    ) {}

    public function registrarJustificada(int $postoId, int $adminId, ?string $obs = null): void
    {
        $posto = $this->requirePosto($postoId);
        if ($posto['status'] !== 'escalado') {
            throw new ValidationException('Posto já tratado.');
        }

        $this->escalas->updatePostoStatus($postoId, 'falta_justificada');
        $this->faltas->registrar($postoId, 'justificada', null, $adminId, $obs);
    }

    public function registrarInjustificada(
        int $postoId,
        int $adminId,
        ?int $substitutoId,
        ?string $obs = null
    ): void {
        $posto = $this->requirePosto($postoId);
        if ($posto['status'] !== 'escalado') {
            throw new ValidationException('Posto já tratado.');
        }

        $this->escalas->updatePostoStatus($postoId, 'falta_injustificada');

        if ($substitutoId) {
            $escalaId = (int) $posto['escala_id'];
            $existente = $this->escalas->findPostoNaEscala($escalaId, $substitutoId);
            if ($existente) {
                // Reserva (ou outro posto) sobe para a função do faltante
                $this->escalas->updatePostoFuncaoStatus((int) $existente['id'], $posto['funcao'], 'escalado');
            } else {
                $this->escalas->addPosto($escalaId, $substitutoId, $posto['funcao']);
            }
        }

        $this->faltas->registrar($postoId, 'injustificada', $substitutoId, $adminId, $obs);
    }

    /** @return list<array<string,mixed>> */
    public function listar(): array
    {
        return $this->faltas->listar();
    }

    /**
     * Presença é o serviço de um dia que já passou e não teve falta.
     *
     * @return array{
     *   presencas:int,justificadas:int,injustificadas:int,servicos:int,
     *   dias:list<array{data:string,presencas:int,faltas:int}>,
     *   atiradores:list<array{numero:string,nome:string,presencas:int,justificadas:int,injustificadas:int}>
     * }
     */
    public function painelAtiradores(?string $ate = null): array
    {
        $ate = $ate ?? date('Y-m-d');
        $presencas = 0;
        $justificadas = 0;
        $injustificadas = 0;
        $dias = [];
        $pessoas = [];

        foreach ($this->faltas->servicosPassadosAtirador($ate) as $row) {
            $resultado = $row['resultado'];
            if ($resultado === 'injustificada') {
                $injustificadas++;
            } elseif ($resultado === 'justificada') {
                $justificadas++;
            } else {
                $presencas++;
            }

            $data = $row['data_servico'];
            if (!isset($dias[$data])) {
                $dias[$data] = ['data' => $data, 'presencas' => 0, 'faltas' => 0];
            }
            if ($resultado === 'presenca') {
                $dias[$data]['presencas']++;
            } else {
                $dias[$data]['faltas']++;
            }

            $id = (int) $row['id'];
            if (!isset($pessoas[$id])) {
                $pessoas[$id] = [
                    'numero' => $row['numero'],
                    'nome' => $row['nome'],
                    'presencas' => 0,
                    'justificadas' => 0,
                    'injustificadas' => 0,
                ];
            }
            if ($resultado === 'injustificada') {
                $pessoas[$id]['injustificadas']++;
            } elseif ($resultado === 'justificada') {
                $pessoas[$id]['justificadas']++;
            } else {
                $pessoas[$id]['presencas']++;
            }
        }

        $atiradores = array_values($pessoas);
        usort($atiradores, function (array $a, array $b): int {
            $fa = $a['justificadas'] + $a['injustificadas'];
            $fb = $b['justificadas'] + $b['injustificadas'];
            return $fb <=> $fa ?: ((int) $a['numero'] <=> (int) $b['numero']);
        });

        return [
            'presencas' => $presencas,
            'justificadas' => $justificadas,
            'injustificadas' => $injustificadas,
            'servicos' => $presencas + $justificadas + $injustificadas,
            'dias' => array_values($dias),
            'atiradores' => $atiradores,
        ];
    }

    private function requirePosto(int $id): array
    {
        $p = $this->escalas->findPosto($id);
        if (!$p) {
            throw new NotFoundException('Posto não encontrado.');
        }
        // precisa do escala_id — findPosto já traz via join, mas escala_id está em ep.*
        return $p;
    }
}
