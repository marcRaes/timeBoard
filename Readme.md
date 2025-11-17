# TimeBoard
[![License: PolyForm Noncommercial 1.0.0](https://img.shields.io/badge/license-PolyForm%20Noncommercial%201.0.0-blue.svg)](https://polyformproject.org/licenses/noncommercial/1.0.0/)

**TimeBoard** est une application web de gestion des feuilles de temps développée en Symfony 7.3.  
Elle simplifie la saisie, la génération et la transmission des fiches d’heures mensuelles.

---

## Fonctionnalités

- Saisie et édition des journées de travail
- Organisation des feuilles de temps par mois et année
- Calcul automatique des heures totales mensuelles
- Génération de rapports au format Excel et PDF à partir de templates
- Transmission des fiches par email
- Interface responsive optimisée (Bootstrap 5, Stimulus, Turbo)

---

## Technologies utilisées

- PHP 8.2+
- Symfony 7.3
- Doctrine ORM
- Bootstrap 5
- Stimulus & Turbo (Hotwired)
- PhpSpreadsheet (génération Excel / PDF)
- Symfony Mailer
- Docker (environnement de développement)
- MySQL (base de données)

---

## Objectifs du projet

- Simplifier la gestion des heures de travail personnelles ou professionnelles
- Proposer une interface responsive et moderne utilisable sur tout type d'appareil
- Mettre en pratique les bonnes pratiques Symfony : architecture propre, DRY, SRP
- Servir de support à l'apprentissage de Docker, Symfony avancé et des principes DDD

---

## Diagramme UML

Le schéma suivant illustre le modèle de données utilisé :

![Diagramme UML de TimeBoard](docs/uml/timeboard-schema.png)

> Les sources du diagramme sont disponibles dans le répertoire `docs/uml/`.

---

# Accès à l'application
<a href="https://timeboard-demo.marcraes.fr" target="_blank" rel="noopener noreferrer">https://timeboard-demo.marcraes.fr</a>

---

## Licence

**TimeBoard** est distribué sous la **[PolyForm Noncommercial License 1.0.0](https://polyformproject.org/wp-content/uploads/2020/05/PolyForm-Noncommercial-1.0.0.txt)**.  
© 2025 Marc Raes – All rights reserved.

Cette licence autorise :
- l’utilisation du logiciel à des fins **personnelles, éducatives, académiques ou de recherche** ;
- la consultation et l’étude du code source.

Elle interdit :
- tout usage **commercial, professionnel ou institutionnel** ;
- toute prestation ou service impliquant une **rémunération directe ou indirecte**.

Le logiciel est fourni “tel quel”, sans garantie.  
Toute violation des termes met fin immédiatement au droit d’utilisation.

> Les **collectivités, entreprises ou associations** souhaitant un usage professionnel  
> peuvent contacter l’auteur pour obtenir une **licence commerciale dédiée**

---

## Informations SPDX

- **License ID :** `PolyForm-Noncommercial-1.0.0`
- **SPDX URL :** [https://spdx.org/licenses/PolyForm-Noncommercial-1.0.0.html](https://spdx.org/licenses/PolyForm-Noncommercial-1.0.0.html)
