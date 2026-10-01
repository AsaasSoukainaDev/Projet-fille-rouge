# MCD — Plateforme de Webinaires

## Entités

| Entité | Identifiant | Propriétés |
|--------|-------------|-----------|
| THEME | id_theme | libelle, description |
| INTERVENANT | id_intervenant | nom, prenom, bio, photo |
| PARTICIPANT | id_participant | nom, prenom, email, mot_de_passe |
| SESSION | id_session | titre, description, date_session, duree_minutes, lien_connexion, places_max, statut |

## Associations

| Association | Entités liées | Signification |
|-------------|---------------|---------------|
| CLASSER | THEME, SESSION | Un thème classe des sessions |
| ANIMER | INTERVENANT, SESSION | Un intervenant anime des sessions |
| INSCRIRE | PARTICIPANT, SESSION | Un participant s'inscrit à des sessions |

## Cardinalités

| Association | Entité | Cardinalité |
|-------------|--------|-------------|
| CLASSER | THEME | 0,N |
| CLASSER | SESSION | 1,1 |
| ANIMER | INTERVENANT | 0,N |
| ANIMER | SESSION | 1,1 |
| INSCRIRE | PARTICIPANT | 0,N |
| INSCRIRE | SESSION | 0,N |

## Schéma

```mermaid
flowchart LR
    THEME["<b>THEME</b><br/>─────────<br/><u>id_theme</u><br/>libelle<br/>description"]
    CLASSER{{"CLASSER"}}
    SESSION["<b>SESSION</b><br/>─────────<br/><u>id_session</u><br/>titre<br/>description<br/>date_session<br/>duree_minutes<br/>lien_connexion<br/>places_max<br/>statut"]
    ANIMER{{"ANIMER"}}
    INTERVENANT["<b>INTERVENANT</b><br/>─────────<br/><u>id_intervenant</u><br/>nom<br/>prenom<br/>bio<br/>photo"]
    INSCRIRE{{"INSCRIRE"}}
    PARTICIPANT["<b>PARTICIPANT</b><br/>─────────<br/><u>id_participant</u><br/>nom<br/>prenom<br/>email<br/>mot_de_passe"]

    THEME ---|"0,N"| CLASSER
    CLASSER ---|"1,1"| SESSION
    SESSION ---|"1,1"| ANIMER
    ANIMER ---|"0,N"| INTERVENANT
    SESSION ---|"0,N"| INSCRIRE
    INSCRIRE ---|"0,N"| PARTICIPANT
```

*Figure 1 : MCD de la plateforme de webinaires (identifiants soulignés).*