<?php
function getProjectConfig(string $project): array {
    $base = __DIR__ . '/projects';
    $file = $base . '/' . strtolower($project) . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("Config projet introuvable: $project ($file)");
    }
    $cfg = require $file;
    foreach (['db_host','db_name','db_user','db_pass'] as $k) {
        if (!array_key_exists($k, $cfg)) {
            throw new RuntimeException("Clé manquante '$k' dans la config du projet $project");
        }
    }
    return $cfg;
}
function projectDb(string $project): PDO {
    $cfg = getProjectConfig($project);
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['db_host'], $cfg['db_name']);
    $options = $cfg['options'] ?? [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], $options);
}
