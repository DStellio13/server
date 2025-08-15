# Projet server (htdocs)

Dashboard et projets locaux (XAMPP) avec centralisation des configs, conventions CSS/JS globales et outillage de base.

## ⚙️ Stack rapide
- **Serveur** : XAMPP (Apache + PHP + MariaDB)
- **Code** : PHP de base, HTML/CSS/JS
- **Gestion** : Git (GitHub)
- **OS cible** : Windows (PowerShell)

---

## � Structure & conventions

### Racine
- `index.php` : Accueil (liste des projets)
- `style/` : **CSS global** (`style.css`) + CSS spécifiques (`<projet>.css`)
- `script/` : **JS global** (`script.js`) + JS spécifiques (`<projet>.js`)
- `assets/` : ressources statiques (images, icônes, médias)

### Projets
Chaque projet a un dossier dédié (`cs_inventory/`, `emploi/`, `monitor/`, …) avec sa page `index.php`/`index.html`.
Les projets **chargent d’abord** `style/style.css` et `script/script.js`, puis leurs fichiers spécifiques.

### Centralisation des configs (important)
- **Routeur** : `includes/core/projects.php` expose :
  - `getProjectConfig('nom')` → array de config
  - `projectDb('nom')` → PDO connecté
- **Secrets réels (gitignorés)** :
  - `includes/core/config.php`
  - `includes/core/projects/<projet>.php`
- **Shims** (compatibilité) :
  - `cs_inventory/includes/config.php` et `emploi/includes/config.php` **retournent** la config centrale.

### Sécurité
- `includes/.htaccess` **bloque** l’accès HTTP direct à tout `includes/`.
- Les fichiers de config **réels** sont **exclus de Git** via `.gitignore`.

---

## � Utilisation des configs (exemple)

Dans `cs_inventory/index.php` (ou un script du projet) :
```php
<?php
require_once __DIR__ . '/../includes/core/projects.php';
$pdo = projectDb('cs_inventory');
