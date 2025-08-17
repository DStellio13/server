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

Mise à jour : Août 2025
