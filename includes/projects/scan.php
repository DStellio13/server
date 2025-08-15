<?php
function scan_and_sync_projects(): array {
  $pdo = db();
  $base = __DIR__ . '/../';
  $dir = new DirectoryIterator($base);
  $inserted = 0; $updated = 0;

  foreach ($dir as $f) {
    if ($f->isDot() || !$f->isDir()) continue;
    $slug = $f->getFilename();
    // ignore dossiers système
    if (in_array($slug, ['includes','style','script','assets','.git'])) continue;

    // Existe en BDD ?
    $stmt = $pdo->prepare("SELECT id, entry_path FROM projects WHERE slug = ?");
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Détecte index
    $entry = null;
    if (is_file($base.$slug.'/index.php'))  $entry = $slug.'/index.php';
    elseif (is_file($base.$slug.'/index.html')) $entry = $slug.'/index.html';

    if (!$row) {
      // Insert minimal
      $ins = $pdo->prepare("INSERT INTO projects (slug, name, order_index, entry_path) VALUES (?,?,?,?)");
      $ins->execute([$slug, ucfirst(str_replace('_',' ',$slug)), 100, $entry]);
      $inserted++;
    } else {
      if ($entry && $entry !== ($row['entry_path'] ?? '')) {
        $up = $pdo->prepare("UPDATE projects SET entry_path = ? WHERE id = ?");
        $up->execute([$entry, $row['id']]);
        $updated++;
      }
    }
  }

  return ['inserted'=>$inserted,'updated'=>$updated];
}
