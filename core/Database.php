<?php
namespace Niyanta\Core;

use PDO;
use PDOException;

/**
 * Thin PDO wrapper (singleton) using prepared statements throughout.
 */
class Database
{
    private static ?PDO $pdo = null;

    /** Build a PDO instance from explicit credentials (used by the installer). */
    public static function connect(string $host, string $name, string $user, string $pass, string $charset = 'utf8mb4'): PDO
    {
        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    /** Shared connection built from config.php. */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::connect(
                (string) Config::get('db.host', 'localhost'),
                (string) Config::get('db.name', ''),
                (string) Config::get('db.user', ''),
                (string) Config::get('db.pass', ''),
                (string) Config::get('db.charset', 'utf8mb4')
            );
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row or null. */
    public static function first(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Fetch all matching rows. */
    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Fetch a single scalar value. */
    public static function scalar(string $sql, array $params = [])
    {
        return self::query($sql, $params)->fetchColumn();
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::pdo()->lastInsertId();
    }

    /**
     * Execute a multi-statement SQL script. Full-line `--` comments are
     * stripped first so semicolons inside comments don't break the split.
     */
    public static function runScript(\PDO $pdo, string $sql): void
    {
        $lines = preg_split('/\r\n|\r|\n/', $sql) ?: [];
        $clean = [];
        foreach ($lines as $line) {
            if (preg_match('/^\s*--/', $line)) {
                continue; // drop full-line SQL comments
            }
            $clean[] = $line;
        }
        $sql = implode("\n", $clean);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }
    }
}
