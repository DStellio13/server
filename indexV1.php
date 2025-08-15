<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Accueil - Mes Projets</title>
  <link rel="stylesheet" href="style/style.css" />
  <script src="script/script.js" defer></script>
</head>
<header style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;">
  <img src="assets/logo.svg" alt="Mes Projets Locaux" width="160" height="36">
</header>
<body>

  <h1>📁 Mes Projets Locaux</h1>

  <!-- Arborescence (sans détails arduino) -->
  <section class="arborescence" aria-label="Arborescence du dossier htdocs">
<pre>xampp\htdocs\
├── index.html
├── assets\               (présent)
├── arduino\              (présent)
├── cs_inventory\         (présent)
├── docs\                 (présent)
├── emploi\               (présent)
├── img\                  (présent)
├── inventaire\           (présent)
├── monitor\              (présent)
├── streaming\            (présent)
├── script\               (présent)
└── style\                (présent)
</pre>
  </section>

  <section id="projects" aria-label="Liste des projets">

    <!-- Présents physiquement -->
    <div class="project" data-target="emploi" tabindex="0" aria-expanded="false" aria-controls="emploi-details">
      <h2 class="project-title">💼 Projet Emploi</h2>
      <div class="project-details" id="emploi-details" hidden>
        <p>Centralisation de la recherche d’emploi (CV, lettres, suivi candidatures, objectifs).</p>
        <p><a href="emploi/index.php" target="_blank" rel="noopener noreferrer">Ouvrir</a></p>
        <ul class="checklist">
          <li class="todo">CV à jour</li>
          <li class="todo">Lettre générique + variantes (VBA / WMS / management)</li>
          <li class="todo">Suivi dynamique (JSON/SQL)</li>
        </ul>
      </div>
    </div>

    <div class="project" data-target="cs_inventory" tabindex="0" aria-expanded="false" aria-controls="cs_inventory-details">
      <h2 class="project-title">🎮 CS:GO Inventory</h2>
      <div class="project-details" id="cs_inventory-details" hidden>
        <p>Gestion locale de l’inventaire via JSON + API PHP, stats de prix.</p>
        <p><a href="cs_inventory/index.php" target="_blank" rel="noopener noreferrer">Ouvrir</a></p>
        <ul class="checklist">
          <li class="done">Structure + API OK</li>
          <li class="in-progress">Stats de prix</li>
          <li class="todo">UI/UX</li>
        </ul>
      </div>
    </div>
<div class="project" data-target="monitor" tabindex="0" aria-expanded="false" aria-controls="monitor-details">
<h2 class="project-title">
  <img src="assets/icons/project-monitor.svg" alt="" width="20" height="20" style="vertical-align:middle;margin-right:.4rem;">
  📡 Projet Monitor
</h2>
  <div class="project-details" id="monitor-details" hidden>
    <p>Description : Écoute des ports utilisés par NDI pour détecter des décalages de son ou de vidéo. 
       Analyse réalisée en Python (stocké hors serveur web). 
       Cette page permet de suivre l’avancement et de consulter les données JSON générées.</p>

    <p>
      <a href="monitor/V2/index.html" target="_blank" rel="noopener noreferrer">🚀 Accéder à la version V2 (actuelle)</a><br>
      <small style="color:gray;">Interface simple — en cours de développement</small>
    </p>
    <p>
      <a href="monitor/V1/index.php" target="_blank" rel="noopener noreferrer">📜 Voir l’ancienne version (V1)</a><br>
      <small style="color:gray;">Interface graphique historique (Chart.js)</small>
    </p>

    <ul class="checklist">
      <li class="todo">Scanner les ports NDI en Python</li>
      <li class="todo">Analyser les paquets pour extraire latence et drift</li>
      <li class="todo">Stocker les mesures en JSON</li>
      <li class="todo">Afficher les données en direct dans la V2</li>
    </ul>
  </div>
</div>

    <!-- Répertoires utilitaires présents -->
    <div class="project" data-target="docs" tabindex="0" aria-expanded="false" aria-controls="docs-details">
      <h2 class="project-title">📚 Documentation</h2>
      <div class="project-details" id="docs-details" hidden>
        <p>Docs et PDF (Arduino, CV, etc.).</p>
        <p><a href="docs/index.html" target="_blank" rel="noopener noreferrer">Ouvrir</a></p>
      </div>
    </div>

    <div class="project" data-target="assets" tabindex="0" aria-expanded="false" aria-controls="assets-details">
      <h2 class="project-title">🗂️ Assets</h2>
      <div class="project-details" id="assets-details" hidden>
        <p>Ressources communes (icônes, sons, images…).</p>
      </div>
    </div>

    <div class="project" data-target="arduino" tabindex="0" aria-expanded="false" aria-controls="arduino-details">
      <h2 class="project-title">🛠️ Arduino</h2>
      <div class="project-details" id="arduino-details" hidden>
        <p>Exemples et projets du kit capteurs. (Sous‑dossiers non listés ici.)</p>
      </div>
    </div>

    <div class="project" data-target="img" tabindex="0" aria-expanded="false" aria-controls="img-details">
      <h2 class="project-title">🖼️ Images</h2>
      <div class="project-details" id="img-details" hidden>
        <p>Dossier images.</p>
      </div>
    </div>

    <div class="project" data-target="inventaire" tabindex="0" aria-expanded="false" aria-controls="inventaire-details">
      <h2 class="project-title">📦 Inventaire (HTML)</h2>
      <div class="project-details" id="inventaire-details" hidden>
        <p>Page statique d’inventaire.</p>
        <p><a href="inventaire/index.html" target="_blank" rel="noopener noreferrer">Ouvrir</a></p>
      </div>
    </div>

    <!-- Projets non initialisés (dossier absent aujourd’hui) -->
    <div class="project" data-target="dashboard" tabindex="0" aria-expanded="false" aria-controls="dashboard-details">
      <h2 class="project-title">📊 Dashboard <em>(non initialisé)</em></h2>
      <div class="project-details" id="dashboard-details" hidden>
        <p>Dossier absent. Chemin prévu : <code>dashboard/</code>.</p>
        <p><a href="dashboard/index.php" aria-disabled="true" title="Dossier absent">index.php</a></p>
        <ul class="checklist">
          <li class="todo">Créer <code>dashboard/</code> + <code>index.php</code></li>
        </ul>
      </div>
    </div>

    <div class="project" data-target="guitare" tabindex="0" aria-expanded="false" aria-controls="guitare-missing-details">
      <h2 class="project-title">🎸 Guitare <em>(non initialisé)</em></h2>
      <div class="project-details" id="guitare-missing-details" hidden>
        <p>Dossier absent. Chemin prévu : <code>guitare/</code>.</p>
        <p><a href="guitare/index.html" aria-disabled="true" title="Dossier absent">index.html</a></p>
        <ul class="checklist">
          <li class="todo">Créer <code>guitare/</code> + <code>index.html</code></li>
        </ul>
      </div>
    </div>

    <div class="project" data-target="serveur" tabindex="0" aria-expanded="false" aria-controls="serveur-missing-details">
      <h2 class="project-title">🖥️ Serveur <em>(non initialisé)</em></h2>
      <div class="project-details" id="serveur-missing-details" hidden>
        <p>Dossier absent. Chemin prévu : <code>serveur/</code>.</p>
        <p><a href="serveur/index.html" aria-disabled="true" title="Dossier absent">index.html</a></p>
        <ul class="checklist">
          <li class="todo">Créer <code>serveur/</code> + <code>index.html</code></li>
        </ul>
      </div>
    </div>

    <div class="project" data-target="clavier_corsair" tabindex="0" aria-expanded="false" aria-controls="clavier-details">
      <h2 class="project-title">⌨️ Clavier Corsair <em>(non initialisé)</em></h2>
      <div class="project-details" id="clavier-details" hidden>
        <p>Dossier absent. Chemin prévu : <code>clavier_corsair/</code>.</p>
        <p><a href="clavier_corsair/index.html" aria-disabled="true" title="Dossier absent">index.html</a></p>
        <ul class="checklist">
          <li class="todo">Créer <code>clavier_corsair/</code> + <code>index.html</code></li>
        </ul>
      </div>
    </div>

    <div class="project" data-target="tasks" tabindex="0" aria-expanded="false" aria-controls="tasks-missing-details">
      <h2 class="project-title">📌 Tasks (MySQL) <em>(non initialisé)</em></h2>
      <div class="project-details" id="tasks-missing-details" hidden>
        <p>Dossier absent. Chemin prévu : <code>tasks/</code>.</p>
        <p><a href="tasks/index.php" aria-disabled="true" title="Dossier absent">index.php</a></p>
        <ul class="checklist">
          <li class="todo">Créer <code>tasks/</code> + <code>index.php</code></li>
        </ul>
      </div>
    </div>

    <!-- Checklist globale -->
    <div class="project" data-target="checklist" tabindex="0" aria-expanded="false" aria-controls="checklist-details">
      <h2 class="project-title">✅ Checklist Générale</h2>
      <div class="project-details" id="checklist-details" hidden>
        <section id="checklist" aria-label="Checklist globale pour l’interface index.html">
          <h1>Checklist pour le site index.html</h1>

          <section>
            <h2>1. Vérification des contenus projets</h2>
            <ul>
              <li><label><input type="checkbox" data-id="projets-liste" /> Valider la liste complète des projets</label></li>
              <li><label><input type="checkbox" data-id="projets-liens" /> Vérifier les liens (présent / non initialisé)</label></li>
              <li><label><input type="checkbox" data-id="index-par-projet" /> Créer une page index par projet</label></li>
            </ul>
          </section>

          <section>
            <h2>2. Amélioration UX / UI</h2>
            <ul>
              <li><label><input type="checkbox" data-id="ux-toggle" /> Toggle + accessibilité</label></li>
              <li><label><input type="checkbox" data-id="ux-responsive" /> Responsive</label></li>
            </ul>
          </section>
        </section>
      </div>
    </div>

  </section>

</body>
<!-- juste après <body> -->
<header class="site-header">
  <img src="assets/logo.svg" alt="Mes Projets Locaux" width="160" height="36">
  <div class="header-right">
    <span class="tag">local</span>
    <button id="compactToggle" class="btn-ghost" aria-pressed="true" title="Mode compact">↕</button>
  </div>
</header>

<!-- remplace le <section id="projects"...> d'origine par ceci : -->
<section id="projects" aria-label="Liste des projets" class="projects-grid">
  <!-- EXEMPLE sur quelques projets : on garde tes div.project et leur contenu -->
  <div class="project" data-target="monitor" tabindex="0" aria-expanded="false" aria-controls="monitor-details">
    <h2 class="project-title">
      <img src="assets/icons/project-monitor.svg" alt="" width="20" height="20" class="icon">
      📡 Projet Monitor
      <span class="chip chip-wip" title="En cours">⏳</span>
    </h2>
    <div class="project-details" id="monitor-details" hidden>
      <!-- contenu inchangé -->
    </div>
  </div>

  <div class="project" data-target="cs_inventory" tabindex="0" aria-expanded="false" aria-controls="cs_inventory-details">
    <h2 class="project-title">
      <img src="assets/icons/project-cs.svg" alt="" width="20" height="20" class="icon">
      🎮 Projet CS:GO Inventory
      <span class="chip chip-todo" title="À faire">•</span>
    </h2>
    <div class="project-details" id="cs_inventory-details" hidden>
      <!-- contenu inchangé -->
    </div>
  </div>

  <!-- laisse les autres .project tels quels, tu peux ajouter l’icône correspondante dans le <h2> -->
</section>
</html>
