<?php
/**
 * Conexão PDO (SQLite por padrão — fácil de rodar localmente).
 * Para PostgreSQL/MySQL, altere DSN e credenciais abaixo.
 */

namespace App\Config;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    /**
     * Retorna a conexão singleton.
     */
    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $dbPath = BASE_PATH . '/storage/escalas.sqlite';
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            try {
                self::$pdo = new PDO('sqlite:' . $dbPath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                self::$pdo->exec('PRAGMA foreign_keys = ON');
                require BASE_PATH . '/database/009_atestado_troca.php';
            } catch (PDOException $e) {
                throw new PDOException('Falha ao conectar ao banco: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
