# 📚 Convention & Structure du Projet `htdocs/`

Ce fichier sert de guide de cohérence pour tous les projets personnels contenus dans le dossier `htdocs/`. Il définit :

- l'organisation des fichiers,
- les conventions CSS/JS/HTML,
- les règles de nommage,
- les comportements communs (checklists, sections pliables, etc.),
- et les composants de base utilisés dans l'ensemble du site local.

---

## 📁 Arborescence standard

```
htdocs/
├── index.html             # Page d'accueil listant tous les projets
├── style/                 # Fichiers CSS globaux et spécifiques
│   └── style.css          # Style global pour tous les projets
├── script/                # Scripts JS globaux et spécifiques
│   └── script.js          # JS global : toggle, clavier, accessibilité
├── assets/                # Fichiers statiques communs (images, icônes, etc.)
├── cs_inventory/          # Projet 1 : Gestion d'inventaire CS:GO
│   └── index.html         # Page propre au projet
├── dashboard/             # Projet 2 : Interface centrale
├── guitare/               # Projet 3 : Timeline de guitare
├── streaming/             # Projet 4 : Timeline stream / overlay OBS / chatbot
├── serveur/               # Projet 5 : Serveur local & développement
├── monitor/               # Projet 6 : Monitoring réseau/audio/vidéo
└── clavier_corsair/       # Projet 7 : Gestion de périphériques
```

Chaque dossier de projet contient :

- `index.html` ou `index.php`
- Une checklist dédiée
- Un lien accessible depuis `htdocs/index.html`

---

## 🎨 Conventions CSS

Fichier global : `style/style.css`

### Classes principales :

- `.project` : bloc contenant un projet ou une section pliable
- `.project-title` : titre visible cliquable
- `.project-details` : contenu pliable/expandable
- `.checklist` : liste d'avancement par `li`
  - `.done`         ✅
  - `.in-progress`  ⏩
  - `.todo`         •

### Checklist flottante

- `.floating-checklist` : utilisée uniquement pour la checklist générale dans `index.html` (haut à droite)

---

## ⚙️ Comportement JS commun (`script/script.js`)

Le script applique :

- Le *toggle* (déplier/replier) des projets et checklists.
- La gestion clavier (entrée ou espace).
- L’attribut `aria-expanded` pour l’accessibilité.

### Attributs requis dans le HTML :

```html
<div class="project" data-target="mon_id" tabindex="0" aria-expanded="false" aria-controls="mon_id-details">
  <h2 class="project-title">Titre</h2>
  <div class="project-details" id="mon_id-details" hidden>...</div>
</div>
```

- `data-target` et `id` doivent être synchronisés : `mon_id`
- `aria-expanded` change selon l'état

---

## 📄 Checklists par projet

Chaque `index.html` de projet contient une checklist locale avec la même structure.

### Structure :

```html
<ul>
  <li><label><input type="checkbox" data-id="identifiant" /> Tâche à effectuer</label></li>
</ul>
```

- Utiliser `data-id` unique pour chaque case à cocher
- Grouper les tâches en `section` avec `h2`

---

## 📘 Règles générales

- Tous les liens vers les projets doivent être relatifs et corrects : `href="../projet/index.html"`
- Tous les projets doivent avoir une `index.html` (ou `index.php`) avec leur propre checklist visible.
- La page `htdocs/index.html` sert de hub central.

---

## 🛠️ Prochaine amélioration prévue

- Intégration AJAX pour chargement dynamique
- Sauvegarde locale des checkboxes via `localStorage`
- Ajout d’un vrai menu responsive
- Ajout d’un README.md par projet

---

Dernière mise à jour : 2025-07-14

