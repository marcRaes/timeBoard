# 🕒 TimeBoard — Journal des changements

Toutes les modifications notables de ce projet sont documentées dans ce fichier.  
Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/),  
et ce projet adhère au principe de [Versionnement sémantique](https://semver.org/lang/fr/).

## [v1.5.1] - 2025-10-25
### 🐞 Corrigé
- Suppression de l’affichage redondant des erreurs du formulaire d'envoi de la fiche d'heure.
- Correction de l’affichage HTML des erreurs du formulaire d'envoi de la fiche d'heure.
- Correction du mappage du champ `type` pour l'édition de la journée de travail

---

## [v1.5.0] - 2025-09-10
### 🧪 Ajouté
- Ajout de la première couverture de tests unitaires avec PHPUnit.
- Début de la mise en place de la stratégie TDD sur le projet.

---

## [v1.4.1] - 2025-08-23
### 🐞 Corrigé
- Correction du mapping du champ `type` dans l’édition des journées de travail.

---

## [v1.4.0] - 2025-08-23
### 🧩 Modifié
- Mise à jour du schéma UML de TimeBoard avec le champ `type` dans `WorkPeriod`.
- Ajustements internes liés à la nouvelle structure de données.

---

## [v1.3.1] - 2025-07-24
### 🐞 Corrigé
- Correction du calcul du temps de travail dans les feuilles de temps.

---

## [v1.3.0] - 2025-06-26
### ✨ Ajouté
- Changement d’orientation des feuilles de calcul pour une meilleure lisibilité.
- Amélioration des règles de calcul des pauses.

---

## [v1.2.0] - 2025-06-21
### ✨ Ajouté
- Ajout d’un bouton de renvoi du mail de confirmation de compte.
- Amélioration de la gestion de l’authentification (login / logout / confirmation).

---

## [v1.1.0] - 2025-06-15
### 🔧 Modifié
- Renommage complet des fichiers Twig en `snake_case` pour cohérence et maintenabilité.
- Refactorisation légère du design des pages.

---

## [v1.0.1] - 2025-06-15
### 🐞 Corrigé
- Correction d’un bug d’affichage des erreurs dans les formulaires.

---

## [v1.0.0] - 2025-06-14
### 🚀 Première version stable
- Base fonctionnelle complète du projet TimeBoard.
- Affichage et gestion des journées de travail.
- Système d’authentification et de création de compte.
- Export des feuilles de temps au format Excel/PDF.
- Intégration de Bootstrap 5 et mise à jour vers Symfony 7.3.
- Ajout du fichier `README.md`.

---

## 🧭 À venir
### 🚧 Prévu pour la version 2.0.0
- Ajout d’une page **Profil utilisateur** pour gérer ses informations personnelles.
- Possibilité de **personnaliser la signature** des emails envoyés.
- Personnalisation du **corps du message** lors de la transmission des feuilles de temps.
- Amélioration du **système de tests unitaires** et **augmentation de la couverture de code**.
