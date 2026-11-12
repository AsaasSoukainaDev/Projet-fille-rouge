# Backend template

La structure backend suit maintenant le template du projet pédagogique :

```text
backend/
├── classes/
├── dao/
├── services/
├── data/
└── README.md
```

- `classes/` : modèles / entités
- `dao/` : accès aux données JSON
- `services/` : logique métier et validation
- `data/` : fichiers JSON de persistance

Le projet garde aussi ses points d'entrée API dans le dossier racine `api/` et l'interface dans `public/`.
