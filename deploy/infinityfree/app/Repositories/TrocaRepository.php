<?php
namespace App\Repositories;

use App\Config\Database;
use PDO;

final class TrocaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $postoId, int $solicitanteId, int $destinoId, ?string $motivo): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO trocas (escala_posto_id, solicitante_id, destino_id, motivo, status)
             VALUES (?,?,?,?,\'pendente\')'
        );
        $stmt->execute([$postoId, $solicitanteId, $destinoId, $motivo]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*,
                    us.nome AS solicitante_nome, us.numero AS solicitante_numero,
                    ud.nome AS destino_nome, ud.numero AS destino_numero,
                    e.data_servico, e.tipo, ep.funcao
             FROM trocas t
             JOIN usuarios us ON us.id = t.solicitante_id
             JOIN usuarios ud ON ud.id = t.destino_id
             JOIN escala_postos ep ON ep.id = t.escala_posto_id
             JOIN escalas e ON e.id = ep.escala_id
             WHERE t.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return list<array<string,mixed>> */
    public function listar(?string $status = null): array
    {
        $sql = 'SELECT t.*,
                       us.nome AS solicitante_nome, us.numero AS solicitante_numero,
                       ud.nome AS destino_nome, ud.numero AS destino_numero,
                       e.data_servico, e.tipo, ep.funcao
                FROM trocas t
                JOIN usuarios us ON us.id = t.solicitante_id
                JOIN usuarios ud ON ud.id = t.destino_id
                JOIN escala_postos ep ON ep.id = t.escala_posto_id
                JOIN escalas e ON e.id = ep.escala_id';
        $params = [];
        if ($status) {
            $sql .= ' WHERE t.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY t.criado_em DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function doUsuario(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*,
                    us.nome AS solicitante_nome, us.numero AS solicitante_numero,
                    ud.nome AS destino_nome, ud.numero AS destino_numero,
                    e.data_servico, e.tipo, ep.funcao
             FROM trocas t
             JOIN usuarios us ON us.id = t.solicitante_id
             JOIN usuarios ud ON ud.id = t.destino_id
             JOIN escala_postos ep ON ep.id = t.escala_posto_id
             JOIN escalas e ON e.id = ep.escala_id
             WHERE t.solicitante_id = ? OR t.destino_id = ?
             ORDER BY t.criado_em DESC'
        );
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE trocas SET status = ?, atualizado_em = datetime(\'now\',\'localtime\') WHERE id = ?'
        );
        $stmt->execute([$status, $id]);
    }

    public function countPendentes(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM trocas WHERE status = 'aceito_destino'"
        )->fetchColumn();
    }
}
