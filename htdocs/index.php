<?php
declare(strict_types=1);

/**
 * htdocs/htdocs/index.php
 * - Affiche le README.md (cadre scrollable)
 * - Résume les grandes lignes du style global (style/style.css)
 * - Liste les fonctions disponibles (remplies côté client via script.js)
 * - Liste les tables/colonnes/commentaires depuis la BDD (via ../config.php)
 * - Sans dépendance DB codée en dur
 */

// -------- README
$readmePath = __DIR__ . '/../README.md';
$readmeContent = is_file($readmePath)
  ? file_get_contents($readmePath)
  : "# README.md introuvable\n\nAssure-toi que le fichier **htdocs/README.md** existe.";

/**
 * Conversion Markdown → HTML minimaliste
 * ⚠️ Important : NE PAS échapper tout le document avec htmlspecialchars(),
 * sinon les chevrons < > deviennent illisibles hors des blocs <code>.
 */
function md_to_html(string $md): string {
  // 1) Blocs de code (```lang ... ```) — protégés et échappés
  $md = preg_replace_callback('/```([a-z0-9_-]*)\n([\s\S]*?)```/i', function($m){
    $lang = $m[1] ?: 'text';
    $code = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
    return "<pre><code class=\"lang-$lang\">$code</code></pre>";
  }, $md);

  // 2) Titres
  $md = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $md);
  $md = preg_replace('/^## (.+)$/m',  '<h2>$1</h2>', $md);
  $md = preg_replace('/^# (.+)$/m',   '<h1>$1</h1>', $md);

  // 3) Gras / italique
  $md = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $md);
  $md = preg_replace('/\*(.+?)\*/s',     '<em>$1</em>', $md);

  // 4) Code inline `...` — seulement le contenu est échappé
  $md = preg_replace_callback('/`([^`\n]+)`/', function($m){
    return '<code>'.htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8').'</code>';
  }, $md);

  // 5) Listes
  $md = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $md);
  $md = preg_replace_callback(
    '/(?:^|\n)(?:<li>.*<\/li>\n?)+/m',
    fn($m)=>"<ul>\n".trim($m[0])."\n</ul>\n",
    $md
  );

  // 6) Paragraphes (n’entoure pas les blocs déjà transformés)
  $parts = preg_split("/\n{2,}/", $md);
  foreach ($parts as &$p) {
    if (!preg_match('/^\s*<(h\d|pre|ul|li|code)/i', $p)) {
      $p = '<p>'.$p.'</p>';
    }
  }
  return implode("\n", $parts);
}

$readmeHtml = md_to_html($readmeContent);


// -------- Résumé style.css (côté PHP)
$cssPath = __DIR__ . '/../style/style.css';
$css = is_file($cssPath) ? file_get_contents($cssPath) : '';

function summarize_css(string $css): array {
  if ($css === '') return ['_error' => "style.css introuvable"];
  $has = fn(string $needle) => (strpos($css, $needle) !== false);

  // Thème & base
  $theme = [];
  if ($has(':root {') || $has(':root{')) $theme[] = "Palette dark par défaut via :root (variables --bg, --fg, --bd, --accent, --card-bg).";
  if ($has(':root[data-theme="light"]')) $theme[] = "Thème clair opt-in via :root[data-theme=\"light\"].";
  if ($has('color-scheme: dark')) $theme[] = "color-scheme: dark (et light dans le mode clair).";
  if ($has('font: 15px/1.5')) $theme[] = "Reset & base (font système, antialiasing, couleurs globales).";

  // Composants / layout
  $components = [];
  if ($has('.container'))      $components[] = "Layout responsive avec .container.";
  if ($has('.card') || $has('.panel')) $components[] = "Cartes & panneaux (.card, .panel) pour encadrer des sections.";
  if ($has('.projects-grid'))  $components[] = "Grille de projets (.projects-grid) auto-fill/minmax.";
  if ($has('.tag'))            $components[] = "Étiquettes (.tag) & boutons fantômes (.btn-ghost).";
  if ($has('.chip'))           $components[] = "Chips (.chip) et états (.chip-wip / .chip-ok / .chip-todo).";
  if ($has('.toolbar'))        $components[] = "Toolbar (.toolbar) pour filtres/actions.";
  if ($has('.list') || $has('.empty')) $components[] = "Listes “cards grid” (.list) + état vide (.empty).";
  if ($has('dialog.modal'))    $components[] = "Modales (dialog.modal) prêtes à l'emploi.";

  // Spécifiques hub
  $hub = [];
  if ($has('.project-title') || $has('.project-details')) $hub[] = ".project + .project-title/.project-details (carte projet compacte).";
  if ($has('.arborescence'))   $hub[] = "Bloc arborescence monospace (.arborescence).";
  if ($has('#checklist') || $has('.checklist')) $hub[] = "Checklists (#checklist, .checklist, marqueurs done/todo/in-progress).";

  // Ce que le style “prend en charge”
  $features = [];
  if ($has('.scrollbox')) $features[] = ['label'=>"Cadre scrollable pour README/doc",'selectors'=>".scrollbox",'why'=>"Max-height + overflow, idéal pour afficher markdown ou logs."];
  if ($has('.md-body'))   $features[] = ['label'=>"Rendu Markdown basique",'selectors'=>".md-body, .md-body pre, .md-body code",'why'=>"Titres/listes/code, prêt à l’emploi pour un lecteur `.md`."];
  if ($has('.card') || $has('.panel')) $features[] = ['label'=>"Panneaux & cartes de contenu",'selectors'=>".panel, .card",'why'=>"Encadrer des sections, padding cohérent."];
  if ($has('.projects-grid') || $has('.project')) $features[] = ['label'=>"Grille de projets & cartes projet",'selectors'=>".projects-grid, .project",'why'=>"Mise en page responsive des tuiles de projets."];
  if ($has('.chip') || $has('.chip-wip') || $has('.chip-ok') || $has('.chip-todo')) $features[] = ['label'=>"Chips + états de progression",'selectors'=>".chip, .chip-wip, .chip-ok, .chip-todo",'why'=>"Badges d’état réutilisables."];
  if ($has('#checklist') || $has('.checklist')) $features[] = ['label'=>"Checklists prêtes",'selectors'=>"#checklist, .checklist",'why'=>"Styles des cases, puces (✔ ➤ •) et espacements."];
  if ($has('dialog.modal')) $features[] = ['label'=>"Modales standardisées",'selectors'=>"dialog.modal, .modal header/footer/content, .grid2, .field",'why'=>"Fenêtres d’édition/formulaire uniformes."];
  if ($has('.toolbar')) $features[] = ['label'=>"Barres d’outils",'selectors'=>".toolbar, .toolbar .btn, input, select",'why'=>"Actions/filtres réutilisables."];
  if ($has('.list') || $has('.empty')) $features[] = ['label'=>"Listes en cartes + état vide",'selectors'=>".list, .empty",'why'=>"Grille générique multi-projets."];
  if ($has('.arborescence')) $features[] = ['label'=>"Bloc arborescence monospace",'selectors'=>".arborescence",'why'=>"Affichage d’arbre de fichiers, `pre-wrap`."];
  if ($has('.container') || $has('.site-header')) $features[] = ['label'=>"Layout de page",'selectors'=>".container, .site-header",'why'=>"Gabarit de largeur + en-tête réutilisable."];

  return [
    'theme'      => $theme,
    'components' => $components,
    'hub'        => $hub,
    'features'   => $features,
  ];
}
$cssSummary = summarize_css($css);


// -------- BDD : se brancher via ../config.php (SANS secrets en dur)

/**
 * Essaie de construire un PDO depuis la config projet.
 * Prend en charge plusieurs conventions possibles :
 *  - fonction utilitaire : get_db_pdo(): PDO
 *  - constantes : DB_DSN | (DB_HOST, DB_NAME, DB_USER, DB_PASS)
 *  - variables : $DB_DSN | $DB | $config['db'] | $CONFIG['db']
 */
function hub_get_pdo_from_config(): array {
  $pdo = null;
  $err = null;

  $configFile = __DIR__ . '/../config.php';
  if (!is_file($configFile)) {
    return [null, "config.php introuvable dans htdocs/"];
  }

  // Scope d'inclusion isolé
  $cfg = (function($file){
    /** @noinspection PhpIncludeInspection */
    return include $file;
  })($configFile);

  // 1) Si la config expose une fonction utilitaire
  if (function_exists('get_db_pdo')) {
    try { return [get_db_pdo(), null]; } catch (Throwable $e) { $err = $e->getMessage(); }
  }

  // 2) Constantes connues
  if (defined('DB_DSN')) {
    try { return [new PDO(DB_DSN, defined('DB_USER')?DB_USER:null, defined('DB_PASS')?DB_PASS:null, [
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    ]), null]; } catch (Throwable $e) { $err = $e->getMessage(); }
  }
  if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
    $host = DB_HOST; $name = DB_NAME; $user = DB_USER; $pass = defined('DB_PASS')?DB_PASS:'';
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    try { return [new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    ]), null]; } catch (Throwable $e) { $err = $e->getMessage(); }
  }

  // 3) Variables globales possibles (incluses par config.php)
  foreach ([
    // DSN direct
    function() {
      if (isset($GLOBALS['DB_DSN'])) {
        $user = $GLOBALS['DB_USER'] ?? null; $pass = $GLOBALS['DB_PASS'] ?? null;
        return ["dsn"=>$GLOBALS['DB_DSN'], "user"=>$user, "pass"=>$pass];
      }
      return null;
    },
    // Tableau $DB = ['dsn'=>..., 'user'=>..., 'pass'=>...]
    function() {
      if (isset($GLOBALS['DB']) && is_array($GLOBALS['DB'])) {
        $DB = $GLOBALS['DB'];
        if (!empty($DB['dsn'])) return ["dsn"=>$DB['dsn'], "user"=>$DB['user']??null, "pass"=>$DB['pass']??null];
        if (!empty($DB['host']) && !empty($DB['name'])) {
          $dsn = "mysql:host={$DB['host']};dbname={$DB['name']};charset=utf8mb4";
          return ["dsn"=>$dsn, "user"=>$DB['user']??null, "pass"=>$DB['pass']??null];
        }
      }
      return null;
    },
    // Tableaux $config['db'] / $CONFIG['db']
    function() {
      foreach (['config','CONFIG'] as $k) {
        if (isset($GLOBALS[$k]['db']) && is_array($GLOBALS[$k]['db'])) {
          $db = $GLOBALS[$k]['db'];
          if (!empty($db['dsn'])) return ["dsn"=>$db['dsn'], "user"=>$db['user']??null, "pass"=>$db['pass']??null];
          if (!empty($db['host']) && !empty($db['name'])) {
            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
            return ["dsn"=>$dsn, "user"=>$db['user']??null, "pass"=>$db['pass']??null];
          }
        }
      }
      return null;
    },
  ] as $resolver) {
    $res = $resolver();
    if ($res && !empty($res['dsn'])) {
      try {
        $pdo = new PDO($res['dsn'], $res['user'] ?? null, $res['pass'] ?? null, [
          PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        ]);
        return [$pdo, null];
      } catch (Throwable $e) { $err = $e->getMessage(); }
    }
  }

  return [null, $err ?: "Impossible de déterminer la configuration BDD depuis config.php"];
}

[$db, $dbError] = hub_get_pdo_from_config();

// Prépare la liste des tables si connexion OK
$tables = [];
if ($db instanceof PDO) {
  // Détecte automatiquement le schéma courant quand possible
  $dbname = null;
  try {
    $dbname = $db->query('SELECT DATABASE() AS db')->fetch()['db'] ?? null;
  } catch (Throwable $e) {}

  // Si pas de DB courante (cas DSN sans dbname), on tente le fallback via SQL_USER()
  if (!$dbname) {
    try {
      $dbnameRow = $db->query("SELECT SCHEMA() AS db")->fetch();
      $dbname = $dbnameRow['db'] ?? null;
    } catch (Throwable $e) {}
  }

  // Si toujours rien, on abandonne proprement (pas de schéma)
  if ($dbname) {
    $sqlTables = "
      SELECT TABLE_NAME, TABLE_COMMENT
      FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA = :schema
      ORDER BY TABLE_NAME
    ";
    $st = $db->prepare($sqlTables);
    $st->execute(['schema'=>$dbname]);
    $tables = $st->fetchAll();

    function hub_get_columns(PDO $db, string $schema, string $table): array {
      $sql = "
        SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table
        ORDER BY ORDINAL_POSITION
      ";
      $stmt = $db->prepare($sql);
      $stmt->execute(['schema'=>$schema, 'table'=>$table]);
      return $stmt->fetchAll();
    }

    // Mémorise le schéma pour l'HTML
    $GLOBALS['__HUB_SCHEMA__'] = $dbname;
  } else {
    $dbError = "Schéma (base) non détecté depuis la connexion courante.";
  }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Documentation — Projet htdocs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="../style/style.css" />
  <script src="../script/script.js" defer></script>
</head>
<body>
  <header class="site-header container">
    <h1>📖 Projet <code>htdocs</code></h1>
  </header>

  <main class="container">
    <!-- README dans une carte, scrollable -->
    <section class="panel">
      <h2>README du hub</h2>
      <div class="scrollbox">
        <div class="md-body">
          <?= $readmeHtml ?>
        </div>
      </div>
    </section>

    <!-- Résumé des grandes lignes du style global -->
    <section class="panel">
      <h2>🎨 style/style.css — grandes lignes & éléments prêts-à-l’emploi</h2>

      <?php if (isset($cssSummary['_error'])): ?>
        <p class="muted"><?= htmlspecialchars($cssSummary['_error'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <h3>Thème & base</h3>
        <ul class="checklist">
          <?php foreach ($cssSummary['theme'] as $li): ?>
            <li class="done"><?= htmlspecialchars($li, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; if (empty($cssSummary['theme'])): ?>
            <li class="todo muted">Aucun point relevé.</li>
          <?php endif; ?>
        </ul>

        <h3>Composants & layout</h3>
        <ul class="checklist">
          <?php foreach ($cssSummary['components'] as $li): ?>
            <li class="in-progress"><?= htmlspecialchars($li, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; if (empty($cssSummary['components'])): ?>
            <li class="todo muted">Aucun point relevé.</li>
          <?php endif; ?>
        </ul>

        <h3>Spécifiques hub</h3>
        <ul class="checklist">
          <?php foreach ($cssSummary['hub'] as $li): ?>
            <li class="todo"><?= htmlspecialchars($li, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; if (empty($cssSummary['hub'])): ?>
            <li class="todo muted">Aucun point relevé.</li>
          <?php endif; ?>
        </ul>

        <h3>Ce que le style <em>prend en charge</em> (réutilisable tel quel)</h3>
        <?php if (!empty($cssSummary['features'])): ?>
          <ul class="checklist">
            <?php foreach ($cssSummary['features'] as $feat): ?>
              <li class="done">
                <strong><?= htmlspecialchars($feat['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="muted">
                  — <?= htmlspecialchars($feat['why'], ENT_QUOTES, 'UTF-8') ?>
                  <br><code><?= htmlspecialchars($feat['selectors'], ENT_QUOTES, 'UTF-8') ?></code>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
          <p class="muted">Astuce : pour un nouveau projet, pars d’abord de ces blocs. Ex. lecteur Markdown → <code>.scrollbox</code> + <code>.md-body</code>.</p>
        <?php else: ?>
          <p class="muted">Aucun élément réutilisable détecté.</p>
        <?php endif; ?>
      <?php endif; ?>
    </section>

    <!-- Fonctions disponibles dans script.js (remplies côté client) -->
    <section class="panel">
      <h2>⚡ script/script.js — fonctions disponibles</h2>
      <ul class="checklist" id="js-functions">
        <li class="muted">Chargement en cours…</li>
      </ul>
      <p class="muted">Astuce : ajoute un commentaire JSDoc au-dessus de chaque fonction pour enrichir cette doc.</p>
    </section>

    <!-- Documentation automatique de la base de données -->
    <section class="panel">
      <h2>🗄️ Base de données<?= isset($GLOBALS['__HUB_SCHEMA__']) ? ' — '.htmlspecialchars($GLOBALS['__HUB_SCHEMA__']) : '' ?></h2>

      <?php if (!empty($dbError)): ?>
        <p class="muted">⚠️ Connexion BDD : <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="muted">Vérifie <code>htdocs/config.php</code> (DSN / host / user / pass) — rien n’est stocké ici.</p>
      <?php elseif (empty($tables)): ?>
        <p class="muted">Aucune table détectée (ou schéma non défini).</p>
      <?php else: ?>
        <?php foreach ($tables as $t): ?>
          <details>
            <summary>
              <strong><?= htmlspecialchars($t['TABLE_NAME']) ?></strong>
              <?php if (!empty($t['TABLE_COMMENT'])): ?>
                <span class="muted">— <?= htmlspecialchars($t['TABLE_COMMENT']) ?></span>
              <?php endif; ?>
            </summary>
            <div class="scrollbox" style="max-height:50vh;">
              <table class="list">
                <thead>
                  <tr>
                    <th>Colonne</th>
                    <th>Type</th>
                    <th>NULL</th>
                    <th>Clé</th>
                    <th>Défaut</th>
                    <th>Extra</th>
                    <th>Commentaire</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (hub_get_columns($db, $GLOBALS['__HUB_SCHEMA__'], $t['TABLE_NAME']) as $c): ?>
                    <tr>
                      <td><code><?= htmlspecialchars($c['COLUMN_NAME']) ?></code></td>
                      <td><?= htmlspecialchars($c['COLUMN_TYPE']) ?></td>
                      <td><?= htmlspecialchars($c['IS_NULLABLE']) ?></td>
                      <td><?= htmlspecialchars($c['COLUMN_KEY']) ?></td>
                      <td><?= htmlspecialchars((string)$c['COLUMN_DEFAULT']) ?></td>
                      <td><?= htmlspecialchars($c['EXTRA']) ?></td>
                      <td class="muted"><?= htmlspecialchars($c['COLUMN_COMMENT']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </details>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
