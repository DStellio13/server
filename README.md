# 📖 README — Dossier `htdocs/`

## 🎯 Rôle du dossier

Le dossier **`htdocs/` est la racine du serveur local (XAMPP)**.  
Il sert de **hub central** pour tous les projets personnels.  

Il a deux fonctions principales :

- **Page d’accueil** (`index.html` / `index.php`)  
  → Liste l’ensemble des projets disponibles avec leur état (initialisé ou non).  
  → Sert de point d’entrée unique au serveur local.  

- **Projet `htdocs`** (interne)  
  → Le hub est lui-même un projet à part entière.  
  → Il présente les **tâches, sous-tâches et évolutions futures**.  
  → Il peut contenir des **sous-projets dynamiques**, gérés par la base de données.

---

## 📂 Arborescence (vue simplifiée)

```text
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
├── <project>/                   # Un dossier par projet (cs_inventory, monitor, etc.)
│   └── index.html / index.php
├── includes/                    # Configs & utilitaires (quickbar, DB, etc.)
├── readme_htdocs.md             # Documentation du hub
├── styleguide_global.md         # Conventions globales
└── favicon.ico
```

---

## ⚙️ Conventions globales

### 🔗 Configuration

- Tous les projets utilisent **la même config globale** :  
  ```text
  includes/core/config.php
  ```
- Override optionnel par projet :  
  ```text
  includes/core/projects/<project>.php
  ```

### 🎨 Chargement CSS

- Toujours charger :  
  ```html
  <link rel="stylesheet" href="../style/style.css" />
  ```
- Puis, si besoin, un fichier spécifique :  
  ```html
  <link rel="stylesheet" href="../style/<PROJECT>.css" />
  ```

### ⚡ Chargement JS

- Toujours charger :  
  ```html
  <script src="../script/script.js" defer></script>
  ```
- Puis, si besoin, un fichier spécifique :  
  ```html
  <script src="../script/<PROJECT>.js" defer></script>
  ```

### 📁 Ressources

- Plus de détails sur le index du projet htdocs

---

## 🗂️ Gestion dynamique

- Plus de détails sur le index du projet htdocs

---

## 🛡️ Bonnes pratiques

- **Pas d’accents ni d’espaces** dans les noms de fichiers.  
- Utiliser le **kebab-case** pour les fichiers CSS/JS :  
  - `<PROJECT>.css`  
  - `<PROJECT>.js`  
- Les IDs et attributs HTML doivent être synchronisés :  
  ```html
  <div class="project" data-target="<PROJECT>" aria-controls="<PROJECT>-details">
      <div id="<PROJECT>-details">...</div>
  </div>
  ```

---

✍️ **Résumé** :  
`htdocs/` est à la fois **la racine du serveur local** et **un projet interne** qui documente et centralise tous les autres.  
Sa structure est **claire, normalisée et extensible**, grâce à une configuration globale et une gestion dynamique en base de données.
