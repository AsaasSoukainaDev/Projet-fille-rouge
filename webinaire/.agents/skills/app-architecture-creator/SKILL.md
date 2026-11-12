---
name: app-architecture-creator
description: "À utiliser pour concevoir, structurer, coder ou étendre une application web complète (frontend et backend) selon l'architecture N-tiers légère Vanilla PHP / DAO JSON / Tailwind CSS / Vanilla JS observée à SoliCode, quel que soit le nombre de classes ou d'entités (1, 2 ou N classes). Permet de créer un nouveau projet ou d'ajouter une entité à un projet existant avec la même simplicité."
---

# App Architecture Creator

Ce skill permet de concevoir, développer et étendre des applications web basées sur l'architecture N-tiers légère sans framework, inspirée du projet `liveCoding_Sprint_3`. Il garantit un code pédagogique, propre, typé et immédiatement fonctionnel, quel que soit le nombre d'entités du projet.

## Quand utiliser ce skill

- Créer une nouvelle application web complète (Frontend Tailwind + Backend PHP POO + persistance JSON).
- Ajouter une ou plusieurs nouvelles classes/entités à un projet existant en respectant la même séparation en couches.
- Structurer un CRUD rapide, modulaire et lisible pour des apprenants ou prototypes techniques.
- Mettre en place les relations entre entités (1-N, N-N) de manière simple sans ORM ni configuration complexe.

Ne pas utiliser pour :
- Des architectures basées sur des frameworks imposés (Laravel, Symfony, React, Angular, Vue).
- Des applications nécessitant obligatoirement une base SQL relationnelle avec migrations, sauf demande explicite de migration du DAO JSON vers PDO/MySQL.

---

## Architecture de Référence

Le projet s'organise selon 6 couches bien distinctes :

```
mon-projet/
├── index.html                  # Interface SPA moderne (Tailwind CDN, Dashboard layout, Toast, Formulaire masquable)
├── assets/
│   └── js/
│       └── app.js              # Logique client asynchrone (Fetch, Loading state, Toasts, DOM dynamisé)
└── backend/
    ├── api/
    │   └── api.php             # Point d'entrée HTTP (JSON headers, verbes GET/POST, routage)
    ├── classes/
    │   └── [Entity].php        # Modèle / Entité POPO (propriétés privées typées, constructeur, getters)
    ├── dao/
    │   └── [Entity]DAO.php     # Persistance JSON (lecture/écriture, incrément d'ID, mapping, validation)
    ├── data/
    │   └── [Entities].json     # Fichier de données au format JSON
    └── services/
        ├── BaseService.php     # Classe mère abstraite pour standardiser les services
        └── [Entity]Service.php # Service métier (orchestration DAO, codes HTTP 200/500, try/catch, mapping API)
```

---

## Workflow d'Exécution

### Étape 1 : Cadrage des Entités et Relations
1. Lister chaque entité avec ses attributs et leurs types scalaires (`int`, `string`, `float`, `bool`).
2. Identifier les clés primaires (toujours `int $id`) et les clés étrangères éventuelles (`int $genreId`, `int $categorieId`).
3. Déterminer la stratégie d'API :
   - Pour 1 seule entité : `backend/api/api.php`.
   - Pour 2 ou N entités : soit un endpoint dédié par ressource (`backend/api/[entities].php`), soit un routeur unifié (`backend/api/api.php?resource=[entities]`). Consulter [references/multi-entites-guide.md](./references/multi-entites-guide.md).

### Étape 2 : Initialisation de l'Arborescence
Créer les dossiers nécessaires si inexistants :
- `backend/classes/`
- `backend/dao/`
- `backend/services/`
- `backend/api/`
- `backend/data/`
- `assets/js/`

### Étape 3 : Création de la Couche Modèle (`backend/classes/`)
Pour chaque entité, générer la classe en s'appuyant sur [resources/EntityTemplate.php](./resources/EntityTemplate.php) :
- Propriétés privées et strictement typées.
- Constructeur avec affectation directe.
- Getters pour chaque propriété.
- Ne jamais injecter de logique de stockage ou de base de données dans cette classe.

### Étape 4 : Création du Fichier de Données (`backend/data/`)
Initialiser un fichier `backend/data/[Entities].json` contenant un tableau JSON vide (`[]`) ou quelques données de démonstration :
```json
[
  {
    "id": 1,
    "nom": "Exemple",
    "description": "Donnée initiale"
  }
]
```

### Étape 5 : Création de la Couche DAO (`backend/dao/`)
Générer le DAO pour chaque entité à partir de [resources/DAOTemplate.php](./resources/DAOTemplate.php) :
- Propriété statique pointant vers le fichier JSON correspondant (`private static string $dataFile = "../data/[Entities].json";`).
- Méthode `AfficherTous()` : lit avec `file_get_contents`, décode, instancie chaque objet et renvoie la collection.
- Méthode `ajouter(...)` :
  - Vérifie que les champs obligatoires ne sont pas vides (`if (trim($champ) == "") throw new Error("...");`).
  - Calcule l'ID : `$id = count($items) + 1` (ou `max + 1`).
  - Ajoute l'objet à la liste.
  - Réécrit le fichier avec `file_put_contents(self::$dataFile, json_encode($data, JSON_PRETTY_PRINT))`.

### Étape 6 : Création de la Couche Service (`backend/services/`)
1. S'assurer que `BaseService.php` est présent à l'aide de [resources/BaseServiceTemplate.php](./resources/BaseServiceTemplate.php).
2. Créer le service spécifique avec [resources/ServiceTemplate.php](./resources/ServiceTemplate.php) :
   - Hérite de `BaseService`.
   - Instancie le DAO dans le constructeur.
   - Méthode de listing retournant un tableau associatif propre.
   - Méthode d'ajout lisant `php://input` avec bloc `try...catch (Error $e)` :
     - Succès : `http_response_code(200)` et `["success" => true, "message" => "..."]`.
     - Erreur : `http_response_code(500)` et `["success" => false, "message" => $e->getMessage()]`.

### Étape 7 : Création de la Couche API (`backend/api/`)
Créer l'endpoint REST avec [resources/ApiTemplate.php](./resources/ApiTemplate.php) :
- Définit `header("Content-Type: application/json");`.
- Lit `$_SERVER["REQUEST_METHOD"]`.
- Traite `GET` pour lister et `POST` pour ajouter.

### Étape 8 : Création de l'Interface Utilisateur (`index.html`)
Créer ou compléter `index.html` à partir de [resources/indexTemplate.html](./resources/indexTemplate.html) :
- Inclure Tailwind CSS via CDN avec palette `primary` personnalisée.
- Insérer le conteneur Toast fixe (`#toast-container`).
- Configurer la sidebar avec un lien par entité.
- Intégrer le tableau d'affichage des enregistrements.
- Intégrer le formulaire masqué par défaut avec la classe `hidden`.

### Étape 9 : Création du Script JavaScript Client (`assets/js/app.js`)
Développer ou enrichir `app.js` à l'aide de [resources/appTemplate.js](./resources/appTemplate.js) :
- Déclarer les sélecteurs DOM.
- Implémenter `Loading(isLoading)` pour gérer l'état du bouton d'envoi.
- Implémenter `Toast(message, type)` pour des retours visuels élégants.
- Implémenter `Afficher()` pour consommer l'API via `fetch` et construire les lignes du tableau.
- Gérer l'événement `submit` du formulaire (envoi JSON asynchrone, réinitialisation, rechargement du tableau).
- Lier les boutons d'ouverture et d'annulation du formulaire (`classList.remove("hidden")` / `classList.add("hidden")`).

### Étape 10 : Évolution et Multi-Entités
Lorsque le projet comporte plusieurs classes (ex: `Film` dépendant de `Genre`) :
- Suivre scrupuleusement [references/multi-entites-guide.md](./references/multi-entites-guide.md).
- Renseigner les champs clés étrangères.
- Alimenter dynamiquement les listes déroulantes `<select>` via un appel `fetch` à l'API de l'entité liée.
- Réaliser les jointures de libellés directement dans le Service pour garder le DAO ultra-simple.

---

## Règles d'Or de Simplicité

1. **Zéro dépendance complexe** : Pas de Composer requis, pas d'ORM lourd, pas de bundler JS (Webpack/Vite). Le projet doit tourner avec une simple commande `php -S localhost:8000`.
2. **Nommage clair et unifié** : Utiliser des noms de méthodes explicites (`AfficherTous`, `ajouter`, `listerGenres`).
3. **Typage strict en PHP** : Spécifier le type de chaque propriété et paramètre pour prévenir les bugs.
4. **Validation explicite** : Toujours valider les entrées côté DAO ou Service avant d'écrire dans le fichier JSON.
5. **Feedback utilisateur systématique** : Chaque action doit fournir un retour immédiat via un toast et un état de chargement.

---

## Validation et Contrôles

Avant de livrer une application générée :
1. Vérifier la validité syntaxique de chaque fichier PHP (`php -l chemin/fichier.php`).
2. Vérifier que chaque fichier JSON de `backend/data/` contient un JSON valide (`[]` au minimum).
3. Vérifier les chemins relatifs des `require_once` (`../dao/...`, `../classes/...`, `BaseService.php`).
4. Tester le bon chargement des styles Tailwind et la réactivité du script `app.js`.

