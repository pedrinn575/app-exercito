<?php
/**
 * Repository de Usuários — único ponto de acesso à tabela usuarios.
 */

namespace App\Repositories;

use App\Config\Database;
use App\Entities\Usuario;
use PDO;

final class UsuarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByNumero(string $numero): ?Usuario
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE numero = ? AND ativo = 1 LIMIT 1');
        $stmt->execute([$numero]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function findById(int $id): ?Usuario
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    /** @return list<Usuario> */
    public function all(bool $somenteAtivos = true): array
    {
        $sql = 'SELECT * FROM usuarios' . ($somenteAtivos ? ' WHERE ativo = 1' : '') . ' ORDER BY CAST(numero AS INTEGER), numero';
        $rows = $this->db->query($sql)->fetchAll();
        return array_map([Usuario::class, 'fromRow'], $rows);
    }

    public function findByNumeroMonitor(string $numeroMonitor): ?Usuario
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE numero_monitor = ? AND ativo = 1 LIMIT 1'
        );
        $stmt->execute([$numeroMonitor]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    /**
     * Ordem da rotação: atiradores do primeiro número ao último.
     * Monitores seguem o número de monitor, do último ao primeiro.
     *
     * @return list<Usuario>
     */
    public function filaDaFuncao(string $perfil, bool $inverso = false): array
    {
        if ($perfil === 'monitor') {
            $stmt = $this->db->prepare(
                "SELECT * FROM usuarios
                 WHERE perfil = 'monitor' AND ativo = 1
                   AND numero_monitor IS NOT NULL AND numero_monitor <> ''
                 ORDER BY CAST(numero_monitor AS INTEGER) DESC, numero_monitor"
            );
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare(
                'SELECT * FROM usuarios WHERE perfil = ? AND ativo = 1
                 ORDER BY CAST(numero AS INTEGER) ' . ($inverso ? 'DESC' : 'ASC') . ', numero'
            );
            $stmt->execute([$perfil]);
        }
        return array_map([Usuario::class, 'fromRow'], $stmt->fetchAll());
    }

    /** @return list<Usuario> */
    public function byPerfil(string $perfil): array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE perfil = ? AND ativo = 1 ORDER BY numero');
        $stmt->execute([$perfil]);
        return array_map([Usuario::class, 'fromRow'], $stmt->fetchAll());
    }

    public function create(Usuario $u): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, numero, numero_monitor, email, senha_hash, perfil, ativo) VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $u->nome, $u->numero, $u->numeroMonitor, $u->email, $u->senhaHash, $u->perfil, $u->ativo ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(Usuario $u): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET nome=?, numero=?, numero_monitor=?, email=?, perfil=?, ativo=?, atualizado_em=datetime(\'now\',\'localtime\') WHERE id=?'
        );
        $stmt->execute([$u->nome, $u->numero, $u->numeroMonitor, $u->email, $u->perfil, $u->ativo ? 1 : 0, $u->id]);
    }

    public function updateSenha(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET senha_hash=? WHERE id=?');
        $stmt->execute([$hash, $id]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET ativo=0 WHERE id=?');
        $stmt->execute([$id]);
    }

    public function countAtivos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM usuarios WHERE ativo=1')->fetchColumn();
    }
}
