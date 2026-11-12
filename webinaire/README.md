# WebinairePro — Plateforme de Webinaires

Application web de gestion de webinaires avec architecture **3-tiers**, pattern **DAO**, exceptions personnalisées et interface SPA Tailwind CSS.

---

## 🏗️ Architecture standardisée

Le projet suit désormais la structure de référence du skill, avec une seule architecture principale backend/frontend et aucun CSS personnalisé.

```
webinaire/
├── backend/
│   ├── api/
│   │   ├── themes.php
│   │   └── sessions.php
│   ├── classes/
│   │   ├── Theme.php
│   │   └── Session.php
│   ├── dao/
│   │   ├── ThemeDAO.php
│   │   ├── SessionDAO.php
│   │   ├── JsonDAO.php
│   │   └── DAOInterface.php
│   ├── data/
│   │   ├── themes.json
│   │   └── sessions.json
│   ├── services/
│   │   ├── ThemeService.php
│   │   ├── SessionService.php
│   │   └── BaseService.php
│   └── README.md
│
├── frontend/
│   ├── index.html
│   └── js/
│       ├── api.js
│       ├── themes.js
│       └── sessions.js
│
├── autoload.php
├── index.html
├── README.md
├── prompt.md
└── .agents/
```

---

## 🚀 Lancement

> **Prérequis** : PHP 8.0 ou supérieur

```bash
# Se placer à la racine du projet
cd webinaire

# Démarrer le serveur de développement PHP
php -S localhost:8000 -t .
```

Ouvrir ensuite dans le navigateur :

```
http://localhost:8000
```

---

## 🔌 API REST

### Thèmes

| Méthode | URL                        | Corps JSON           | Réponse                       |
|---------|----------------------------|----------------------|-------------------------------|
| GET     | `/api/themes.php`          | —                    | `{ success, data: [...] }`    |
| GET     | `/api/themes.php?id=N`     | —                    | `{ success, data: {...} }`    |
| POST    | `/api/themes.php`          | `{ libelle }`        | `{ success, message, data }`  |
| PUT     | `/api/themes.php?id=N`     | `{ libelle }`        | `{ success, message, data }`  |
| DELETE  | `/api/themes.php?id=N`     | —                    | `{ success, message }`        |

### Sessions

| Méthode | URL                           | Corps JSON                                                                 | Réponse                      |
|---------|-------------------------------|----------------------------------------------------------------------------|------------------------------|
| GET     | `/api/sessions.php`           | —                                                                          | `{ success, data: [...] }`   |
| GET     | `/api/sessions.php?id=N`      | —                                                                          | `{ success, data: {...} }`   |
| GET     | `/api/sessions.php?theme=N`   | —                                                                          | `{ success, data: [...] }`   |
| POST    | `/api/sessions.php`           | `{ titre, description, date_session, heure_debut, duree, lien_visio, id_theme }` | `{ success, message, data }` |
| PUT     | `/api/sessions.php?id=N`      | idem POST                                                                  | `{ success, message, data }` |
| DELETE  | `/api/sessions.php?id=N`      | —                                                                          | `{ success, message }`       |

### Format des erreurs

```json
{
  "success": false,
  "message": "Données invalides.",
  "errors": [
    "Le titre doit contenir au moins 2 caractères.",
    "La durée doit être un entier entre 1 et 480 minutes."
  ]
}
```

---

## 🧱 Exceptions personnalisées

| Classe                  | HTTP | Levée par       | Cas d'usage                                           |
|-------------------------|------|-----------------|-------------------------------------------------------|
| `AppException`          | 500  | —               | Classe mère, catch générique dans les contrôleurs     |
| `ValidationException`   | 422  | Service         | Champs vides, format invalide, valeur hors plage      |
| `NotFoundException`     | 404  | Service         | `findById()` retourne null                            |
| `DataAccessException`   | 500  | DAO (JsonDAO)   | Fichier illisible, non inscriptible, JSON corrompu    |
| `BusinessRuleException` | 409  | Service         | Suppression d'un thème avec des sessions liées        |

---

## ✅ Règles de validation

### Theme
- `libelle` : obligatoire, 2–100 caractères

### Session
- `titre`        : obligatoire, 2–200 caractères
- `description`  : obligatoire, non vide
- `date_session` : format `YYYY-MM-DD` valide
- `heure_debut`  : format `HH:MM` valide (00:00–23:59)
- `duree`        : entier 1–480 (minutes)
- `lien_visio`   : URL `http://` ou `https://`
- `id_theme`     : entier > 0, référence un thème **existant** (vérification cross-DAO)

---

## 🔒 Règle métier

La suppression d'un **thème** est bloquée (`HTTP 409`) si des sessions lui sont encore associées.  
Le message renvoyé précise le nombre de sessions bloquantes.

---

## ⚙️ Principes de conception

| Principe                      | Implémentation                                                              |
|-------------------------------|-----------------------------------------------------------------------------|
| **Séparation des couches**    | Présentation (api/ + public/) / Métier (Services/) / Données (DAO/)        |
| **Injection de dépendances**  | Contrôleur instancie DAO → Service (manuelle, sans conteneur IoC)           |
| **Aucune logique dans le DAO**| JsonDAO lit/écrit JSON ; validation et règles = Services uniquement         |
| **Aucune logique dans l'API** | Contrôleurs PHP : lire requête → appeler service → retourner JSON           |
| **Verrouillage fichier**      | `flock(LOCK_EX)` en écriture, `flock(LOCK_SH)` en lecture                  |
| **Protection XSS**            | `esc()` dans app.js avant tout innerHTML                                    |
| **PSR-12**                    | `declare(strict_types=1)`, types scalaires, noms explicites en français     |
