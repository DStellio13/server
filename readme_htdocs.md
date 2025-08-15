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