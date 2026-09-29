<?php
/**
 * Feriados extras (além dos nacionais fixos) que viram escala vermelha.
 */

namespace App\Repositories;

use App\Config\Database;
use PDO;

final class FeriadoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function exists(string $data): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM feriados WHERE data = ? LIMIT 1');
        $stmt->execute([$data]);
        return (bool) $stmt->fetchColumn();
    }

    /** @return list<array{id:int,data:string,nome:string}> */
    public function listar(): array
    {
        return $this->db->query('SELECT id, data, nome FROM feriados ORDER BY data')->fetchAll();
    }

    /** @return list<array{id:int,data:string,nome:string}> */
    public function doMes(string $anoMes): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, data, nome FROM feriados WHERE strftime('%Y-%m', data) = ? ORDER BY data"
        );
        $stmt->execute([$anoMes]);
        return $stmt->fetchAll();
    }

    public function create(string $data, string $nome): int
    {
        $stmt = $this->db->prepare('INSERT INTO feriados (data, nome) VALUES (?, ?)');
        $stmt->execute([$data, $nome]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM feriados WHERE id = ?')->execute([$id]);
    }
}
