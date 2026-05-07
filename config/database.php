<?php
/**
 * TaskFlow Pro — PDO Database Connection (Singleton)
 */
class Database
{
    private static ?PDO $instance = null;
    private function __construct() {}
    private function __clone()     {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('[TaskFlow DB Error] ' . $e->getMessage());
                die(json_encode([
                    'success' => false,
                    'error'   => 'Database connection failed. Check config/config.php settings.',
                ]));
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function beginTransaction(): void { self::getInstance()->beginTransaction(); }
    public static function commit(): void           { self::getInstance()->commit(); }
    public static function rollBack(): void         { self::getInstance()->rollBack(); }
    public static function lastInsertId(): string   { return self::getInstance()->lastInsertId(); }
}