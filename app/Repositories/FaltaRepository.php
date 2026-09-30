<?php
namespace App\Repositories;

use App\Config\Database;
use PDO;

final class FaltaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function registrar(
        int $postoId,
        string $tipo,
        ?int $substitutoId,
        ?int $registradoPor,
        ?string $obs
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO faltas (escala_posto_id, tipo, substituto_id, registrado_por, observacao)
             VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$postoId, $tipo, $substitutoId, $registradoPor, $obs]);
        return (int) $this->db->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public function listar(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT f.*,
                    u.nome, u.numero,
                    e.data_servico, e.tipo AS tipo_escala,
                    s.nome AS substituto_nome
             FROM faltas f
             JOIN escala_postos ep ON ep.id = f.escala_posto_id
             JOIN usuarios u ON u.id = ep.usuario_id
             JOIN escalas e ON e.id = ep.escala_id
             LEFT JOIN usuarios s ON s.id = f.substituto_id
             ORDER BY f.criado_em DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Serviços de atirador em dias que já passaram.
     * Sem falta registrada, conta como presença.
     *
     * @return list<array{id:int,numero:string,nome:string,data_servico:string,resultado:string}>
     */
    public function servicosPassadosAtirador(string $ate): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.numero, u.nome, e.data_servico,
                    CASE
                      WHEN EXISTS (
                        SELECT 1 FROM faltas f
                        WHERE f.escala_posto_id = ep.id AND f.tipo = 'injustificada'
                      ) THEN 'injustificada'
                      WHEN EXISTS (
                        SELECT 1 FROM faltas f
                        WHERE f.escala_posto_id = ep.id AND f.tipo = 'justificada'
                      ) THEN 'justificada'
                      ELSE 'presenca'
                    END AS resultado
             FROM escala_postos ep
             JOIN usuarios u ON u.id = ep.usuario_id
             JOIN escalas e ON e.id = ep.escala_id
             WHERE u.perfil = 'atirador'
               AND ep.funcao = 'atirador'
               AND e.data_servico < ?
             ORDER BY e.data_servico, CAST(u.numero AS INTEGER)"
        );
        $stmt->execute([$ate]);
        return $stmt->fetchAll();
    }

    public function countInjustificadasMes(string $anoMes): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM faltas f
             JOIN escala_postos ep ON ep.id = f.escala_posto_id
             JOIN escalas e ON e.id = ep.escala_id
             WHERE f.tipo = 'injustificada' AND strftime('%Y-%m', e.data_servico) = ?"
        );
        $stmt->execute([$anoMes]);
        return (int) $stmt->fetchColumn();
    }
}
