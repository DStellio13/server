<?php
/**
 * Core projets : fallback global + overrides optionnels par projet.
 * - Config globale: includes/core/config.php
 * - Override projet (facultatif): includes/core/projects/<slug>.php
 *
 * API exposée:
 *   getProjectConfig(string $slug): array
 *   projectDb(string $slug): PDO
 */

function __core_global_config(): array {
  static $cfg = null;
  if ($cfg !== null) return $cfg;

  $path = __DIR__ . '/config.php';
  if (!is_file($path)) {
    throw new RuntimeException("Config globale introuvable: includes/core/config.php");
  }
  $cfg = require $path;
  if (!is_array($cfg) || empty($cfg['db'])) {
    throw new RuntimeException("Config globale invalide (clé 'db' manquante).");
  }
  return $cfg;
}

/**
 * Charge la config d’un projet :
 * - si un fichier override existe (includes/core/projects/<slug>.php), on le charge
 * - sinon, on renvoie un array minimal fusionné avec la globale
 */
function getProjectConfig(string $slug): array {
  $global = __core_global_config();

  $overridePath = __DIR__ . '/projects/' . $slug . '.php';
  $override = [];
  if (is_file($overridePath)) {
    $override = require $overridePath;
    if (!is_array($override)) {
      throw new RuntimeException("Override projet invalide pour '$slug'.");
    }
  }

  // Base minimale du projet
  $base = [
    'slug'   => $slug,
    'tables' => $global['tables'] ?? [],
  ];

  // Fusion simple (override > base > global)
  // Note: pour la DB on gère plus bas (dsn vs host/port/name)
  $cfg = array_replace_recursive($global, $base, $override);
  return $cfg;
}

/**
 * Retourne un PDO pour le projet (singleton par slug).
 * Par défaut, utilise la DB globale (host/port/name/user/pass/charset).
 * Overrides possibles côté projet:
 *   - 'db' => ['name' => 'autre_base']             // garde host/user/pass globaux
 *   - 'db' => ['dsn' => 'mysql:...','user'=>'..']  // DSN complet custom
 */
function projectDb(string $slug): PDO {
  static $pool = [];

  if (isset($pool[$slug])) return $pool[$slug];

  $cfg = getProjectConfig($slug);

  $db = $cfg['db'] ?? [];
  $charset = $db['charset'] ?? 'utf8mb4';

  if (!empty($db['dsn'])) {
    // Override DSN complet
    $dsn  = $db['dsn'];
    $user = $db['user'] ?? 'root';
    $pass = $db['pass'] ?? '';
  } else {
    // Construction DSN depuis host/port/name (fallback global)
    $host = $db['host'] ?? '127.0.0.1';
    $port = $db['port'] ?? 3306;
    $name = $db['name'] ?? 'htdocs_local';
    $user = $db['user'] ?? 'root';
    $pass = $db['pass'] ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
  }

  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  $pool[$slug] = $pdo;
  return $pdo;
}
