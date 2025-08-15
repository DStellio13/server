# 📡 Projet Monitor

Surveillance en temps réel de la latence, du jitter et des désynchronisations audio/vidéo entre le PC de jeu et le PC de stream.

---

## 🎯 Objectif

Ce projet simule (et plus tard mesurera en temps réel) l’état du flux NDI utilisé entre deux machines. Il permet d’afficher dynamiquement :

- la latence réseau,
- le jitter,
- le nombre de frames perdues,
- le décalage audio,
- l’état du flux (actif/inactif).

Un graphique interactif (via Chart.js) affiche l’évolution de ces métriques.

---

## 📂 Fichiers

monitor/
├── index.php # Page principale avec checklist intégrée
├── monitor.css # Style dédié (thème sombre, blocs info, responsive)
├── monitor.js # Simulation JS + graphique + logs
└── data/
└── history.json # (Prévu) Historique des mesures réelles


---

## ⚙️ Fonctionnalités actuelles

- ✅ Simulation toutes les secondes des métriques NDI
- ✅ Affichage dynamique des valeurs
- ✅ Graphique Chart.js à mise à jour en temps réel
- ✅ État du flux visuel (🟢 / 🔴)
- ✅ Boutons de diagnostic :
  - `Lancer un test` → simulateur de progression avec log
  - `Analyser la synchro` → affiche une analyse simulée
- ✅ Style sombre avec visuels clairs

---

## 🚧 Prochaines étapes

- [ ] Export CSV / JSON des données collectées
- [ ] Ajout d’un bouton “reset” du graphe
- [ ] Lecture réelle des métriques NDI (via backend ou script externe)
- [ ] Responsive design complet
- [ ] Améliorations accessibilité (ARIA, clavier, etc.)
- [ ] Documentation interne (commentaires JS + PHP si back)

---

## 💡 Idées à venir

- Bouton de *sauvegarde instantanée* (JSON local ou push vers `data/history.json`)
- Mode diagnostic étendu (ping NDI, perte paquets…)
- Intégration avec le projet `dashboard/` pour suivi centralisé

---

## 🛠️ Dépendances

- [Chart.js](https://www.chartjs.org/) (inclus via CDN)

---

## ✍️ Auteur

Projet personnel local. Géré avec XAMPP dans le dossier `htdocs/monitor/`.

---
