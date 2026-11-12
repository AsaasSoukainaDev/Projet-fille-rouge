# Guide de Gestion Multi-Entités (N Classes)

Ce guide détaille comment étendre l'architecture observée dans `liveCoding_Sprint_3` à n'importe quel nombre d'entités (1, 2, 5, 10+ classes) tout en conservant exactement la même simplicité d'écriture et de compréhension.

---

## 1. Principe de Modularité

Chaque nouvelle entité ajoutée au projet suit la même règle de symétrie :

| Couche | Nom du fichier pour l'entité `Film` | Nom du fichier pour l'entité `Genre` |
|---|---|---|
| **Modèle** | `backend/classes/Film.php` | `backend/classes/Genre.php` |
| **Persistance** | `backend/data/Films.json` | `backend/data/Genres.json` |
| **DAO** | `backend/dao/FilmDAO.php` | `backend/dao/GenreDAO.php` |
| **Service** | `backend/services/FilmService.php` | `backend/services/GenreService.php` |
| **API** | `backend/api/film_api.php` *(ou routeur central)* | `backend/api/api.php` |

---

## 2. Gestion des Relations (Clés Étrangères)

### A. Modélisation de l'Entité dépendante
Lorsqu'une entité est reliée à une autre (par exemple, un `Film` appartient à un `Genre`) :
- Dans la classe `Film` : ajouter la propriété `$genreId` (entier) et optionnellement `$genreNom` (chaîne) pour faciliter l'affichage.

```php
// backend/classes/Film.php
class Film {
    private int $id;
    private string $titre;
    private int $duree;
    private int $genreId;

    public function __construct(int $id, string $titre, int $duree, int $genreId) {
        $this->id = $id;
        $this->titre = $titre;
        $this->duree = $duree;
        $this->genreId = $genreId;
    }

    public function getId(): int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function getDuree(): int { return $this->duree; }
    public function getGenreId(): int { return $this->genreId; }
}
```

### B. Jointure simple en mémoire dans le Service
Pour afficher le nom du genre dans le tableau des films sans alourdir le DAO :
```php
// backend/services/FilmService.php
require_once "../dao/FilmDAO.php";
require_once "../dao/GenreDAO.php";
require_once "BaseService.php";

class FilmService extends BaseService {
    private FilmDAO $filmDao;
    private GenreDAO $genreDao;

    public function __construct() {
        $this->filmDao = new FilmDAO();
        $this->genreDao = new GenreDAO();
    }

    public function listerFilms(): array {
        $films = $this->filmDao->AfficherTous();
        $genres = $this->genreDao->AfficherTous();

        // Indexation rapide des noms de genres par ID
        $genreMap = [];
        foreach ($genres as $g) {
            $genreMap[$g->getId()] = $g->getNom();
        }

        $data = [];
        foreach ($films as $film) {
            $data[] = [
                "id" => $film->getId(),
                "titre" => $film->getTitre(),
                "duree" => $film->getDuree(),
                "genreId" => $film->getGenreId(),
                "genreNom" => $genreMap[$film->getGenreId()] ?? "Non défini"
            ];
        }
        return $data;
    }
}
```

---

## 3. Stratégies de Routage d'API pour N Entités

Deux approches simples et élégantes sont possibles :

### Approche 1 : Un fichier API par entité (Recommandé pour débuter)
- `backend/api/genres.php`
- `backend/api/films.php`
- `backend/api/acteurs.php`

Chaque fichier reste ultra-court (10 lignes) et totalement indépendant.

### Approche 2 : Un routeur centralisé par ressource
- `backend/api/api.php?resource=genres`
- `backend/api/api.php?resource=films`

```php
<?php
header("Content-Type: application/json");
$resource = $_GET["resource"] ?? "";
$method = $_SERVER["REQUEST_METHOD"];

switch ($resource) {
    case "genres":
        require_once "../services/GenreService.php";
        $service = new GenreService();
        if ($method == "GET") echo json_encode($service->listerGenres());
        if ($method == "POST") echo json_encode($service->ajouterGenre());
        break;

    case "films":
        require_once "../services/FilmService.php";
        $service = new FilmService();
        if ($method == "GET") echo json_encode($service->listerFilms());
        if ($method == "POST") echo json_encode($service->ajouterFilm());
        break;

    default:
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Ressource introuvable"]);
}
?>
```

---

## 4. Frontend Multi-Vues avec Tailwind CSS

Dans `index.html` :
1. **Sidebar** : Créer un lien par entité avec attributs `data-target="section-[entity]"` :
   ```html
   <a href="#genres" class="nav-link block px-4 py-2 rounded text-gray-400 hover:bg-gray-800 hover:text-white" data-view="view-genres">Genres</a>
   <a href="#films" class="nav-link block px-4 py-2 rounded text-gray-400 hover:bg-gray-800 hover:text-white" data-view="view-films">Films</a>
   ```

2. **Sections Vues** : Chaque entité a son propre bloc conteneur `<div id="view-[entity]" class="view-section">` masquable avec la classe `hidden`.

3. **Select dynamique pour les relations** :
   Dans le formulaire de l'entité dépendante (`#form-film`) :
   ```html
   <label for="film-genre" class="block text-sm font-semibold text-gray-700">Genre</label>
   <select id="film-genre" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
       <option value="">Sélectionner un genre</option>
   </select>
   ```

4. **Alimentation dynamique du `<select>` en JS** :
   ```javascript
   function ChargerSelectGenres() {
       fetch("backend/api/genres.php")
           .then(res => res.json())
           .then(genres => {
               const select = document.getElementById("film-genre");
               select.innerHTML = '<option value="">Sélectionner un genre</option>';
               genres.forEach(g => {
                   const opt = document.createElement("option");
                   opt.value = g.id;
                   opt.textContent = g.nom;
                   select.appendChild(opt);
               });
           });
   }
   ```

