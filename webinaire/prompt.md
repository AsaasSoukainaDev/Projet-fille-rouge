
Vous êtes développeur senior, travaillant pour le projet de webinaire. Je veux travailler en POO et enregistrer les données dans un fichier JSON. Je veux le CRUD de la catégorie séparé du CRUD de la classe principale. On va utiliser un fichier JSON pour stocker les catégories et un fichier JSON pour la classe principale. On va travailler avec l'architecture 3-tiers, et utiliser Tailwind CSS et Fetch API pour afficher et ajouter dans le JSON.
 |
 
# CONTEXTE DU PROJET

Tu es un développeur senior full-stack PHP/JavaScript. Tu vas développer une 
application web de gestion de webinaires en respectant STRICTEMENT les 
exigences ci-dessous. Ne t'écarte d'aucune règle.

---

# 🎯 OBJECTIF

Développer une application web **"Plateforme de Webinaires"** avec :

- **Backend** : PHP orienté objet (POO)
- **Stockage** : Fichiers JSON (PAS de base de données MySQL)
- **Architecture** : Frontend / Backend (séparation physique des dossiers)
- **Frontend** : HTML + Tailwind CSS + JavaScript (Fetch API)
- **CRUD complet** : Create, Read, Update, Delete
- **Séparation obligatoire** : 
  - 1 fichier JSON pour les **catégories** (thèmes)
  - 1 fichier JSON pour la **classe principale** (sessions)

---

# 📊 ENTITÉS DU SYSTÈME

## Entité principale : SESSION (webinaire)
- id_session (int, auto-incrément)
- titre (string)
- description (string)
- date_session (datetime)
- duree_minutes (int)
- lien_connexion (string)
- places_max (int)
- statut (string : "planifiée", "en cours", "terminée")
- id_theme (int, référence vers thème)

## Entité catégorie : THEME
- id_theme (int, auto-incrément)
- libelle (string)
- description (string)

## Entités secondaires (optionnelles au CRUD actuel)
- Participant (id, nom, prenom, email, mot_de_passe)
- Intervenant (id, nom, prenom, bio, photo)

---

# 🏗️ ARCHITECTURE FRONTEND / BACKEND OBLIGATOIRE

```
📁 webinaire/
│
├── 📁 frontend/                   ← 🖥️ CÔTÉ CLIENT (navigateur)
│   ├── index.html                 → Interface principale (Tailwind)
│   ├── 📁 css/
│   │   └── style.css
│   └── 📁 js/
│       ├── api.js                 → Fetch API générique
│       ├── themes.js              → Logique front pour les thèmes
│       └── sessions.js            → Logique front pour les sessions
│
├── 📁 backend/                    ← ⚙️ CÔTÉ SERVEUR (PHP)
│   │
│   ├── 📁 models/                 → Classes POO (entités)
│   │   ├── Theme.php
│   │   └── Session.php
│   │
│   ├── 📁 services/               → Logique métier + validation
│   │   ├── ThemeService.php
│   │   └── SessionService.php
│   │
│   ├── 📁 dao/                    → Accès aux données (JSON)
│   │   ├── ThemeDAO.php           → CRUD thèmes (fichier themes.json)
│   │   └── SessionDAO.php         → CRUD sessions (fichier sessions.json)
│   │
│   ├── 📁 data/                   → Stockage des données
│   │   ├── themes.json            → ⚠️ UNIQUEMENT les thèmes
│   │   └── sessions.json          → ⚠️ UNIQUEMENT les sessions
│   │
│   └── 📁 api/                    → Points d'entrée REST (JSON)
│       ├── themes.php             → Endpoints CRUD thèmes
│       └── sessions.php           → Endpoints CRUD sessions
│
└── 📄 README.md
```

---

# ⚠️ RÈGLES STRICTES À RESPECTER

## Règle 1 — Séparation des CRUD
- ❌ INTERDIT de mélanger les thèmes et les sessions dans le même fichier.
- ✅ Le CRUD des **thèmes** est **entièrement séparé** du CRUD des **sessions**.
- ✅ Chaque entité a son propre fichier JSON, son propre DAO, son propre Service, son propre endpoint API.

## Règle 2 — Stockage JSON
- ❌ Pas de MySQL, pas de PDO, pas de SQL.
- ✅ Les données sont stockées dans des fichiers `.json`.
- ✅ Lecture/écriture via `file_get_contents()` et `file_put_contents()` en PHP.
- ✅ Verrouillage des fichiers (`LOCK_EX`) pour éviter la corruption.
- ✅ Auto-incrémentation des IDs gérée manuellement par le DAO.

## Règle 3 — POO et encapsulation
- ✅ Tous les attributs des classes sont **privés**.
- ✅ Accès via **getters** et **setters** publics.
- ✅ Chaque classe a une méthode `toArray()` et une méthode statique `fromArray()`.
- ✅ Chaque classe a une méthode `afficher()` qui retourne une représentation textuelle.

## Règle 4 — Architecture Frontend / Backend
- ✅ **Frontend** : HTML + Tailwind + JS + Fetch API (dans `frontend/`)
- ✅ **Backend** : Classes POO + Services + DAO + API (dans `backend/`)
- ✅ Aucun mélange : pas de `.php` dans `frontend/`, pas de `.html` dans `backend/`
- ✅ Le frontend communique UNIQUEMENT avec le backend via Fetch API (JSON)

## Règle 5 — Frontend
- ✅ **Tailwind CSS** via CDN pour tout le style.
- ✅ **Fetch API** pour tous les appels au backend.
- ✅ **DOM** pour l'affichage dynamique (tableaux, formulaires).
- ✅ Interface d'administration avec sidebar + header + contenu.
- ✅ Affichage et ajout obligatoires (modification et suppression en bonus).

---

# 📄 DÉTAIL DES FICHIERS À PRODUIRE

## BACKEND

### 1. `backend/data/themes.json` — Structure initiale
```json
[]
```

### 2. `backend/data/sessions.json` — Structure initiale
```json
[]
```

### 3. `backend/models/Theme.php`
Classe `Theme` avec :
- Attributs privés : idTheme, libelle, description
- Constructeur avec paramètres par défaut
- Getters et setters pour chaque attribut
- Méthode `toArray()` : retourne un tableau associatif
- Méthode statique `fromArray(array $data)` : crée un objet Theme
- Méthode `afficher()` : retourne une chaîne descriptive

### 4. `backend/models/Session.php`
Classe `Session` avec :
- Attributs privés : idSession, titre, description, dateSession, dureeMinutes, lienConnexion, placesMax, statut, idTheme
- Mêmes méthodes que Theme (constructeur, getters/setters, toArray, fromArray, afficher)

### 5. `backend/services/ThemeService.php`
- Méthode `valider(array $data)` : retourne un tableau d'erreurs
- Méthode `creer(array $data)` : crée un Theme et l'enregistre
- Méthode `modifier(int $id, array $data)` : modifie un Theme
- Méthode `supprimer(int $id)` : supprime un Theme
- Méthode `lister()` : retourne tous les thèmes
- Méthode `trouver(int $id)` : retourne un thème par ID

### 6. `backend/services/SessionService.php`
- Idem pour les sessions + validation spécifique

### 7. `backend/dao/ThemeDAO.php`
- Utilise `themes.json` UNIQUEMENT
- Méthodes : `findAll()`, `findById()`, `save()`, `update()`, `delete()`, `getNextId()`
- Gestion du verrouillage fichier avec `LOCK_EX`

### 8. `backend/dao/SessionDAO.php`
- Utilise `sessions.json` UNIQUEMENT
- Mêmes méthodes que ThemeDAO

### 9. `backend/api/themes.php`
- Endpoint REST pour les thèmes
- Gère : GET (liste), GET?id (détail), POST (créer), PUT (modifier), DELETE (supprimer)
- Retourne du JSON avec `Content-Type: application/json`
- Gère les codes HTTP : 200, 201, 400, 404, 422, 500

### 10. `backend/api/sessions.php`
- Endpoint REST pour les sessions
- Mêmes règles que themes.php

---

## FRONTEND

### 11. `frontend/index.html`
Interface Tailwind complète avec :
- Sidebar (fond `bg-gray-800`, liens avec hover)
- Header (fond blanc, ombre)
- Onglets ou sections : Thèmes / Sessions
- Formulaire d'ajout de thème
- Formulaire d'ajout de session
- Tableaux d'affichage des thèmes et sessions
- Boutons Supprimer / Modifier
- Messages de succès/erreur

### 12. `frontend/js/api.js`
```javascript
const API_URL = '../backend/api';

async function apiGet(endpoint, id = null) { ... }
async function apiPost(endpoint, data) { ... }
async function apiPut(endpoint, id, data) { ... }
async function apiDelete(endpoint, id) { ... }
```

### 13. `frontend/js/themes.js`
- Charger les thèmes au démarrage
- Gérer la soumission du formulaire (POST)
- Afficher les thèmes dans le tableau (DOM)
- Supprimer un thème (DELETE)

### 14. `frontend/js/sessions.js`
- Mêmes logiques pour les sessions
- Charger aussi les thèmes pour le `<select>` du formulaire

---

## DOCUMENTATION

### 15. `README.md`
- Description du projet
- Architecture Frontend / Backend expliquée
- Arborescence
- Instructions de lancement (`php -S localhost:8000`)
- Exemples d'appels API

---

# 🎨 STYLE TAILWIND À APPLIQUER

- **Sidebar** : `w-64 bg-gray-800 text-white flex flex-col p-6 gap-4`
- **Lien actif** : `bg-blue-600 text-white rounded-lg`
- **Lien inactif** : `text-gray-300 hover:bg-gray-700 hover:text-white`
- **Header** : `bg-white border-b border-gray-200 shadow-sm px-6 py-4`
- **Bouton primaire** : `bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700`
- **Bouton secondaire** : `bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50`
- **Bouton danger** : `text-red-600 hover:text-red-800 font-medium`
- **Champs** : `border border-gray-300 rounded-lg w-full p-2 focus:outline-none focus:ring-2 focus:ring-blue-500`
- **Tableau** : `w-full`, en-têtes `bg-gray-50 text-left text-xs text-gray-500 uppercase`
- **Lignes** : `divide-y divide-gray-200` + `hover:bg-gray-50`

---

# 📋 LIVRABLES ATTENDUS

1. Arborescence complète créée
2. Tous les fichiers listés ci-dessus, remplis et fonctionnels
3. Fichiers JSON créés avec `[]` comme contenu initial
4. README.md avec instructions
5. Code commenté en français
6. Respect strict de la séparation thèmes / sessions
7. Respect strict de l'architecture Frontend / Backend
8. Aucune dépendance à MySQL ou PDO
9. Fonctionnalités testables immédiatement :
   - Ajouter un thème → apparaît dans `themes.json` et dans le tableau
   - Ajouter une session → apparaît dans `sessions.json` et dans le tableau
   - Supprimer → disparaît des deux

---

# 🚀 ORDRE DE GÉNÉRATION

Génère les fichiers dans cet ordre :

1. `backend/data/themes.json` et `backend/data/sessions.json`
2. `backend/models/Theme.php` et `backend/models/Session.php`
3. `backend/dao/ThemeDAO.php` et `backend/dao/SessionDAO.php`
4. `backend/services/ThemeService.php` et `backend/services/SessionService.php`
5. `backend/api/themes.php` et `backend/api/sessions.php`
6. `frontend/index.html`
7. `frontend/js/api.js`, `frontend/js/themes.js`, `frontend/js/sessions.js`
8. `README.md`

---

# ⚠️ INTERDICTIONS ABSOLUES

- ❌ Ne PAS utiliser de base de données (MySQL, PDO, SQLite)
- ❌ Ne PAS mélanger thèmes et sessions dans le même JSON
- ❌ Ne PAS mettre de logique métier dans les DAO
- ❌ Ne PAS mettre de logique d'accès aux données dans les Services
- ❌ Ne PAS appeler directement les fichiers JSON depuis le JavaScript
- ❌ Ne PAS utiliser de framework PHP (Laravel, Symfony…)
- ❌ Ne PAS installer de dépendances Composer
- ❌ Ne PAS créer de dossier `presentation/` ou `metier/`

---

# ✅ CRITÈRES DE RÉUSSITE

Le projet est réussi si :
- ✅ Un thème ajouté via l'interface apparaît dans `themes.json`
- ✅ Une session ajoutée via l'interface apparaît dans `sessions.json`
- ✅ La suppression fonctionne pour les deux
- ✅ Les deux CRUD sont totalement indépendants
- ✅ L'architecture Frontend / Backend est respectée
- ✅ Le code est en POO avec encapsulation
- ✅ L'interface est propre avec Tailwind
- ✅ Les appels passent par Fetch API

---

# 🎬 COMMENCE MAINTENANT

Génère d'abord l'arborescence complète, puis chaque fichier dans l'ordre indiqué.
Sois exhaustif, professionnel, et respecte TOUTES les règles ci-dessus.
