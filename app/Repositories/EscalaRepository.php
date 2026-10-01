<?php
/**
 * Repository de Escalas e postos.
 */

namespace App\Repositories;

use App\Config\Database;
use App\Entities\Escala;
use PDO;

final class EscalaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByTipoData(string $tipo, string $data): ?Escala
    {
        $stmt = $this->db->prepare('SELECT * FROM escalas WHERE tipo = ? AND data_servico = ? LIMIT 1');
        $stmt->execute([$tipo, $data]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $escala = Escala::fromRow($row);
        $escala->postos = $this->postosDaEscala((int) $escala->id);
        return $escala;
    }

    public function findById(int $id): ?Escala
    {
        $stmt = $this->db->prepare('SELECT * FROM escalas WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $escala = Escala::fromRow($row);
        $escala->postos = $this->postosDaEscala($id);
        return $escala;
    }

    /** @return list<array<string,mixed>> */
    public function postosDaEscala(int $escalaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ep.*, u.nome, u.numero, u.numero_monitor, u.perfil AS perfil_usuario
             FROM escala_postos ep
             JOIN usuarios u ON u.id = ep.usuario_id
             WHERE ep.escala_id = ?
             ORDER BY
               CASE ep.funcao
                 WHEN \'monitor\' THEN 1
                 WHEN \'atirador\' THEN 2
                 ELSE 3
               END,
               CASE WHEN ep.funcao = \'monitor\'
                    THEN CAST(u.numero_monitor AS INTEGER)
                    ELSE CAST(u.numero AS INTEGER)
               END'
        );
        $stmt->execute([$escalaId]);
        return $stmt->fetchAll();
    }

    public function create(string $tipo, string $data, ?int $criadoPor, ?string $obs = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO escalas (tipo, data_servico, observacao, criado_por) VALUES (?,?,?,?)'
        );
        $stmt->execute([$tipo, $data, $obs, $criadoPor]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Posição em que cada função começou nesta escala.
     *
     * @return array{monitor:int,atirador:int}
     */
    public function inicios(int $escalaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT inicio_monitor, inicio_atirador FROM escalas WHERE id = ?'
        );
        $stmt->execute([$escalaId]);
        $row = $stmt->fetch();
        return [
            'monitor'  => (int) ($row['inicio_monitor'] ?? 0),
            'atirador' => (int) ($row['inicio_atirador'] ?? 0),
        ];
    }

    public function updateInicios(int $escalaId, int $monitor, int $atirador): void
    {
        $stmt = $this->db->prepare(
            'UPDATE escalas SET inicio_monitor = ?, inicio_atirador = ? WHERE id = ?'
        );
        $stmt->execute([$monitor, $atirador, $escalaId]);
    }

    /**
     * Escalas anteriores a $data, da mais recente para a mais antiga.
     * A contagem é por função, para a fila continuar depois da véspera
     * mesmo quando o dia tem preta e vermelha.
     *
     * @return list<array{data_servico:string,inicio_monitor:string,inicio_atirador:string,usados_monitor:string,usados_atirador:string}>
     */
    public function escalasAnteriores(string $data, ?int $excetoId = null): array
    {
        $sql = "SELECT e.data_servico, e.inicio_monitor, e.inicio_atirador,
                    (SELECT COUNT(*) FROM escala_postos p
                      WHERE p.escala_id = e.id AND p.funcao = 'monitor')  AS usados_monitor,
                    (SELECT COUNT(*) FROM escala_postos p
                      WHERE p.escala_id = e.id AND p.funcao = 'atirador') AS usados_atirador
             FROM escalas e
             WHERE e.data_servico < ?";
        $params = [$data];
        if ($excetoId !== null) {
            $sql .= ' AND e.id != ?';
            $params[] = $excetoId;
        }
        $sql .= ' ORDER BY e.data_servico DESC, e.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return list<string> */
    public function datasComEscalaDepois(string $data): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT data_servico FROM escalas
             WHERE data_servico > ?
             ORDER BY data_servico'
        );
        $stmt->execute([$data]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * O militar já tem serviço neste dia ou num dia anterior perto demais.
     * A escala que está sendo montada não entra na conta.
     */
    public function servicoDentroDoIntervalo(int $usuarioId, string $data, int $horas, ?int $excetoEscalaId = null): bool
    {
        // Serviços caem em dias inteiros: 48h bloqueia o dia anterior e o mesmo dia.
        $dias = (int) floor(($horas - 1) / 24);
        $sql = "SELECT 1
             FROM escala_postos ep
             JOIN escalas e ON e.id = ep.escala_id
             WHERE ep.usuario_id = ?
               AND ep.status IN ('escalado', 'substituido')
               AND e.data_servico >= date(?, '-{$dias} days')
               AND e.data_servico <= ?";
        $params = [$usuarioId, $data, $data];
        if ($excetoEscalaId !== null) {
            $sql .= ' AND e.id != ?';
            $params[] = $excetoEscalaId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function updateObservacao(int $escalaId, ?string $observacao): void
    {
        $stmt = $this->db->prepare('UPDATE escalas SET observacao = ? WHERE id = ?');
        $stmt->execute([$observacao, $escalaId]);
    }

    public function addPosto(int $escalaId, int $usuarioId, string $funcao): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO escala_postos (escala_id, usuario_id, funcao) VALUES (?,?,?)'
        );
        $stmt->execute([$escalaId, $usuarioId, $funcao]);
        return (int) $this->db->lastInsertId();
    }

    public function findPosto(int $postoId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ep.*, e.tipo, e.data_servico, u.nome, u.numero
             FROM escala_postos ep
             JOIN escalas e ON e.id = ep.escala_id
             JOIN usuarios u ON u.id = ep.usuario_id
             WHERE ep.id = ?'
        );
        $stmt->execute([$postoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePostoUsuario(int $postoId, int $novoUsuarioId, bool $trocado = false): void
    {
        if ($trocado) {
            $stmt = $this->db->prepare('UPDATE escala_postos SET usuario_id = ?, trocado = 1 WHERE id = ?');
        } else {
            $stmt = $this->db->prepare('UPDATE escala_postos SET usuario_id = ? WHERE id = ?');
        }
        $stmt->execute([$novoUsuarioId, $postoId]);
    }

    public function updatePostoStatus(int $postoId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE escala_postos SET status = ? WHERE id = ?');
        $stmt->execute([$status, $postoId]);
    }

    public function findPostoNaEscala(int $escalaId, int $usuarioId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM escala_postos WHERE escala_id = ? AND usuario_id = ? LIMIT 1'
        );
        $stmt->execute([$escalaId, $usuarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePostoFuncaoStatus(int $postoId, string $funcao, string $status = 'escalado'): void
    {
        $stmt = $this->db->prepare('UPDATE escala_postos SET funcao = ?, status = ? WHERE id = ?');
        $stmt->execute([$funcao, $status, $postoId]);
    }

    /**
     * Última data de serviço. Com $tipo, a fila daquela escala (rotação própria).
     * Sem $tipo, qualquer serviço — usado no intervalo de 48h.
     */
    public function ultimaDataServico(int $usuarioId, ?string $tipo = null): ?string
    {
        $sql = 'SELECT e.data_servico
             FROM escala_postos ep
             JOIN escalas e ON e.id = ep.escala_id
             WHERE ep.usuario_id = ? AND ep.status IN (\'escalado\', \'substituido\')';
        $params = [$usuarioId];
        if ($tipo !== null) {
            $sql .= ' AND e.tipo = ?';
            $params[] = $tipo;
        }
        $sql .= ' ORDER BY e.data_servico DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $v = $stmt->fetchColumn();
        return $v !== false ? (string) $v : null;
    }

    /**
     * Escalas de um mês, agrupáveis por data.
     *
     * @return list<array{data_servico:string,tipo:string,total:int}>
     */
    public function resumoMes(string $anoMes): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.data_servico, e.tipo, COUNT(ep.id) AS total
             FROM escalas e
             LEFT JOIN escala_postos ep ON ep.escala_id = e.id
             WHERE strftime(\'%Y-%m\', e.data_servico) = ?
             GROUP BY e.id
             ORDER BY e.data_servico'
        );
        $stmt->execute([$anoMes]);
        return $stmt->fetchAll();
    }

    /**
     * Dias do mês em que o militar está na escala.
     *
     * @return list<array{data_servico:string,tipo:string,funcao:string,status:string}>
     */
    public function diasDoUsuarioNoMes(int $usuarioId, string $anoMes): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.data_servico, e.tipo, ep.funcao, ep.status
             FROM escala_postos ep
             JOIN escalas e ON e.id = ep.escala_id
             WHERE ep.usuario_id = ?
               AND ep.status != \'substituido\'
               AND strftime(\'%Y-%m\', e.data_servico) = ?
             ORDER BY e.data_servico, e.tipo'
        );
        $stmt->execute([$usuarioId, $anoMes]);
        return $stmt->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function postosDoUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ep.id, ep.funcao, ep.status, ep.trocado, e.tipo, e.data_servico
             FROM escala_postos ep
             JOIN escalas e ON e.id = ep.escala_id
             WHERE ep.usuario_id = ?
             ORDER BY e.data_servico DESC'
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    /** @return list<Escala> */
    public function listarPorTipo(string $tipo, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM escalas WHERE tipo = ? ORDER BY data_servico DESC LIMIT ?'
        );
        $stmt->bindValue(1, $tipo);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $e = Escala::fromRow($row);
            $e->postos = $this->postosDaEscala((int) $e->id);
            $list[] = $e;
        }
        return $list;
    }

    public function countMes(string $tipo, string $anoMes): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM escalas WHERE tipo = ? AND strftime('%Y-%m', data_servico) = ?"
        );
        $stmt->execute([$tipo, $anoMes]);
        return (int) $stmt->fetchColumn();
    }

    public function deleteEscala(int $escalaId): void
    {
        $this->db->prepare('DELETE FROM escalas WHERE id = ?')->execute([$escalaId]);
    }

    public function deletePosto(int $postoId): void
    {
        $this->db->prepare('DELETE FROM escala_postos WHERE id = ?')->execute([$postoId]);
    }

    public function limparPostos(int $escalaId): void
    {
        $this->db->prepare('DELETE FROM escala_postos WHERE escala_id = ?')->execute([$escalaId]);
    }
}
