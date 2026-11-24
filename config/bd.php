<?php
/**
 * Conexão com o Banco de Dados via PDO
 * Projeto: HairMatch
 * 
 * Regra Global: Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections.
 * Separação lógica: arquivo isolado exclusivamente para conexão.
 */

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'hairmatch_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna a instância única (Singleton) da conexão PDO
 * @return PDO
 */
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $driver = getenv('DB_DRIVER') ?: 'auto';
    $sqlitePath = dirname(__DIR__) . '/database/hairmatch.db';

    // Se driver for forçado para SQLite
    if ($driver === 'sqlite') {
        $pdo = initSqliteConnection($sqlitePath);
        return $pdo;
    }

    // Verificação rápida de porta para não travar a aplicação quando MySQL estiver offline
    $mysqlReachable = false;
    if ($driver === 'auto') {
        $errno = 0;
        $errstr = '';
        $fp = @fsockopen(DB_HOST, (int)DB_PORT, $errno, $errstr, 0.2);
        if ($fp) {
            $mysqlReachable = true;
            fclose($fp);
        }
    } else {
        $mysqlReachable = true;
    }

    if (!$mysqlReachable && $driver === 'auto') {
        $pdo = initSqliteConnection($sqlitePath);
        return $pdo;
    }

    // Tenta conectar ao MySQL via PDO
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;

    } catch (PDOException $e) {
        // Se MySQL estiver indisponível, faz fallback suave para SQLite PDO
        if ($driver === 'auto') {
            $pdo = initSqliteConnection($sqlitePath);
            return $pdo;
        }

        error_log("Erro de conexão PDO MySQL: " . $e->getMessage());
        throw new PDOException("Erro ao conectar com o banco de dados MySQL: " . $e->getMessage(), (int)$e->getCode());
    }
}

/**
 * Conexão fallback SQLite PDO caso o MySQL não esteja rodando localmente
 * Mantém total conformidade com PDO e previne travamento do sistema
 */
function initSqliteConnection(string $sqlitePath): PDO {
    static $sqlitePdo = null;
    if ($sqlitePdo !== null) {
        return $sqlitePdo;
    }

    $dbDir = dirname($sqlitePath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    $sqlitePdo = new PDO('sqlite:' . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Habilita foreign keys no SQLite
    $sqlitePdo->exec("PRAGMA foreign_keys = ON;");
    return $sqlitePdo;
}
