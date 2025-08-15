<?php
// include minimal : <?php $projectSlug = $projectSlug ?? ($_GET['project'] ?? ''); include __DIR__.'/includes/quickbar.php';
$__qb_project = isset($projectSlug) ? (string)$projectSlug : (string)($_GET['project'] ?? '');
?>
<link rel="stylesheet" href="/includes/quickbar.css">
<div id="quickbar" data-project="<?= htmlspecialchars($__qb_project) ?>">
  <button class="qb-toggle" aria-expanded="false" aria-controls="qb-panel" title="Quickbar">＋</button>
  <div id="qb-panel" hidden>
    <div class="qb-row">
      <form id="qb-form-project" class="qb-form" autocomplete="off">
        <strong>Projet</strong>
        <input name="name" type="text" placeholder="Nom du projet" required>
        <input name="slug" type="text" placeholder="slug (optionnel)">
        <button type="submit">Ajouter</button>
      </form>

      <form id="qb-form-task" class="qb-form" autocomplete="off">
        <strong>Tâche</strong>
        <input name="title" type="text" placeholder="Titre" required>
        <input name="project_slug" type="text" placeholder="project_slug" value="<?= htmlspecialchars($__qb_project) ?>">
        <select name="status">
          <option value="todo">À faire</option>
          <option value="in_progress">En cours</option>
          <option value="done">Fait</option>
          <option value="blocked">Bloqué</option>
        </select>
        <input name="priority" type="number" min="1" max="5" value="3" title="Priorité (1 fort → 5 faible)">
        <button type="submit">Ajouter</button>
      </form>

      <form id="qb-form-item" class="qb-form" autocomplete="off">
        <strong>Item</strong>
        <input name="task_id" type="number" placeholder="ID tâche" required>
        <input name="title" type="text" placeholder="Titre de l’item" required>
        <button type="submit">Ajouter</button>
      </form>
    </div>
    <div class="qb-toast" role="status" aria-live="polite" hidden></div>
  </div>
</div>
<script src="/includes/quickbar.js" defer></script>
