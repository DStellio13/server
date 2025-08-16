<?php
/**
 * htdocs/config.local.php (HORS GIT)
 * Tes secrets réels. Surchargera config.example.php via htdocs/config.php.
 */

declare(strict_types=1);

// Exemple : tu avais parlé d'un mot de passe root
define('DB_DSN',  'mysql:host=127.0.0.1;dbname=htdocs_local;charset=utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', 'RootFort@13@RootContent'); // <-- adapte si besoin

if (!function_exists('db')) {
    function db(): PDO {
        static $pdo;
        if (!$pdo) {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return $pdo;
    }
}
