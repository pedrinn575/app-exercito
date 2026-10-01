<?php
/**
 * Permissões de tela por perfil.
 */

namespace App\Repositories;

use App\Config\Database;
use PDO;

final class PermissaoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return array<string, bool> chave => permitido
     */
    public function doPerfil(string $perfil): array
    {
        $stmt = $this->db->prepare('SELECT chave, permitido FROM permissoes WHERE perfil = ?');
        $stmt->execute([$perfil]);

        $mapa = [];
        foreach ($stmt->fetchAll() as $row) {
            $mapa[$row['chave']] = (bool) $row['permitido'];
        }
        return $mapa;
    }

    /**
     * @return array<string, array<string, bool>> perfil => (chave => permitido)
     */
    public function todas(): array
    {
        $rows = $this->db->query('SELECT perfil, chave, permitido FROM permissoes')->fetchAll();

        $mapa = [];
        foreach ($rows as $row) {
            $mapa[$row['perfil']][$row['chave']] = (bool) $row['permitido'];
        }
        return $mapa;
    }

    /**
     * @param array<string, array<string, bool>> $mapa perfil => (chave => permitido)
     */
    public function salvar(array $mapa): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO permissoes (perfil, chave, permitido) VALUES (?,?,?)
             ON CONFLICT (perfil, chave) DO UPDATE SET permitido = excluded.permitido'
        );

        $this->db->beginTransaction();
        foreach ($mapa as $perfil => $chaves) {
            foreach ($chaves as $chave => $permitido) {
                $stmt->execute([$perfil, $chave, $permitido ? 1 : 0]);
            }
        }
        $this->db->commit();
    }
}
