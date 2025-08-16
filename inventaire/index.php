<?php /* inventaire/index.php */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="x-project-slug" content="inventaire" />
  <title>Inventaire — Liste des articles</title>

  <!-- CSS global puis spécifique projet -->
  <link rel="stylesheet" href="../style/style.css" />
  <link rel="stylesheet" href="../style/inventaire.css" />

  <!-- JS global puis spécifique projet -->
  <script src="../script/script.js" defer></script>
  <script src="../script/inventaire.js" defer></script>
</head>

<body>
  <div class="container">
    <!-- En-tête + outils (IDs alignés avec script/inventaire.js) -->
    <section class="header">
      <div class="title">
        <h1>📦 Inventaire</h1>
        <span class="chip">Total: <strong id="inv-count">0</strong></span>
      </div>
      <div class="toolbar">
        <input id="inv-search" placeholder="Rechercher..." />
        <select id="inv-filter-category" aria-label="Filtrer par catégorie">
          <option value="">Toutes</option>
          <option value="arduino">arduino</option>
          <option value="capteur">capteur</option>
          <option value="outil">outil</option>
          <option value="autre">autre</option>
        </select>
        <select id="inv-sort" aria-label="Trier">
          <option value="name.asc">Nom (A→Z)</option>
          <option value="name.desc">Nom (Z→A)</option>
          <option value="created_at.desc">Récents d’abord</option>
          <option value="created_at.asc">Anciens d’abord</option>
        </select>
        <button id="inv-add" class="btn">+ Ajouter</button>
      </div>
    </section>

    <!-- Liste dynamique -->
    <ul id="inv-list" class="list"></ul>
    <div id="inv-empty" class="empty" hidden>Aucun article.</div>

    <p class="muted" style="margin-top:16px">
      Astuce : utilise la recherche et les filtres pour retrouver rapidement un article.
    </p>
  </div>

  <!-- Modal d’édition (utilisable plus tard si tu branches l’édition) -->
  <dialog id="inv-drawer" class="modal" aria-hidden="true">
    <header><h3>Modifier l’article</h3></header>
    <form id="inv-form" method="dialog">
      <div class="content">
        <div class="grid2">
          <div class="field">
            <label for="f_name">Nom</label>
            <input id="f_name" name="name" required />
          </div>
          <div class="field">
            <label for="f_category">Catégorie</label>
            <input id="f_category" name="category" placeholder="arduino, capteur, outil..." />
          </div>
        </div>
        <div class="grid2">
          <div class="field">
            <label for="f_code">Code produit</label>
            <input id="f_code" name="product_code" />
          </div>
          <div class="field">
            <label for="f_version">Version</label>
            <input id="f_version" name="version" />
          </div>
        </div>
        <div class="grid2">
          <div class="field">
            <label for="f_location">Emplacement</label>
            <input id="f_location" name="location" placeholder="étagère, boîte, etc." />
          </div>
          <div class="field">
            <label for="f_functional">Fonctionnel</label>
            <select id="f_functional" name="is_functional">
              <option value="1">Oui</option>
              <option value="0">Non</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label for="f_notes">Notes</label>
          <textarea id="f_notes" name="notes" rows="4"></textarea>
        </div>
        <div class="field">
          <label for="f_photo">Photo (chemin relatif)</label>
          <input id="f_photo" name="photo" placeholder="arduino/kit/lcd1602.jpg" />
        </div>
        <input type="hidden" name="id" />
      </div>
      <footer>
        <button id="inv-drawer-close" class="btn" value="cancel">Annuler</button>
        <button class="btn primary" id="saveEdit" value="default">Enregistrer</button>
        <button type="button" id="inv-delete" class="btn">Supprimer</button>
      </footer>
    </form>
    <div id="inv-drawer-backdrop" style="display:none"></div>
  </dialog>
</body>
</html>
