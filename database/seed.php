<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Run this script from the command line: php database/seed.php\n";
    exit(1);
}

$config = require __DIR__ . '/../config/app.php';
$db = $config['db'];

function quoteIdentifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $statement = '';
    $quote = null;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $statement .= $char;

        if ($quote !== null) {
            if ($char === '\\' && $i + 1 < $length) {
                $i++;
                $statement .= $sql[$i];
                continue;
            }

            if ($char === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($char === "'" || $char === '"') {
            $quote = $char;
            continue;
        }

        if ($char === ';') {
            $trimmed = trim(substr($statement, 0, -1));

            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }

            $statement = '';
        }
    }

    $trimmed = trim($statement);

    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}

function runSqlFile(PDO $pdo, string $path): void
{
    if (!is_readable($path)) {
        throw new RuntimeException("SQL file not found or not readable: {$path}");
    }

    $sql = file_get_contents($path);

    if ($sql === false) {
        throw new RuntimeException("Could not read SQL file: {$path}");
    }

    foreach (splitSqlStatements($sql) as $statement) {
        $pdo->exec($statement);
    }
}

try {
    $charset = (string) $db['charset'];

    if (!preg_match('/^[A-Za-z0-9_]+$/', $charset)) {
        throw new RuntimeException('Invalid database charset configured.');
    }

    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            $db['host'],
            $db['port'],
            $charset
        ),
        $db['username'],
        $db['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );

    $database = (string) $db['database'];

    $pdo->exec(
        sprintf(
            'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET %s',
            quoteIdentifier($database),
            $charset
        )
    );
    $pdo->exec('USE ' . quoteIdentifier($database));

    runSqlFile($pdo, __DIR__ . '/schema.sql');
    runSqlFile($pdo, __DIR__ . '/seed.sql');

    echo "Database '{$database}' seeded successfully.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Database seed failed: " . $exception->getMessage() . "\n");
    exit(1);
}
