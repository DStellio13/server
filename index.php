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
  $rel = preg_replace('#\?.*$#', '', $rel); // drop query pour FS
  $rel = ltrim($rel, '/\\');
  if (str_contains($rel, '..')) return null; // no traversal
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

  // 1) Si la BDD indique une entrée, on la teste en priorité
  if ($san = sanitize_rel_path($entryFromDb)) {
    $candidates[] = $san;
  }

  // 2) Index à la racine du projet (cas classique)
  $candidates[] = "$slug/index.php";
  $candidates[] = "$slug/index.html";

  // 3) Sous-dossiers versionnés : v1, V2, version-3, etc.
  $projDir = $base . $slug;
  if (is_dir($projDir)) {
    $versions = []; // [numero => 'nomDossier']
    foreach (scandir($projDir) as $sub) {
      if ($sub === '.' || $sub === '..') continue;
      $path = $projDir . DIRECTORY_SEPARATOR . $sub;
      if (!is_dir($path)) continue;

      // v2 / V2 / version-2 / VERSION_10
      if (preg_match('/^(?:v(?:ersion)?)?[-_ ]?(\d+)$/i', $sub, $m)) {
        $num = (int)$m[1];
        $versions[$num] = $sub;
      }
    }

    if ($versions) {
      krsort($versions, SORT_NUMERIC);              // plus grand numéro d’abord
      $latest = reset($versions);                   // ex: 'v2'
      // on essaie index.php puis index.html dans ce sous-dossier
      $candidates[] = "$slug/$latest/index.php";
      $candidates[] = "$slug/$latest/index.html";
    }
  }

  // 4) Résolution des candidats
  foreach ($candidates as $rel) {
    $fs = $base . $rel;
    if (is_file($fs)) {
      $nonEmpty = is_non_empty_file($fs);
      return [$rel, $fs, true, $nonEmpty];
    }
  }

  // 5) Fallback : rien trouvé → retourne le premier candidat testé
  $fallback = $candidates[0] ?? "$slug/index.php";
  return [$fallback, $base . $fallback, false, false];
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

/** Tâches ouvertes (top N) */
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

/** Normalisation des statuts → classes CSS */
function status_class(string $status): string {
  // uniformiser tirets/underscores
  $s = str_replace('_', '-', strtolower(trim($status)));
  return match ($s) {
    'in-progress' => 'in-progress',
    'done'        => 'done',
    'blocked'     => 'todo', // ou 'blocked' si tu ajoutes le style
    default       => 'todo',
  };
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Accueil - Mes Projets</title>

  <!-- Global d'abord (règle projet) -->
  <link rel="stylesheet" href="style/style.css">
  <script src="script/script.js" defer></script>

</head>
<body>
<header class="site-header">
  <?php if (is_file(__DIR__.'/assets/logo.svg')): ?>
    <img src="assets/logo.svg" alt="Mes Projets Locaux" width="160" height="36">
  <?php else: ?>
    <strong>Mes Projets Locaux</strong>
  <?php endif; ?>
  <div class="header-actions">
    <a href="?scan=1" style="text-decoration:none">↻ Scanner les projets</a>
    <button id="compactToggle" type="button" aria-pressed="false" title="Basculer mode compact">Compact</button>
  </div>
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
    $entry    = $p['_entry_rel'];
    $exists   = (bool)$p['_exists'];
    $nonEmpty = (bool)$p['_nonempty'];
    $autoInit = ((int)$p['_auto_init'] === 1);

    // Cas particulier "tasks" → ajouter un filtre si pas présent
    $entryUrl = $entry;
    if ($slug === 'tasks' && $entryUrl && !str_contains($entryUrl, '?')) {
      $entryUrl .= '?project=' . urlencode($slug);
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
          <a href="<?= htmlspecialchars($entryUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ouvrir le projet</a>
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
<!-- ===== TÂCHES TEMPORAIRES (HARDCODÉES) POUR L'ACCUEIL ===== -->
<!-- ===== TÂCHES TEMPORAIRES (HARDCODÉES) POUR L'ACCUEIL ===== -->
<style>
  .idx-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:10px;margin:12px 0}
  .idx-card h2{margin:.2rem 0 .6rem}
  .idx-checklist{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:6px}
  .idx-checklist li{padding:8px;border:1px dashed #e5e7eb;border-radius:8px;background:#fafafa}
  .idx-checklist li.todo{list-style:'⬜ ' inside}
  .idx-checklist li.progress{list-style:'🟡 ' inside}
  .idx-checklist li.bug{list-style:'🐞 ' inside;border-color:#fca5a5;background:#fff5f5}
  .idx-muted{color:#666}
</style>

<section class="idx-card" aria-label="Tâches accueil (temporaire)">
  <h2>🧰 Tâches à faire — Accueil</h2>
  <ul class="idx-checklist ">
    <li class="bug card"><strong>Quickbar / Projets</strong> — Réussir à charger la liste des projets (le select reste vide).</li>
    <li class="bug"><strong>Quickbar / Tâches</strong> — Même souci : la liste des projets est vide dans l’onglet Tâches.</li>
    <li class="todo"><strong>Grille Accueil</strong> — Revoir la hauteur des cartes : quand on ouvre une carte, les 2 autres colonnes s’allongent sans contenu.</li>
    <li class="todo"><strong>Quickbar / UI</strong> — Garder une taille fixe quand on change d’onglet (la hauteur ne doit pas sauter).</li>
    <li class="todo"><strong>Accueil / Projets</strong> — Supprimer les dossiers colorés à gauche de l’emoji (l’emoji suffit).</li>
    <li class="todo"><strong>Accueil / Projets</strong> — Revoir les infos affichées dans la carte quand on clique (contenu + ordre).</li>
    <li class="bug"><strong>PHP Warning</strong> — Corriger <code>session_start()</code> après envoi des headers (includes/ui/quickbar/quickbar.php:8).</li>
    <li class="todo"><strong>Accueil</strong> — Revoir l'interface index htdocs parceque c'est sans ame la. peut etre rajouter des animations de l'ajax a reflechir.</li>
    <!-- NOUVEAU : sauvegardes -->
    <li class="todo"><strong>Sauvegarde</strong> — Faire une sauvegarde complète de la BDD et de <code>htdocs</code> (snapshot du jour).</li>
    <li class="todo"><strong>Automatisation sauvegarde</strong> — Créer un script + planification (rotation 7/30 jours, logs, exclusions temporaires).</li>
  </ul>
  <p class="idx-muted" style="margin-top:8px">Bloc temporaire (hardcodé). À supprimer quand la Quickbar refonctionne.</p>
</section>
<!-- ===== FIN TÂCHES TEMPORAIRES ===== -->

<!-- ===== FIN TÂCHES TEMPORAIRES ===== -->
<?php if (empty($projects)): ?>
  <p style="margin-top:1rem;color:#a00"><strong>Aucun projet actif.</strong> Remplis la table <code>projects</code> dans <code>htdocs_local</code>.</p>
<?php endif; ?>

<!-- Quickbar : un seul set, en relatif, et à la fin du body -->
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/ui/quickbar/quickbar.php'; ?>
</body>
</html>
