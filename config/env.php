<?php

function loadEnvFile(string $path): void
{
    static $loadedPaths = [];

    if (isset($loadedPaths[$path]) || !is_readable($path)) {
        return;
    }

    $loadedPaths[$path] = true;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, 'export ') === 0) {
            $line = trim(substr($line, 7));
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        $isQuoted = (
            strlen($value) >= 2
            && (($value[0] === '"' && substr($value, -1) === '"')
                || ($value[0] === "'" && substr($value, -1) === "'"))
        );

        if ($isQuoted) {
            $quote = $value[0];
            $value = substr($value, 1, -1);

            if ($quote === '"') {
                $value = stripcslashes($value);
            }
        } else {
            $value = preg_replace('/\s+#.*$/', '', $value);
        }

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

function envValue(string $name, $default = null)
{
    $value = getenv($name);

    if ($value === false) {
        return $default;
    }

    $normalized = strtolower(trim($value));

    if ($normalized === 'true') {
        return true;
    }

    if ($normalized === 'false') {
        return false;
    }

    if ($normalized === 'null') {
        return null;
    }

    return $value;
}
