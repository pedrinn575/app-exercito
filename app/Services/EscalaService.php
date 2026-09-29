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
        ?int $qtdAtiradores = null
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

        // Uma escala refeita retoma a mesma posição da fila que já usava
        $inicios = $existente
            ? $this->escalas->inicios($escalaId)
            : $this->escalas->proximosInicios($data);
        $this->escalas->updateInicios($escalaId, $inicios['monitor'], $inicios['atirador']);

        $semFolga = 0;
        $semFolga += $this->preencher($escalaId, 'monitor', $qtdMonitores, $inicios['monitor'], $data);
        $semFolga += $this->preencher($escalaId, 'atirador', $qtdAtiradores, $inicios['atirador'], $data);

        if ($semFolga > 0) {
            $aviso = $semFolga . ' militar(es) entraram sem completar as ' . $this->intervaloHoras . 'h de folga.';
            $obs = $obs ? $obs . ' ' . $aviso : $aviso;
            $this->escalas->updateObservacao($escalaId, $obs);
        }

        $result = $this->escalas->findById($escalaId);
        if (!$result) {
            throw new ValidationException('Falha ao gerar escala.');
        }
        return $result;
    }

    /**
     * Escala a quantidade pedida seguindo a fila a partir de $inicio,
     * dando a volta ao chegar no fim. Devolve quantos entraram sem as 48h.
     */
    private function preencher(
        int $escalaId,
        string $funcao,
        int $quantidade,
        int $inicio,
        string $data
    ): int {
        $fila = $this->fila($funcao);
        $total = count($fila);
        if ($total === 0 || $quantidade === 0) {
            return 0;
        }

        // Ninguém entra duas vezes no mesmo dia
        $quantidade = min($quantidade, $total);
        $semFolga = 0;

        for ($i = 0; $i < $quantidade; $i++) {
            $militar = $fila[($inicio + $i) % $total];
            // Antes de inserir: senão o próprio dia conta como serviço anterior
            if (!$this->temFolga((int) $militar->id, $data)) {
                $semFolga++;
            }
            $this->escalas->addPosto($escalaId, (int) $militar->id, $funcao);
        }

        return $semFolga;
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

    /** A folga de 48h considera qualquer serviço, preto ou vermelho. */
    private function temFolga(int $usuarioId, string $data): bool
    {
        $ultima = $this->escalas->ultimaDataServico($usuarioId);
        if ($ultima === null) {
            return true;
        }
        return abs(strtotime($data) - strtotime($ultima)) / 3600 >= $this->intervaloHoras;
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

            $this->gerar($tipo, $data, $adminId, $qtdMonitores, $qtdAtiradores);
        }

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
        ?int $qtdAtiradores = null
    ): Escala {
        $this->assertTipo($tipo);
        $existente = $this->escalas->findByTipoData($tipo, $data);
        if ($existente) {
            $this->escalas->limparPostos((int) $existente->id);
            $this->escalas->updateObservacao((int) $existente->id, null);
        }

        return $this->gerar($tipo, $data, $adminId, $qtdMonitores, $qtdAtiradores);
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
