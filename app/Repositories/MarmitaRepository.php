<?php
/**
 * Pedidos de marmita.
 */

namespace App\Repositories;

use App\Config\Database;
use PDO;

final class MarmitaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $usuarioId, string $data, string $refeicao, int $quantidade, ?string $obs): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO marmitas (usuario_id, data_pedido, refeicao, quantidade, observacao, status)
             VALUES (?,?,?,?,?,\'pendente\')'
        );
        $stmt->execute([$usuarioId, $data, $refeicao, $quantidade, $obs]);
        return (int) $this->db->lastInsertId();
    }

    public function ativaNoDia(int $usuarioId, string $data): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM marmitas
             WHERE usuario_id = ? AND data_pedido = ?
               AND status NOT IN ('cancelado', 'negado')
             LIMIT 1"
        );
        $stmt->execute([$usuarioId, $data]);
        return (bool) $stmt->fetchColumn();
    }

    public function update(int $id, string $data, string $refeicao, int $quantidade, ?string $obs): void
    {
        $stmt = $this->db->prepare(
            "UPDATE marmitas
             SET data_pedido = ?, refeicao = ?, quantidade = ?, observacao = ?,
                 status = 'pendente', retorno = NULL
             WHERE id = ?"
        );
        $stmt->execute([$data, $refeicao, $quantidade, $obs, $id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, u.nome, u.numero
             FROM marmitas m
             JOIN usuarios u ON u.id = m.usuario_id
             WHERE m.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return list<array<string,mixed>> */
    public function listar(?int $usuarioId = null): array
    {
        $sql = 'SELECT m.*, u.nome, u.numero
                FROM marmitas m
                JOIN usuarios u ON u.id = m.usuario_id';
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE m.usuario_id = ?';
            $params[] = $usuarioId;
        }
        $sql .= ' ORDER BY m.data_pedido DESC, m.criado_em DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function aprovadosEntre(string $inicio, string $fim): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*, u.nome, u.numero
             FROM marmitas m
             JOIN usuarios u ON u.id = m.usuario_id
             WHERE m.status = 'aprovado'
               AND m.data_pedido >= ?
               AND m.data_pedido <= ?
             ORDER BY m.data_pedido, CAST(u.numero AS INTEGER), u.nome"
        );
        $stmt->execute([$inicio, $fim]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $retorno = null): void
    {
        $stmt = $this->db->prepare('UPDATE marmitas SET status = ?, retorno = ? WHERE id = ?');
        $stmt->execute([$status, $retorno, $id]);
    }

    public function countPendentes(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM marmitas WHERE status IN ('pendente', 'alteracao')"
        )->fetchColumn();
    }

    public function countPorData(string $data): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(quantidade), 0) FROM marmitas
             WHERE data_pedido = ? AND status = 'aprovado'"
        );
        $stmt->execute([$data]);
        return (int) $stmt->fetchColumn();
    }
}
