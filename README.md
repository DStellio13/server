# Projet server (htdocs)

Suite de pages et outils locaux (XAMPP) avec **CSS/JS globaux**, **configs centralisées** et **checklists projets**.

## � Stack
- **Serveur**: XAMPP (Apache, PHP, MariaDB)
- **Code**: PHP/HTML/CSS/JS
- **OS cible**: Windows
- **Git**: GitHub (`main`)

---

## � Organisation

### Globaux
- `style/` → `style.css` global + `<projet>.css`
- `script/` → `script.js` global + `<projet>.js`
- `assets/` → images, icônes, médias

### Projets
Chaque projet a son dossier (`cs_inventory/`, `emploi/`, `monitor/`…), avec son `index.php`/`index.html`.  
Les pages chargent **d’abord** `style/style.css` et `script/script.js`, puis les fichiers spécifiques.

### Configs **centralisées**
- Routeur: `includes/core/projects.php`
  - `getProjectConfig('nom')` → array config
  - `projectDb('nom')` → `PDO`
- **Shims** (compat):
  - `cs_inventory/includes/config.php` et `emploi/includes/config.php` **retournent** la config centrale.
- **Secrets réels (gitignorés)**:
  - `includes/core/config.php`
  - `includes/core/projects/<projet>.php`
- **Exemples versionnés**:
  - `includes/core/config.example.php`
  - `includes/core/projects/*.example.php`

### Sécurité
- `includes/.htaccess` bloque l’accès HTTP direct.
- Secrets **hors Git** via `.gitignore`.

---

## � Utilisation rapide (PDO)

```php
<?php
require_once __DIR__ . '/../includes/core/projects.php';
$pdo = projectDb('cs_inventory'); // ou 'emploi'
$stmt = $pdo->query('SELECT 1');
echo $stmt->fetchColumn();
