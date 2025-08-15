<?php
require_once __DIR__.'/includes/config.php';

$slug = preg_replace('~[^a-z0-9_/-]+~i','', $_GET['slug'] ?? '');
if (!$slug) { http_response_code(400); exit('slug invalide'); }

$base = __DIR__ . '/';
$dir  = $base . $slug;

if (!is_dir($dir)) mkdir($dir, 0775, true);

$index = $dir . '/index.php';
if (!is_file($index)) {
  $html = <<<PHP
<?php /* {$slug}/index.php */ ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$slug}</title>
  <link rel="stylesheet" href="../style/style.css">
  <script src="../script/script.js" defer></script>
</head>
<body>
  <section class="project" data-target="{$slug}" tabindex="0" aria-expanded="true" aria-controls="{$slug}-details">
    <h2 class="project-title"><?= htmlspecialchars(ucfirst('{$slug}')) ?></h2>
    <div class="project-details" id="{$slug}-details">
      <p>Page initialisée.</p>
      <ul class="checklist">
        <li class="done">Création du dossier</li>
        <li class="done">Index par défaut</li>
        <li class="todo">Contenu spécifique du projet</li>
      </ul>
    </div>
  </section>
</body>
</html>
PHP;
  file_put_contents($index, $html);
}

// Optionnel: marquer initialisé en BDD
$pdo = db();
$pdo->prepare("UPDATE projects SET is_initialized = 1, entry_path = ? WHERE slug = ?")
    ->execute([$slug.'/index.php', $slug]);

header('Location: index.php');
