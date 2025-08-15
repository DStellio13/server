<?php
require_once __DIR__.'/includes/core/config.php';
require_once __DIR__.'/includes/projects/scan.php';

if (isset($_GET['scan']) && $_GET['scan'] === '1') {
  $res = scan_and_sync_projects();
  echo '<div style="background:#e7f7e9;border:1px solid #bfe6c6;padding:8px;margin:8px 0;border-radius:6px">
          Scan terminé → insérés: '.(int)$res['inserted'].' · mis à jour: '.(int)$res['updated'].'
        </div>';
}

$pdo = db();

/**
 * Sécurise un chemin relatif stocké en BDD : force relatif à htdocs, empêche les ../
 */
function sanitize_rel_path(?string $rel): ?string {
  if (!$rel) return null;
  $rel = preg_replace('#\?.*$#', '', $rel);               // drop query pour FS
  $rel = ltrim($rel, '/\\');
  if (str_contains($rel, '..')) return null;               // no traversal
  return $rel;
}

/**
 * “Non vide” un peu plus robuste : > 10 octets ET contenu avec au moins 1 caractère non-blanc
 */
function is_non_empty_file(string $fs): bool {
  if (!is_file($fs) || filesize($fs) <= 10) return false;
  $fh = fopen($fs, 'rb');
  if (!$fh) return false;
  $chunk = fread($fh, 2048);
  fclose($fh);
  return (bool)preg_match('/\S/u', $chunk);
}

/**
 * Résout l’entrée (index.php / index.html), priorité à la BDD si valide.
 */
function resolve_entry(string $slug, ?string $entryFromDb): array {
  $base = __DIR__ . '/';
  $candidates = [];

  $sanitized = sanitize_rel_path($entryFromDb);
  if ($sanitized) $candidates[] = $sanitized;

  $candidates[] = $slug . '/index.php';
  $candidates[] = $slug . '/index.html';

  foreach ($candidates as $rel) {
    $fs = $base . $rel;
    if (is_file($fs)) {
      $nonEmpty = is_non_empty_file($fs);
      return [$rel, $fs, true, $nonEmpty];
    }
  }
  // par défaut, on renvoie le premier candidat attendu même s’il n’existe pas
  return [$candidates[0], $base . $candidates[0], false, false];
}

/** Charge projets actifs */
$projects = $pdo->query("
  SELECT id, slug, name, emoji, description, icon_path, entry_path,
         order_index, is_active, is_initialized
  FROM projects
  WHERE is_active = 1
  ORDER BY order_index, name
")->fetchAll(PDO::FETCH_ASSOC);

/** Synchronisation auto de is_initialized + entry_path */
$updateStmt = $pdo->prepare("UPDATE projects SET is_initialized = ?, entry_path = ? WHERE id = ?");

foreach ($projects as &$p) {
  [$entryRel, $entryFs, $exists, $nonEmpty] = resolve_entry($p['slug'], $p['entry_path'] ?? null);
  $autoInit = ($exists && $nonEmpty) ? 1 : 0;

  $p['_entry_rel'] = $entryRel;
  $p['_exists']    = $exists;
  $p['_nonempty']  = $nonEmpty;
  $p['_auto_init'] = $autoInit;

  if ((int)($p['is_initialized'] ?? 0) !== $autoInit || ($p['entry_path'] ?? '') !== $entryRel) {
    try {
      $updateStmt->execute([$autoInit, $entryRel, $p['id']]);
      $p['is_initialized'] = $autoInit;
      $p['entry_path']     = $entryRel;
    } catch (Throwable $e) {
      // silencieux
    }
  }
}
unset($p);
function render_tasks_for_project(PDO $pdo, string $slug): string {
  $stmt = $pdo->prepare("
    SELECT id, title, status, notes
    FROM tasks
    WHERE project_slug = ?
    ORDER BY 
      FIELD(status,'in-progress','todo','done'),  -- ordre visuel sympa
      id DESC
    LIMIT 200
  ");
  $stmt->execute([$slug]);
  $rows = $stmt->fetchAll();

  if (!$rows) {
    return '<ul class="checklist"><li class="todo">Aucune tâche encore — ajoute-les depuis Tasks 🌱</li></ul>';
  }

  $li = '';
  foreach ($rows as $t) {
    $statusClass = htmlspecialchars($t['status'] ?: 'todo');
    $title = htmlspecialchars($t['title']);
    $notes = trim((string)$t['notes']) !== '' ? '<small style="color:#666;display:block;margin-top:2px">'.nl2br(htmlspecialchars($t['notes'])).'</small>' : '';
    $li .= "<li class=\"{$statusClass}\">{$title}{$notes}</li>";
  }
  return "<ul class=\"checklist\">{$li}</ul>";
}
function fetch_open_tasks(PDO $pdo, string $slug, int $limit = 5): array {
  $sql = "SELECT id, title, status, priority, due_at
          FROM tasks
          WHERE project_slug = :slug AND is_archived = 0 AND status <> 'done'
          ORDER BY order_index ASC, priority ASC, id ASC
          LIMIT :lim";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
  $stmt->bindValue(':lim',  $limit, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function status_class(string $status): string {
  switch ($status) {
    case 'in_progress': return 'in-progress';
    case 'done':        return 'done';
    case 'blocked':     return 'todo'; // ou 'blocked' si tu ajoutes le style
    default:            return 'todo';
  }
}

?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Accueil - Mes Projets</title>
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="/includes/ui/quickbar/quickbar.css">
  <script src="script/script.js" defer></script>
  <style>
    .site-header{display:flex;align-items:center;justify-content:space-between;margin:8px 0 14px}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
    .card{background:#fff;border:1px solid #ddd;border-radius:10px;padding:10px 12px}
    .title{display:flex;align-items:center;gap:.5rem;margin:0;font-size:1.05rem;font-weight:700}
    .title img.icon{width:20px;height:20px;vertical-align:middle}
    .meta{display:flex;gap:8px;margin-top:6px;font-size:.85rem;color:#555}
    .pill{border:1px solid #ddd;border-radius:999px;padding:2px 8px}
    .pill.state-ok{color:#16853a}
    .pill.state-missing{color:#b91c1c;border-color:#fca5a5}
    .pill.state-na{color:#b45309}
    .details{margin-top:8px;padding-top:8px;border-top:1px dashed #ddd}
  </style>

  <script src="/includes/ui/quickbar/loader.js" defer></script>
</head>
<body>
<header class="site-header">
  <?php if (is_file(__DIR__.'/assets/logo.svg')): ?>
    <img src="assets/logo.svg" alt="Mes Projets Locaux" width="160" height="36">
  <?php else: ?>
    <strong>Mes Projets Locaux</strong>
  <?php endif; ?>
  <small><a href="?scan=1" style="text-decoration:none">↻ Scanner les projets</a></small>
</header>

<h1>📁 Mes Projets Locaux</h1>

<section class="grid" aria-label="Liste des projets">
<?php foreach ($projects as $p):
    $slug = htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8');
    $desc = htmlspecialchars((string)($p['description'] ?? ''), ENT_QUOTES, 'UTF-8');
    $icon = htmlspecialchars((string)($p['icon_path'] ?? ''), ENT_QUOTES, 'UTF-8');
    $emoji = $p['emoji'] ?? '';

    // Source de vérité : ce qu’on vient de résoudre
    $entry  = $p['_entry_rel'];
    $exists = (bool)$p['_exists'];
    $nonEmpty = (bool)$p['_nonempty'];
    $autoInit = ((int)$p['_auto_init'] === 1);

    // Cas particulier "tasks" → ajouter un filtre si pas présent
    if ($slug === 'tasks' && $entry && !str_contains($entry, '?')) {
      $entry .= '?project=' . urlencode($slug);
    }
?>
  <article class="card project" data-target="<?= $slug ?>" tabindex="0" aria-expanded="false" aria-controls="<?= $slug ?>-details">
    <h2 class="title">
      <?php if ($icon): ?><img class="icon" src="<?= $icon ?>" alt=""><?php endif; ?>
      <?= $emoji ? htmlspecialchars($emoji, ENT_QUOTES, 'UTF-8') . ' ' : '' ?><?= $name ?>
    </h2>

    <div class="meta" aria-hidden="true">
      <?php if ($autoInit): ?>
        <span class="pill state-ok">✅ Prêt</span>
      <?php elseif ($exists && !$nonEmpty): ?>
        <span class="pill state-missing">⚠️ Fichier vide</span>
      <?php else: ?>
        <span class="pill state-na">🚧 Aucun index</span>
      <?php endif; ?>
    </div>

    <div class="details project-details" id="<?= $slug ?>-details" hidden>
      <?php if ($desc): ?><p><?= $desc ?></p><?php endif; ?>

      <?php if ($autoInit): ?>
        <p>
          <a href="<?= htmlspecialchars($entry, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ouvrir le projet</a>
          · <a href="tasks/index.php?project=<?= $slug ?>">Tâches</a>
        </p>
<?php
  $tasks = fetch_open_tasks($pdo, $slug, 5);
  if (!empty($tasks)):
?>
  <h3 style="margin-top:.5rem">📌 Tâches en cours</h3>
  <ul class="checklist">
    <?php foreach ($tasks as $t): ?>
      <li class="<?= htmlspecialchars(status_class($t['status'])) ?>">
        <?= htmlspecialchars($t['title']) ?>
        <?php if (!empty($t['due_at'])): ?>
          — <small>échéance <?= htmlspecialchars(date('d/m/Y', strtotime($t['due_at']))) ?></small>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <p><a href="tasks/index.php?project=<?= urlencode($slug) ?>">Voir toutes les tâches</a></p>
<?php else: ?>
  <p><em>Pas de tâche ouverte.</em> <a href="tasks/index.php?project=<?= urlencode($slug) ?>">En ajouter</a></p>
<?php endif; ?>

      <?php elseif ($exists && !$nonEmpty): ?>
        <p style="color:#b91c1c">
          ⚠️ Le fichier existe mais semble vide :
          <code><?= htmlspecialchars($entry, ENT_QUOTES, 'UTF-8') ?></code>
        </p>

      <?php else: ?>
        <p style="color:#b45309">
          🧩 Projet non initialisé (aucun <code>index.php</code>/<code>index.html</code> trouvé).
          · <a href="init_project.php?slug=<?= urlencode($slug) ?>">Créer le dossier & un index par défaut</a>
        </p>
      <?php endif; ?>
    </div>
  </article>
<?php endforeach; ?>
</section>

<?php if (empty($projects)): ?>
  <p style="margin-top:1rem;color:#a00"><strong>Aucun projet actif.</strong> Remplis la table <code>projects</code> dans <code>htdocs_local</code>.</p>
<?php endif; ?>
<?php include __DIR__.'/includes/ui/quickbar/quickbar.php'; ?>
<script src="/includes/ui/quickbar/quickbar.js" defer></script>
</body>
<?php
  // si la page connaît son slug projet :
  // $projectSlug = 'streaming';
  include __DIR__.'/includes/quickbar.php';
?> 
</html>
