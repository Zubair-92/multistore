<?php
$url = env("DATABASE_URL");
if ($url) {
    $parsed = parse_url($url);
    if (isset($parsed["host"])) {
        putenv("DB_HOST=" . $parsed["host"]);
        $_ENV["DB_HOST"] = $parsed["host"];
    }
    if (isset($parsed["port"])) {
        putenv("DB_PORT=" . $parsed["port"]);
        $_ENV["DB_PORT"] = $parsed["port"];
    }
    if (isset($parsed["user"])) {
        putenv("DB_USERNAME=" . $parsed["user"]);
        $_ENV["DB_USERNAME"] = $parsed["user"];
    }
    if (isset($parsed["pass"])) {
        putenv("DB_PASSWORD=" . $parsed["pass"]);
        $_ENV["DB_PASSWORD"] = $parsed["pass"];
    }
    if (isset($parsed["path"])) {
        $db = ltrim($parsed["path"], "/");
        putenv("DB_DATABASE=" . $db);
        $_ENV["DB_DATABASE"] = $db;
    }
}
