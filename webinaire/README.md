# 🎙️ Plateforme de Webinaires

Application web de gestion de webinaires développée en **PHP orienté objet** avec stockage **JSON** (sans base de données).

---

## 📁 Arborescence du projet

```
webinaire/
│
├── frontend/                   ← 🖥️ CÔTÉ CLIENT (navigateur)
│   ├── index.html              → Interface SPA (Tailwind CSS)
│   ├── css/
│   │   └── style.css           → Styles complémentaires
│   └── js/
│       ├── api.js              → Couche Fetch API générique
│       ├── themes.js           → Logique front — Thèmes
│       └── sessions.js         → Logique front — Sessions
│
├── backend/                    ← ⚙️ CÔTÉ SERVEUR (PHP)
│   ├── models/
│   │   ├── Theme.php           → Entité Thème (POO)
│   │   └── Session.php         → Entité Session (POO)
│   ├── services/
│   │   ├── ThemeService.php    → Logique métier Thèmes
│   │   └── SessionService.php  → Logique métier Sessions
│   ├── dao/
│   │   ├── ThemeDAO.php        → Accès données — themes.json
│   │   └── SessionDAO.php      → Accès données — sessions.json
│   ├── data/
│   │   ├── themes.json         → ⚠️ Stockage des thèmes UNIQUEMENT
│   │   └── sessions.json       → ⚠️ Stockage des sessions UNIQUEMENT
│   └── api/
│       ├── themes.php          → Endpoint REST Thèmes
│       └── sessions.php        → Endpoint REST Sessions
│
└── README.md
```

---

## 🏗️ Architecture

### Séparation Frontend / Backend

| Couche | Technologie | Responsabilité |
|--------|-------------|----------------|
| **Frontend** | HTML + Tailwind + JavaScript | Interface utilisateur, Fetch API |
| **API** | PHP | Points d'entrée REST (JSON) |
| **Services** | PHP POO | Validation et logique métier |
| **DAO** | PHP POO | Lecture / écriture fichiers JSON |
| **Modèles** | PHP POO | Entités avec encapsulation |

### Flux de données

```
Navigateur (JS)
    ↓ Fetch API (JSON)
backend/api/themes.php  ou  backend/api/sessions.php
    ↓
ThemeService / SessionService  (validation)
    ↓
ThemeDAO / SessionDAO  (lecture/écriture)
    ↓
backend/data/themes.json  ou  backend/data/sessions.json
```

---

## 🚀 Lancement

### Prérequis

- PHP >= 8.0
- Aucune extension ni dépendance Composer requise

### Démarrer le serveur de développement

Depuis le dossier **`webinaire/`** :

```bash
php -S localhost:8000
```

Puis ouvrir dans le navigateur :

```
http://localhost:8000/frontend/index.html
```

> **Important** : Le serveur doit être lancé depuis la racine `webinaire/` pour que les chemins relatifs de l'API fonctionnent correctement.

---

## 📡 API REST

### Thèmes — `GET /backend/api/themes.php`

| Méthode | URL | Description |
|---------|-----|-------------|
| `GET` | `/backend/api/themes.php` | Liste tous les thèmes |
| `GET` | `/backend/api/themes.php?id=1` | Récupère le thème #1 |
| `POST` | `/backend/api/themes.php` | Crée un thème |
| `PUT` | `/backend/api/themes.php?id=1` | Modifie le thème #1 |
| `DELETE` | `/backend/api/themes.php?id=1` | Supprime le thème #1 |

#### Corps POST / PUT (JSON)
```json
{
  "libelle": "Développement Web",
  "description": "Tout sur le développement web moderne"
}
```

#### Réponse succès (201)
```json
{
  "message": "Thème créé avec succès.",
  "theme": {
    "id_theme": 1,
    "libelle": "Développement Web",
    "description": "Tout sur le développement web moderne"
  }
}
```

---

### Sessions — `GET /backend/api/sessions.php`

| Méthode | URL | Description |
|---------|-----|-------------|
| `GET` | `/backend/api/sessions.php` | Liste toutes les sessions |
| `GET` | `/backend/api/sessions.php?id=1` | Récupère la session #1 |
| `POST` | `/backend/api/sessions.php` | Crée une session |
| `PUT` | `/backend/api/sessions.php?id=1` | Modifie la session #1 |
| `DELETE` | `/backend/api/sessions.php?id=1` | Supprime la session #1 |

#### Corps POST / PUT (JSON)
```json
{
  "titre": "Introduction à Docker",
  "description": "Découvrez les conteneurs Docker pour vos applications",
  "date_session": "2026-11-15T14:00",
  "duree_minutes": 90,
  "lien_connexion": "https://meet.example.com/docker-intro",
  "places_max": 50,
  "statut": "planifiée",
  "id_theme": 1
}
```

#### Réponse succès (201)
```json
{
  "message": "Session créée avec succès.",
  "session": {
    "id_session": 1,
    "titre": "Introduction à Docker",
    "description": "Découvrez les conteneurs Docker pour vos applications",
    "date_session": "2026-11-15T14:00",
    "duree_minutes": 90,
    "lien_connexion": "https://meet.example.com/docker-intro",
    "places_max": 50,
    "statut": "planifiée",
    "id_theme": 1
  }
}
```

---

## 📋 Codes de réponse HTTP

| Code | Signification |
|------|---------------|
| `200` | Succès |
| `201` | Ressource créée |
| `400` | Requête malformée |
| `404` | Ressource introuvable |
| `422` | Erreurs de validation |
| `500` | Erreur interne serveur |

---

## ✅ Fonctionnalités

- [x] CRUD complet des **thèmes** (créer, lire, modifier, supprimer)
- [x] CRUD complet des **sessions** (créer, lire, modifier, supprimer)
- [x] Séparation stricte `themes.json` / `sessions.json`
- [x] Validation côté serveur avec retour d'erreurs détaillé
- [x] Interface d'administration SPA avec Tailwind CSS
- [x] Sidebar navigation avec section active
- [x] Tableau de bord avec statistiques en temps réel
- [x] Messages de succès / erreur avec disparition automatique
- [x] Protection contre les injections XSS (côté frontend)
- [x] Verrouillage des fichiers JSON (`LOCK_EX`) contre la corruption
- [x] Gestion CORS pour les appels cross-origin

---

## 🔒 Règles de validation

### Thème
- Libellé : obligatoire, max 100 caractères
- Description : obligatoire, max 500 caractères

### Session
- Titre : obligatoire, max 150 caractères
- Description : obligatoire
- Date : format `YYYY-MM-DDTHH:MM`
- Durée : 1–480 minutes
- Lien connexion : URL valide
- Places max : 1–10 000
- Statut : `planifiée` | `en cours` | `terminée`
- Thème : doit exister dans `themes.json`

---

## 📦 Technologies utilisées

| Technologie | Usage |
|-------------|-------|
| PHP 8.0+ | Backend POO |
| JSON | Stockage persistant |
| HTML5 | Structure frontend |
| Tailwind CSS (CDN) | Style et mise en page |
| JavaScript (ES2020) | Logique frontend |
| Fetch API | Communication avec le backend |
