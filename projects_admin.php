<?php
require_once __DIR__.'/includes/config.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['slug'],$_POST['name'])) {
  $stmt=$pdo->prepare("INSERT INTO projects(slug,name,emoji,description,icon_path,order_index,is_active)
                       VALUES(?,?,?,?,?,?,1)
                       ON DUPLICATE KEY UPDATE name=VALUES(name), emoji=VALUES(emoji), description=VALUES(description),
                                               icon_path=VALUES(icon_path), order_index=VALUES(order_index)");
  $stmt->execute([
    trim($_POST['slug']), trim($_POST['name']),
    $_POST['emoji']??null, $_POST['description']??null,
    $_POST['icon_path']??null, (int)($_POST['order_index']??0)
  ]);
  header('Location: projects_admin.php'); exit;
}
if (isset($_GET['toggle'])) {
  $pdo->prepare("UPDATE projects SET is_active=1-is_active WHERE id=?")->execute([(int)$_GET['toggle']]);
  header('Location: projects_admin.php'); exit;
}
$rows=$pdo->query("SELECT * FROM projects ORDER BY order_index,name")->fetchAll();
?>
<!doctype html><meta charset="utf-8"><title>Admin Projets</title>
<link rel="stylesheet" href="style/style.css">
<h1>Admin Projets</h1>
<form method="post" style="display:grid;gap:.5rem;max-width:560px">
  <input name="slug" placeholder="slug (ex: monitor)" required>
  <input name="name" placeholder="Nom affiché" required>
  <input name="emoji" placeholder="Emoji (ex: 📡)">
  <input name="icon_path" placeholder="Chemin icône (ex: assets/icons/project-monitor.svg)">
  <input name="order_index" type="number" placeholder="Ordre (ex: 10)" value="0">
  <textarea name="description" placeholder="Description courte"></textarea>
  <button>Enregistrer</button>
</form>
<hr>
<table border="1" cellpadding="6" style="margin-top:10px;border-collapse:collapse">
  <tr><th>ID</th><th>Slug</th><th>Nom</th><th>Actif</th><th>Ordre</th><th>Actions</th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= htmlspecialchars($r['slug']) ?></td>
      <td><?= htmlspecialchars($r['name']) ?></td>
      <td><?= (int)$r['is_active'] ?></td>
      <td><?= (int)$r['order_index'] ?></td>
      <td><a href="?toggle=<?= (int)$r['id'] ?>"><?= $r['is_active']?'Désactiver':'Activer' ?></a></td>
    </tr>
  <?php endforeach; ?>
</table>
