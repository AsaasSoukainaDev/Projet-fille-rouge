# Conventions et Patterns d'Architecture (Vanilla N-Tiers SoliCode)

Ce document décrit les règles et conventions de conception de l'architecture mise en œuvre dans le projet `liveCoding_Sprint_3`. Cette architecture garantit une clarté pédagogique optimale, une absence de dépendance à un framework lourd, et une scalabilité naturelle vers N classes.

---

## 1. Vue d'Ensemble des Couches

```
Navigateur (index.html + assets/js/app.js)
                  │
        [fetch JSON / HTTP]
                  ▼
         backend/api/api.php
                  │
        [Appel Méthodes Métier]
                  ▼
      backend/services/*Service.php (extends BaseService)
                  │
         [Manipulation Objets]
                  ▼
         backend/dao/*DAO.php
                  │
         [file_get / file_put]
                  ▼
        backend/data/*.json
                  ▲
                  │  (Instanciation & Typage)
        backend/classes/*.php
```

---

## 2. Conventions de la Couche Modèle (`backend/classes/`)

- **Rôle** : Représenter l'entité pure (Plain Old PHP Object).
- **Règles** :
  - Propriétés `private` avec typage strict (`int`, `string`, `float`, `bool`).
  - L'identifiant `$id` est toujours de type `int`.
  - Constructeur initialisant tous les attributs requis.
  - Getters typés pour chaque propriété (`getId(): int`, `getNom(): string`).
  - Aucune logique de persistance, aucun appel SQL ou JSON, aucune dépendance externe dans cette classe.

Exemple :
```php
class Genre {
    private int $id;
    private string $nom;
    private string $description;

    public function __construct(int $id, string $nom, string $description) {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getDescription(): string { return $this->description; }
}
```

---

## 3. Conventions de la Couche DAO (`backend/dao/`)

- **Rôle** : Gérer la persistance et l'accès direct aux fichiers JSON.
- **Règles** :
  - Un fichier DAO par entité (`GenreDAO.php`, `FilmDAO.php`).
  - Constante ou propriété statique privée `$dataFile = "../data/[Entities].json"`.
  - Méthode `AfficherTous(): array` :
    - Lit le fichier via `file_get_contents`.
    - Décode avec `json_decode(..., true)`.
    - Boucle pour instancier des objets entités (`new Genre(...)`).
    - Retourne une liste d'objets métier.
  - Méthode `ajouter(...)` :
    - Valide les entrées (`trim($champ) == "" -> throw new Error(...)`).
    - Calcule l'ID : `$id = count($items) + 1` ou `max(id) + 1`.
    - Instancie la nouvelle entité et l'ajoute à la liste.
    - Transforme les objets en tableaux associatifs.
    - Sauvegarde avec `file_put_contents(self::$dataFile, json_encode($data, JSON_PRETTY_PRINT))`.

---

## 4. Conventions de la Couche Service (`backend/services/`)

- **Rôle** : Orchestrer le métier, valider les règles applicatives, formater pour l'API et gérer les codes HTTP.
- **Règles** :
  - Une classe mère abstraite `BaseService.php` commune à tous les services.
  - Chaque service (`[Entity]Service.php`) hérite de `BaseService`.
  - Le constructeur instancie le DAO correspondant (`$this->genreDao = new GenreDAO();`).
  - `lister[Entities]()` :
    - Appelle le DAO.
    - Mappe les objets vers un tableau associatif propre destiné à `json_encode`.
  - `ajouter[Entity]()` :
    - Récupère la charge utile JSON via `json_decode(file_get_contents("php://input"), true)`.
    - Exécute le traitement dans un bloc `try ... catch (Error $e)`.
    - En cas de succès : `http_response_code(200); return ["success" => true, "message" => "..."];`.
    - En cas d'erreur : `http_response_code(500); return ["success" => false, "message" => $e->getMessage()];`.

---

## 5. Conventions de la Couche API (`backend/api/`)

- **Rôle** : Point d'entrée HTTP public.
- **Règles** :
  - En-tête obligatoire : `header("Content-Type: application/json");`.
  - Récupération de la méthode HTTP : `$methode = $_SERVER["REQUEST_METHOD"];`.
  - Routage simple selon la méthode (`GET`, `POST`, `PUT`, `DELETE`).
  - Envoi direct de la réponse encodée : `echo json_encode(...);`.

---

## 6. Conventions du Frontend (Tailwind + Vanilla JS)

### Structure Visuelle (`index.html`) :
- Mise en page "Dashboard moderne" en flexbox plein écran (`flex h-screen bg-gray-50`).
- Sidebar sombre (`bg-gray-900 text-white`) avec navigation par entités.
- Header blanc avec sous-titre de section et badge utilisateur.
- Zone centrale scrollable (`overflow-y-auto p-6`) avec conteneur centré (`max-w-5xl mx-auto space-y-8`).
- Formulaire pliable/dépliable (`#section-form` avec classe Tailwind `hidden`).
- Tableau épuré avec en-têtes grisés, texte semi-gras et bordures fines (`divide-y divide-gray-100`).
- Conteneur de notification Toast positionné en `fixed top-5 right-5 z-[9999]`.

### Comportement Client (`assets/js/app.js`) :
- **Sélection des éléments DOM** déclarée au sommet du fichier.
- **`Loading(isLoading)`** : bloque le bouton submit et passe son texte à `"En cours ..."`.
- **`Toast(message, type="success")`** : crée un toast coloré (vert `bg-green-500`, rouge `bg-red-500`), anime l'opacité et le supprime après 3 secondes.
- **`Afficher()`** : fait un `fetch` en GET, vide le `tbody`, génère les lignes `<tr>` dynamiquement.
- **`DOMContentLoaded`** : lance `Afficher()`, écoute l'événement `submit` du formulaire, prévient le rechargement, envoie en POST avec `JSON.stringify()`, rafraîchit la table et notifie.
- **Boutons Ouvrir / Annuler** : ajout/suppression de la classe `hidden` sur la section du formulaire.

