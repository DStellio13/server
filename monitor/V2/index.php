<?php
// htdocs/monitor/v2/index.php — Dashboard V2 (sans graphe)
$projectSlug = 'monitor';
$root = dirname(__DIR__, 2); // -> htdocs
require_once $root.'/includes/core/config.php';
$pdo = db();

// Récup projet (facultatif)
$projStmt = $pdo->prepare("SELECT name, description FROM projects WHERE slug = ? LIMIT 1");
$projStmt->execute([$projectSlug]);
$project = $projStmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Monitor', 'description' => "Écoute des ports NDI pour détecter les décalages audio/vidéo. Analyse effectuée en Python (hors serveur web)."];

// Tâches ouvertes
$tasksStmt = $pdo->prepare("
  SELECT id, title, status, priority, due_at
  FROM tasks
  WHERE project_slug = :slug AND is_archived = 0 AND status <> 'done'
  ORDER BY FIELD(status,'in-progress','todo','blocked'), priority ASC, id DESC
");
$tasksStmt->execute([':slug' => $projectSlug]);
$tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

function group_tasks(array $rows): array {
  $g = ['in-progress'=>[], 'todo'=>[], 'blocked'=>[]];
  foreach ($rows as $r) {
    $s = str_replace('_','-', strtolower($r['status'] ?? 'todo'));
    $s = in_array($s, ['in-progress','todo','blocked']) ? $s : 'todo';
    $g[$s][] = $r;
  }
  return $g;
}
$groups = group_tasks($tasks);

// Statut JSON (optionnel)
$livePath = __DIR__ . '/data/live.json';
$live = null; $liveUpdated = null;
if (is_file($livePath)) {
  $json = @file_get_contents($livePath);
  if ($json !== false) {
    $live = json_decode($json, true);
    $liveUpdated = $live['timestamp'] ?? $live['updated_at'] ?? date('c', @filemtime($livePath) ?: time());
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Projet <?= htmlspecialchars($project['name'] ?: 'Monitor') ?> — V2</title>

  <!-- Global d'abord -->
  <link rel="stylesheet" href="/style/style.css">
  <script src="/script/script.js" defer></script>

  <!-- Pas de /script/monitor.js ici (pas de graphe en V2) -->

  <style>
    .kpis{display:flex;gap:12px;flex-wrap:wrap;margin:8px 0}
    .kpis .k{border:1px solid #e5e7eb;border-radius:10px;padding:.4rem .6rem;background:#fff}
    .muted{color:#666}
    .cols{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:10px}
    .card h3{margin:.3rem 0 .6rem}
    .checklist li.todo{list-style:'⬜ ' inside}
    .checklist li.in-progress{list-style:'🟡 ' inside}
    .checklist li.blocked{list-style:'⛔ ' inside}
    pre.json{max-height:40vh;overflow:auto;background:#0b1022;color:#e5e7eb;padding:.75rem;border-radius:8px}
  </style>
</head>
<body>

  <h1>📡 Projet <?= htmlspecialchars($project['name'] ?: 'Monitor') ?> — V2</h1>
  <p class="muted">
    <?= htmlspecialchars($project['description']) ?>
  </p>

  <p>
    <a href="/monitor/v1/index.php" target="_blank">📜 Accéder à l’ancienne version (V1)</a>
    · <a href="/tasks/index.php?project=monitor" target="_blank">✅ Tâches du projet</a>
  </p>

  <div class="cols">
    <!-- Objectifs / checklist du projet -->
    <section class="card">
      <h3>🎯 Objectifs</h3>
      <ul class="checklist">
        <li class="todo">Écrire le script Python d’écoute NDI</li>
        <li class="todo">Extraire latence, jitter, frames, drift audio</li>
        <li class="todo">Émettre un JSON simple exploitable par le dashboard</li>
        <li class="todo">Définir le seuil de tolérance et les alertes</li>
      </ul>
    </section>

    <!-- Statut "live" texte (sans graphe) -->
    <section class="card">
      <h3>📊 Statut Live (texte)</h3>
      <div class="kpis">
        <div class="k">Flux: <strong>
          <?= $live && !empty($live['flux']) ? '🟢 Actif' : '🔴 Inactif' ?>
        </strong></div>
        <div class="k">Latence: <strong><?= $live['latency'] ?? '—' ?> ms</strong></div>
        <div class="k">Jitter: <strong><?= $live['jitter'] ?? '—' ?> ms</strong></div>
        <div class="k">Frames: <strong><?= $live['frames'] ?? '—' ?></strong></div>
        <div class="k">Décalage audio: <strong><?= $live['videoDrift'] ?? '—' ?> ms</strong></div>
      </div>
      <small class="muted">Dernière mise à jour : <?= $liveUpdated ? htmlspecialchars($liveUpdated) : '—' ?></small>

      <?php if ($live): ?>
        <details style="margin-top:6px">
          <summary>Voir le JSON</summary>
          <pre class="json"><?= htmlspecialchars(json_encode($live, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
        </details>
      <?php else: ?>
        <p class="muted">Aucune donnée <code>data/live.json</code> pour le moment.</p>
      <?php endif; ?>
    </section>

    <!-- Tâches ouvertes -->
    <section class="card" style="grid-column:1/-1">
      <h3>📌 Tâches en cours</h3>
      <div class="cols">
        <div>
          <h4>En cours</h4>
          <ul class="checklist">
            <?php foreach ($groups['in-progress'] as $t): ?>
              <li class="in-progress">
                <?= htmlspecialchars($t['title']) ?>
                <?php if (!empty($t['due_at'])): ?>
                  — <small class="muted">échéance <?= htmlspecialchars(date('d/m/Y', strtotime($t['due_at']))) ?></small>
                <?php endif; ?>
              </li>
            <?php endforeach; if (empty($groups['in-progress'])): ?>
              <li class="todo muted">Rien pour l’instant</li>
            <?php endif; ?>
          </ul>
        </div>
        <div>
          <h4>À faire</h4>
          <ul class="checklist">
            <?php foreach ($groups['todo'] as $t): ?>
              <li class="todo">
                <?= htmlspecialchars($t['title']) ?>
                <?php if (!empty($t['due_at'])): ?>
                  — <small class="muted">échéance <?= htmlspecialchars(date('d/m/Y', strtotime($t['due_at']))) ?></small>
                <?php endif; ?>
              </li>
            <?php endforeach; if (empty($groups['todo'])): ?>
              <li class="todo muted">Aucune tâche</li>
            <?php endif; ?>
          </ul>
        </div>
        <div>
          <h4>Bloquées</h4>
          <ul class="checklist">
            <?php foreach ($groups['blocked'] as $t): ?>
              <li class="blocked"><?= htmlspecialchars($t['title']) ?></li>
            <?php endforeach; if (empty($groups['blocked'])): ?>
              <li class="todo muted">Rien de bloqué</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
      <p style="margin-top:8px" class="muted">
        Pour ajouter/modifier des tâches, utilise la Quickbar (bouton en bas à droite) ou la page <a href="/tasks/index.php?project=monitor">Tasks</a>.
      </p>
    </section>
  </div>

<?php $quickbar_scope='tasks-only'; $projectSlug='monitor';
include $_SERVER['DOCUMENT_ROOT'].'/includes/ui/quickbar/quickbar.php'; ?>

</body>
</html>
