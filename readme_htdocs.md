Documentation : Dossier htdocs/

Cette documentation centralise les informations principales concernant la structure, les objectifs et les bonnes pratiques du dossier racine htdocs/ utilisé en local avec XAMPP. Elle sert de référence pour maintenir une cohérence entre tous les projets et suivre leur évolution efficacement.

⚙️ Objectifs Généraux

Fournir un point d'accès unique vers l'ensemble des projets locaux via une page index.html.

Maintenir une organisation claire, modulable et homogène entre les projets.

Intégrer une checklist globale pour assurer le suivi de l'avancement des travaux.

Centraliser les ressources communes (CSS, JS, images) dans des dossiers dédiés.

📂 Arborescence du Dossier htdocs/
htdocs/
├── index.html                # Page d’accueil principale
├── style/                    # CSS globaux et spécifiques par projet
│   ├── style.css              # Style global
│   ├── monitor.css            # Style spécifique Monitor
│   ├── cs_inventory.css       # Style spécifique CS Inventory
│   └── <projet>.css           # Autres styles spécifiques
├── script/                   # JS globaux et spécifiques par projet
│   ├── script.js              # JS global
│   ├── monitor.js             # JS spécifique Monitor
│   ├── cs_inventory.js        # JS spécifique CS Inventory
│   └── <projet>.js            # Autres scripts spécifiques
├── assets/                   # Ressources statiques communes
│   ├── icons/                 # Icônes SVG/PNG
│   ├── img/                   # Images
│   ├── media/                 # Vidéos / sons
│   ├── monitor/               # Ressources propres à Monitor
│   ├── cs_inventory/          # Ressources propres à CS Inventory
│   └── <projet>/              # Autres sous-dossiers projets si besoin
├── cs_inventory/
│   └── index.php              # Interface Inventaire CS:GO
├── dashboard/
│   └── index.php              # Interface centrale
├── guitare/
│   └── index.html             # Gestion vidéos guitare
├── streaming/
│   └── index.html             # Organisation planning & stream
├── serveur/
│   └── index.html             # Gestion serveur local
├── monitor/
│   └── index.php              # Monitoring réseau/audio/vidéo
├── clavier_corsair/
│   └── index.html             # Gestion périphériques
├── tasks/
│   └── index.php              # Gestion des tâches MySQL
├── readme_htdocs.md           # Documentation globale
├── styleguide_global.md       # Conventions CSS/JS/HTML
├── Arborescence.txt           # Capture brute de l'arborescence physique
└── favicon.ico                # Icône du site

🔹 Conventions de chargement

CSS : charger style/style.css en premier, puis <projet>.css si besoin.
# 📖 README — Dossier htdocs/

## 🎯 Rôle du dossier

Le dossier `htdocs/` est la racine du serveur local (XAMPP).  
Il sert de **hub central** pour tous les projets personnels.

Il a deux fonctions principales :

1. **Page d’accueil (`index.html` / `index.php`)**  
   → Liste l’ensemble des projets disponibles avec leur état (initialisé ou non).  
   → Sert de point d’entrée unique au serveur local.

2. **Projet interne `htdocs/`**  
   → Le hub est lui-même un projet à part entière.  
   → Il présente les tâches, sous-tâches et évolutions futures.  
   → Il peut contenir des sous-projets dynamiques, gérés par la base de données.

---

## 📂 Arborescence (vue simplifiée)

```
htdocs/   ← racine du serveur local (XAMPP)
├── index.html / index.php       # Hub principal (accueil)
├── htdocs/                      # Projet interne du hub
│   └── index.php                # Page informative (tâches, sous-projets, roadmap)
├── style/                       # CSS global + spécifiques
│   ├── style.css
│   └── <project>.css
├── script/                      # JS global + spécifiques
│   ├── script.js
│   └── <project>.js
├── assets/                      # Ressources statiques communes
│   ├── icons/
│   ├── img/
│   └── media/
├── <project>/                   # Un dossier par projet (cs_inventory, emploi, monitor…)
│   └── index.html / index.php
├── includes/                    # Configs & utilitaires (quickbar, DB, etc.)
│   └── core/
│       ├── config.php           # Config centrale unique
│       ├── config.local.php     # Secrets (hors Git)
│       └── config.example.php   # Template versionné
├── readme_htdocs.md             # Documentation du hub
├── styleguide_global.md         # Conventions globales
└── favicon.ico
```

---

## ⚙️ Conventions globales

### 🔗 Configuration
- **Tous les projets utilisent la même config centrale :**
  ```
  includes/core/config.php
  ```
- **Aucun `config.php` local** dans les projets (`htdocs/<projet>/`).  
- `db()` est toujours fourni par le core.

### 🎨 Chargement CSS
- Toujours charger :
  ```html
  <link rel="stylesheet" href="../style/style.css" />
  ```
- Puis, si besoin, un fichier spécifique :
  ```html
  <link rel="stylesheet" href="../style/<PROJECT>.css" />
  ```
- **Obligation : commenter clairement les fichiers CSS**  
  → en-tête (contexte global/projet)  
  → sections (thème, composants, utilitaires…)  
- Plus de détails : [`readme_htdocs.md`](./readme_htdocs.md)

### ⚡ Chargement JS
- Toujours charger :
  ```html
  <script src="../script/script.js" defer></script>
  ```
- Puis, si besoin, un fichier spécifique :
  ```html
  <script src="../script/<PROJECT>.js" defer></script>
  ```
- **Obligation : commenter les fonctions JS**  
  → rôle, événements écoutés, extension possible  
- Plus de détails : [`readme_htdocs.md`](./readme_htdocs.md)

### 📁 Ressources
- Icônes, images, médias : toujours dans `assets/` (sous-dossiers si nécessaire).  

---

## 🗂️ Gestion dynamique
- Le projet `htdocs/` interne gère la **documentation dynamique** :  
  → tâches globales, sous-projets, état d’avancement.  
- La base de données peut être utilisée pour **créer/mettre à jour les projet# 📖 README — Dossier htdocs/

## 🎯 Rôle du dossier

Le dossier `htdocs/` est la racine du serveur local (XAMPP).  
Il sert de **hub central** pour tous les projets personnels.

Il a deux fonctions principales :

1. **Page d’accueil (`index.html` / `index.php`)**  
   → Liste l’ensemble des projets disponibles avec leur état (initialisé ou non).  
   → Sert de point d’entrée unique au serveur local.

2. **Projet interne `htdocs/`**  
   → Le hub est lui-même un projet à part entière.  
   → Il présente les tâches, sous-tâches et évolutions futures.  
   → Il peut contenir des sous-projets dynamiques, gérés par la base de données.

---

## 📂 Arborescence (vue simplifiée)

```
htdocs/   ← racine du serveur local (XAMPP)
├── index.html / index.php       # Hub principal (accueil)
├── htdocs/                      # Projet interne du hub
│   └── index.php                # Page informative (tâches, sous-projets, roadmap)
├── style/                       # CSS global + spécifiques
│   ├── style.css
│   └── <project>.css
├── script/                      # JS global + spécifiques
│   ├── script.js
│   └── <project>.js
├── assets/                      # Ressources statiques communes
│   ├── icons/
│   ├── img/
│   └── media/
├── <project>/                   # Un dossier par projet (cs_inventory, emploi, monitor…)
│   └── index.html / index.php
├── includes/                    # Configs & utilitaires (quickbar, DB, etc.)
│   └── core/
│       ├── config.php           # Config centrale unique
│       ├── config.local.php     # Secrets (hors Git)
│       └── config.example.php   # Template versionné
├── readme_htdocs.md             # Documentation du hub
├── styleguide_global.md         # Conventions globales
└── favicon.ico
```

---

## ⚙️ Conventions globales

### 🔗 Configuration
- **Tous les projets utilisent la même config centrale :**
  ```
  includes/core/config.php
  ```
- **Aucun `config.php` local** dans les projets (`htdocs/<projet>/`).  
- `db()` est toujours fourni par le core.

### 🎨 Chargement CSS
- Toujours charger :
  ```html
  <link rel="stylesheet" href="../style/style.css" />
  ```
- Puis, si besoin, un fichier spécifique :
  ```html
  <link rel="stylesheet" href="../style/<PROJECT>.css" />
  ```
- **Obligation : commenter clairement les fichiers CSS**  
  → en-tête (contexte global/projet)  
  → sections (thème, composants, utilitaires…)  
- Plus de détails : [`readme_htdocs.md`](./readme_htdocs.md)

### ⚡ Chargement JS
- Toujours charger :
  ```html
  <script src="../script/script.js" defer></script>
  ```
- Puis, si besoin, un fichier spécifique :
  ```html
  <script src="../script/<PROJECT>.js" defer></script>
  ```
- **Obligation : commenter les fonctions JS**  
  → rôle, événements écoutés, extension possible  
- Plus de détails : [`readme_htdocs.md`](./readme_htdocs.md)

### 📁 Ressources
- Icônes, images, médias : toujours dans `assets/` (sous-dossiers si nécessaire).  

---

## 🗂️ Gestion dynamique
- Le projet `htdocs/` interne gère la **documentation dynamique** :  
  → tâches globales, sous-projets, état d’avancement.  
- La base de données peut être utilisée pour **créer/mettre à jour les projets et checklists**.

---

## 🛡️ Bonnes pratiques
- Pas d’accents ni d’espaces dans les noms de fichiers.  
- Utiliser le **kebab-case** pour les CSS/JS :
  ```
  <PROJECT>.css
  <PROJECT>.js
  ```
- Les IDs et attributs HTML doivent être synchronisés :
  ```html
  <div class="project" data-target="<PROJECT>" aria-controls="<PROJECT>-details">
      <div id="<PROJECT>-details">...</div>
  </div>
  ```

---

## ✍️ Résumé

`htdocs/` est à la fois la **racine du serveur local** et un **projet interne** qui documente et centralise tous les autres.  
Sa structure est claire, normalisée et extensible, grâce à :  
- une configuration **unique et centralisée** (`includes/core/config.php`),  
- une organisation CSS/JS **commentée et cohérente**,  
- une gestion dynamique possible via base de données.  

---

Mise à jour : Août 2025s
 et checklists**.

---

## 🛡️ Bonnes pratiques
- Pas d’accents ni d’espaces dans les noms de fichiers.  
- Utiliser le **kebab-case** pour les CSS/JS :
  ```
  <PROJECT>.css
  <PROJECT>.js
  ```
- Les IDs et attributs HTML doivent être synchronisés :
  ```html
  <div class="project" data-target="<PROJECT>" aria-controls="<PROJECT>-details">
      <div id="<PROJECT>-details">...</div>
  </div>
  ```

---

## ✍️ Résumé

`htdocs/` est à la fois la **racine du serveur local** et un **projet interne** qui documente et centralise tous les autres.  
Sa structure est claire, normalisée et extensible, grâce à :  
- une configuration **unique et centralisée** (`includes/core/config.php`),  
- une organisation CSS/JS **commentée et cohérente**,  
- une gestion dynamique possible via base de données.  

---

Mise à jour : Août 2025

JS : charger script/script.js en premier, puis <projet>.js si besoin.

Images, icônes, médias : stockées uniquement dans assets/ (éventuellement avec sous-dossiers par projet).

🔹 Projets Inclus & Objectifs Spécifiques

(Garde la section telle qu’elle était pour décrire chaque projet.)

📅 Convention de nommage

Pas d’accents ni d’espaces.

Noms en kebab-case (nom-projet.css).

IDs et attributs HTML synchronisés avec le nom du dossier (data-target="monitor" / id="monitor-details").

💭 Améliorations futures

Export checklist globale en JSON.

Page de documentation des classes CSS et composants JS.

README.md par projet.

Interface MySQL pour édition dynamique des tâches.

Mise à jour : Août 2025