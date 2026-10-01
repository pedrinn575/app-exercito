<?php
/**
 * EscalaService — geração e consulta de escalas preta e vermelha.
 *
 * Regras:
 * - 11 pessoas por dia: 2 monitores e 9 atiradores
 * - Vermelha: fins de semana/feriados, 24h
 * - Rotação sequencial: atiradores pelo número de atirador, do primeiro ao último.
 *   Monitores pelo número de monitor, do último ao primeiro.
 * - Intervalo mínimo de 48h entre serviços
 */

namespace App\Services;

use App\Entities\Escala;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\EscalaRepository;
use App\Repositories\FeriadoRepository;
use App\Repositories\UsuarioRepository;
use App\Utils\Calendar;

final class EscalaService
{
    private int $intervaloHoras;
    private int $vagasPorDia;
    private int $monitoresPorDia;

    public function __construct(
        private EscalaRepository $escalas = new EscalaRepository(),
        private UsuarioRepository $usuarios = new UsuarioRepository(),
        private FeriadoRepository $feriados = new FeriadoRepository(),
    ) {
        $cfg = require APP_PATH . '/Config/app.php';
        $this->intervaloHoras = (int) $cfg['intervalo_horas'];
        $this->vagasPorDia = (int) $cfg['vagas_por_dia'];
        $this->monitoresPorDia = (int) $cfg['monitores_por_dia'];
    }

    public function obter(string $tipo, string $data): ?Escala
    {
        $this->assertTipo($tipo);
        return $this->escalas->findByTipoData($tipo, $data);
    }

    /** @return list<Escala> */
    public function listar(string $tipo): array
    {
        $this->assertTipo($tipo);
        return $this->escalas->listarPorTipo($tipo);
    }

    /**
     * Quantidades padrão de monitores e atiradores por dia.
     *
     * @return array{monitores:int,atiradores:int}
     */
    public function efetivoPadrao(): array
    {
        return [
            'monitores'  => $this->monitoresPorDia,
            'atiradores' => max(0, $this->vagasPorDia - $this->monitoresPorDia),
        ];
    }

    /**
     * Gera escala para uma data (ou recria se vazia), seguindo a fila
     * sequencial de onde a véspera parou.
     * Sem os parâmetros de efetivo, vale o padrão da configuração.
     */
    public function gerar(
        string $tipo,
        string $data,
        int $adminId,
        ?int $qtdMonitores = null,
        ?int $qtdAtiradores = null,
        bool $propagar = true
    ): Escala {
        $this->assertTipo($tipo);

        $padrao = $this->efetivoPadrao();
        $qtdMonitores = $this->normalizarQuantidade($qtdMonitores ?? $padrao['monitores'], 'monitores');
        $qtdAtiradores = $this->normalizarQuantidade($qtdAtiradores ?? $padrao['atiradores'], 'atiradores');

        if ($qtdMonitores + $qtdAtiradores === 0) {
            throw new ValidationException('A escala precisa de pelo menos uma pessoa.');
        }

        $existente = $this->escalas->findByTipoData($tipo, $data);
        if ($existente && count($existente->postos) > 0) {
            throw new ValidationException('Já existe escala ' . $tipo . ' para ' . Calendar::formatBr($data) . '.');
        }

        // Tipicamente vermelha = fim de semana/feriado; preta = dia útil
        $deveriaSerVermelha = Calendar::isEscalaVermelha($data);
        if ($tipo === 'vermelha' && !$deveriaSerVermelha) {
            // Permite forçar, mas avisa via obs
            $obs = 'Gerada manualmente (dia útil marcado como vermelha).';
        } elseif ($tipo === 'preta' && $deveriaSerVermelha) {
            $obs = 'Gerada manualmente (fim de semana/feriado marcado como preta).';
        } else {
            $obs = null;
        }

        if ($existente) {
            $escalaId = (int) $existente->id;
        } else {
            $escalaId = $this->escalas->create($tipo, $data, $adminId, $obs);
        }

        // Continua depois da véspera. Refazer também recalcula, senão o dia
        // seguinte fica com a mesma turma.
        $inicios = $this->proximosInicios($data, $escalaId);
        $monitores = $this->preencher($escalaId, 'monitor', $qtdMonitores, $inicios['monitor'], $data);
        $atiradores = $this->preencher($escalaId, 'atirador', $qtdAtiradores, $inicios['atirador'], $data);
        $this->escalas->updateInicios($escalaId, $monitores['inicio'], $atiradores['inicio']);
        $semFolga = $monitores['semFolga'] + $atiradores['semFolga'];

        if ($semFolga > 0) {
            $aviso = $semFolga . ' militar(es) entraram sem completar as ' . $this->intervaloHoras . 'h de folga.';
            $obs = $obs ? $obs . ' ' . $aviso : $aviso;
            $this->escalas->updateObservacao($escalaId, $obs);
        }

        if ($propagar) {
            $this->reposicionarSeguintes($data, $adminId);
        }

        $result = $this->escalas->findById($escalaId);
        if (!$result) {
            throw new ValidationException('Falha ao gerar escala.');
        }
        return $result;
    }

    /**
     * Onde a fila deve começar neste dia: logo depois da última escala anterior.
     * Preta e vermelha do mesmo dia contam as duas.
     *
     * @return array{monitor:int,atirador:int}
     */
    private function proximosInicios(string $data, ?int $excetoId = null): array
    {
        $cursor = ['monitor' => 0, 'atirador' => 0];
        $dataCursor = ['monitor' => null, 'atirador' => null];

        foreach ($this->escalas->escalasAnteriores($data, $excetoId) as $row) {
            foreach (['monitor', 'atirador'] as $funcao) {
                $usados = (int) $row['usados_' . $funcao];
                if ($usados === 0) {
                    continue;
                }
                $fim = (int) $row['inicio_' . $funcao] + $usados;
                $quando = $row['data_servico'];
                if ($dataCursor[$funcao] === null || $quando > $dataCursor[$funcao] || ($quando === $dataCursor[$funcao] && $fim > $cursor[$funcao])) {
                    $dataCursor[$funcao] = $quando;
                    $cursor[$funcao] = $fim;
                }
            }
        }

        return $cursor;
    }

    /**
     * Escala a quantidade pedida a partir de $inicio.
     * Quem não completou a folga é pulado e entra o próximo da fila.
     * Só força a entrada se não houver gente suficiente com folga.
     *
     * @return array{semFolga:int,inicio:int}
     */
    private function preencher(
        int $escalaId,
        string $funcao,
        int $quantidade,
        int $inicio,
        string $data
    ): array {
        $fila = $this->fila($funcao);
        $total = count($fila);
        if ($total === 0 || $quantidade === 0) {
            return ['semFolga' => 0, 'inicio' => $inicio];
        }

        $quantidade = min($quantidade, $total);
        $escolhidos = [];
        $semFolgaNaFila = [];

        for ($i = 0; $i < $total && count($escolhidos) < $quantidade; $i++) {
            $abs = $inicio + $i;
            $militar = $fila[$abs % $total];
            $id = (int) $militar->id;
            if (isset($escolhidos[$id]) || isset($semFolgaNaFila[$id])) {
                continue;
            }
            if ($this->escalas->servicoDentroDoIntervalo($id, $data, $this->intervaloHoras, $escalaId)) {
                $semFolgaNaFila[$id] = $abs;
                continue;
            }
            $escolhidos[$id] = $abs;
        }

        $semFolga = 0;
        foreach ($semFolgaNaFila as $id => $abs) {
            if (count($escolhidos) >= $quantidade) {
                break;
            }
            $escolhidos[$id] = $abs;
            $semFolga++;
        }

        if ($escolhidos === []) {
            return ['semFolga' => 0, 'inicio' => $inicio];
        }

        $porIndice = [];
        foreach ($escolhidos as $id => $abs) {
            $porIndice[$abs] = $id;
        }
        ksort($porIndice);
        foreach ($porIndice as $id) {
            $this->escalas->addPosto($escalaId, $id, $funcao);
        }

        $ultimo = (int) array_key_last($porIndice);
        $colocados = count($porIndice);

        return [
            'semFolga' => $semFolga,
            'inicio'   => $ultimo + 1 - $colocados,
        ];
    }

    /**
     * Fila da função: atiradores pelo número de atirador, do primeiro ao último.
     * Monitores pelo número de monitor, do último ao primeiro.
     *
     * @return list<\App\Entities\Usuario>
     */
    private function fila(string $funcao): array
    {
        return $this->usuarios->filaDaFuncao($funcao, $funcao === 'monitor');
    }

    /**
     * Dias seguintes que repetem gente sem folga, ou que não continuam a fila,
     * são refeitos na ordem do calendário.
     */
    private function reposicionarSeguintes(string $data, int $adminId): void
    {
        foreach ($this->escalas->datasComEscalaDepois($data) as $dia) {
            foreach (['preta', 'vermelha'] as $tipo) {
                $esc = $this->escalas->findByTipoData($tipo, $dia);
                if (!$esc || $esc->postos === []) {
                    continue;
                }
                if (!$this->diaPrecisaReposicionar($esc, $dia)) {
                    continue;
                }

                $monitores = 0;
                $atiradores = 0;
                $reservas = [];
                foreach ($esc->postos as $posto) {
                    if ($posto['funcao'] === 'monitor') {
                        $monitores++;
                    } elseif ($posto['funcao'] === 'atirador') {
                        $atiradores++;
                    } elseif ($posto['funcao'] === 'reserva') {
                        $reservas[] = (int) $posto['usuario_id'];
                    }
                }

                $this->regerar($tipo, $dia, $adminId, $monitores, $atiradores, false);
                $refeita = $this->escalas->findByTipoData($tipo, $dia);
                if (!$refeita) {
                    continue;
                }
                foreach ($reservas as $usuarioId) {
                    if ($this->escalas->findPostoNaEscala((int) $refeita->id, $usuarioId)) {
                        continue;
                    }
                    $this->escalas->addPosto((int) $refeita->id, $usuarioId, 'reserva');
                }
            }
        }
    }

    private function diaPrecisaReposicionar(Escala $escala, string $data): bool
    {
        $esperado = $this->proximosInicios($data, (int) $escala->id);
        $atual = $this->escalas->inicios((int) $escala->id);
        if ($atual['monitor'] !== $esperado['monitor'] || $atual['atirador'] !== $esperado['atirador']) {
            return true;
        }

        foreach ($escala->postos as $posto) {
            if ($posto['funcao'] === 'reserva') {
                continue;
            }
            if ($this->escalas->servicoDentroDoIntervalo((int) $posto['usuario_id'], $data, $this->intervaloHoras, (int) $escala->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dia útil → preta. Fim de semana, feriado nacional ou feriado cadastrado → vermelha.
     */
    public function tipoSugerido(string $data): string
    {
        if (Calendar::isEscalaVermelha($data) || $this->feriados->exists($data)) {
            return 'vermelha';
        }
        return 'preta';
    }

    /**
     * Gera o mês inteiro: cada dia recebe a escala sugerida (preta ou vermelha).
     * Com $substituir, as escalas já existentes do mês são refeitas.
     *
     * @return array{criadas:int,puladas:int,refeitas:int}
     */
    public function gerarMes(
        string $anoMes,
        int $adminId,
        ?int $qtdMonitores = null,
        ?int $qtdAtiradores = null,
        bool $substituir = false
    ): array {
        if (!preg_match('/^\d{4}-\d{2}$/', $anoMes)) {
            throw new ValidationException('Mês inválido.');
        }

        $inicio = $anoMes . '-01';
        $fim = date('Y-m-t', strtotime($inicio));
        $criadas = 0;
        $puladas = 0;
        $refeitas = 0;

        for ($data = $inicio; $data <= $fim; $data = date('Y-m-d', strtotime($data . ' +1 day'))) {
            $tipo = $this->tipoSugerido($data);
            $existente = $this->escalas->findByTipoData($tipo, $data);

            if ($existente && count($existente->postos) > 0) {
                if (!$substituir) {
                    $puladas++;
                    continue;
                }
                $this->escalas->limparPostos((int) $existente->id);
                $refeitas++;
            } else {
                $criadas++;
            }

            $this->gerar($tipo, $data, $adminId, $qtdMonitores, $qtdAtiradores, false);
        }

        $this->reposicionarSeguintes($inicio, $adminId);

        return ['criadas' => $criadas, 'puladas' => $puladas, 'refeitas' => $refeitas];
    }

    /**
     * Refaz uma escala já existente com o efetivo informado.
     */
    public function regerar(
        string $tipo,
        string $data,
        int $adminId,
        ?int $qtdMonitores = null,
        ?int $qtdAtiradores = null,
        bool $propagar = true
    ): Escala {
        $this->assertTipo($tipo);
        $existente = $this->escalas->findByTipoData($tipo, $data);
        if ($existente) {
            $this->escalas->limparPostos((int) $existente->id);
            $this->escalas->updateObservacao((int) $existente->id, null);
        }

        return $this->gerar($tipo, $data, $adminId, $qtdMonitores, $qtdAtiradores, $propagar);
    }

    /**
     * Inclui um militar na escala, em qualquer função.
     */
    public function adicionarMilitar(int $escalaId, int $usuarioId, string $funcao): void
    {
        $escala = $this->escalas->findById($escalaId);
        if (!$escala) {
            throw new NotFoundException('Escala não encontrada.');
        }
        $this->assertFuncao($funcao);

        if ($this->escalas->findPostoNaEscala($escalaId, $usuarioId)) {
            throw new ValidationException('Este militar já está nesta escala.');
        }

        $usuario = $this->usuarios->findById($usuarioId);
        if (!$usuario) {
            throw new NotFoundException('Militar não encontrado.');
        }

        $this->escalas->addPosto($escalaId, $usuarioId, $funcao);
    }

    /**
     * Troca a função de quem já está escalado (monitor, atirador ou reserva).
     */
    public function alterarFuncao(int $postoId, string $funcao): void
    {
        $posto = $this->escalas->findPosto($postoId);
        if (!$posto) {
            throw new NotFoundException('Posto não encontrado.');
        }
        $this->assertFuncao($funcao);

        $this->escalas->updatePostoFuncaoStatus($postoId, $funcao, $posto['status']);
    }

    public function removerMilitar(int $postoId): array
    {
        $posto = $this->escalas->findPosto($postoId);
        if (!$posto) {
            throw new NotFoundException('Posto não encontrado.');
        }
        $this->escalas->deletePosto($postoId);
        return $posto;
    }

    public function excluir(string $tipo, string $data): void
    {
        $this->assertTipo($tipo);
        $escala = $this->escalas->findByTipoData($tipo, $data);
        if (!$escala) {
            throw new NotFoundException('Escala não encontrada.');
        }
        $this->escalas->deleteEscala((int) $escala->id);
    }

    private function assertFuncao(string $funcao): void
    {
        if (!in_array($funcao, ['monitor', 'atirador', 'reserva'], true)) {
            throw new ValidationException('Função inválida.');
        }
    }

    private function normalizarQuantidade(int $valor, string $campo): int
    {
        if ($valor < 0 || $valor > 150) {
            throw new ValidationException('Quantidade de ' . $campo . ' deve ficar entre 0 e 150.');
        }
        return $valor;
    }

    /**
     * @return array<string, list<array{tipo:string,total:int}>>
     */
    public function mapaMes(string $anoMes): array
    {
        $mapa = [];
        foreach ($this->escalas->resumoMes($anoMes) as $row) {
            $mapa[$row['data_servico']][] = [
                'tipo'  => $row['tipo'],
                'total' => (int) $row['total'],
            ];
        }
        return $mapa;
    }

    /**
     * @return array<string, array{tipo:string,funcao:string}>
     */
    public function diasDoUsuarioNoMes(int $usuarioId, string $anoMes): array
    {
        $dias = [];
        foreach ($this->escalas->diasDoUsuarioNoMes($usuarioId, $anoMes) as $row) {
            $data = $row['data_servico'];
            if (!isset($dias[$data])) {
                $dias[$data] = [
                    'tipo'   => $row['tipo'],
                    'funcao' => $row['funcao'],
                ];
            }
        }
        return $dias;
    }

    /** @return list<array<string,mixed>> */
    public function doUsuario(int $usuarioId): array
    {
        return $this->escalas->postosDoUsuario($usuarioId);
    }

    private function assertTipo(string $tipo): void
    {
        if (!in_array($tipo, ['preta', 'vermelha'], true)) {
            throw new ValidationException('Tipo de escala inválido.');
        }
    }
}
