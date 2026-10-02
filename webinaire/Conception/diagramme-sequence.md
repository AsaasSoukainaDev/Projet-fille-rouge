# Diagramme de séquence — Réserver une session

```mermaid
sequenceDiagram
    autonumber
    actor P as 🎫 Participant
    participant UI as 🖥️ InterfaceSession
    participant Ctrl as 🎛️ InscriptionController
    participant Serv as ⚙️ InscriptionService
    participant DAO as 💾 InscriptionDAO
    participant Visio as 📹 ServiceVisio
    participant Mail as 📧 ServiceEmail
    participant DB as 🗄️ Fichiers JSON

    Note over P,DB: Scénario complet : Réserver une session

    P->>UI: cliquer sur "Réserver"
    activate UI
    UI->>Ctrl: demanderReservation(idSession)
    activate Ctrl

    Ctrl->>Serv: verifierDisponibilite(idSession)
    activate Serv
    Serv->>DAO: trouverSession(idSession)
    activate DAO
    DAO->>DB: lire sessions.json
    activate DB
    DB-->>DAO: session
    deactivate DB
    DAO-->>Serv: session
    deactivate DAO

    alt Places disponibles
        Serv-->>Ctrl: disponible = true
    else Plus de places
        Serv-->>Ctrl: disponible = false
        Ctrl-->>UI: afficherErreur("Complet")
        UI-->>P: ❌ Session complète
    end
    deactivate Serv

    Ctrl->>Serv: verifierDejaInscrit(idParticipant, idSession)
    activate Serv
    Serv->>DAO: existeInscription(idParticipant, idSession)
    activate DAO
    DAO->>DB: lire inscriptions.json
    activate DB
    DB-->>DAO: count
    deactivate DB
    DAO-->>Serv: count
    deactivate DAO

    alt Déjà inscrit
        Serv-->>Ctrl: dejaInscrit = true
        Ctrl-->>UI: afficherErreur("Déjà inscrit")
        UI-->>P: ⚠️ Déjà inscrit
    else Nouvelle inscription
        Serv-->>Ctrl: dejaInscrit = false
    end
    deactivate Serv

    Ctrl->>Serv: creerInscription(idParticipant, idSession)
    activate Serv
    Serv->>Serv: new Inscription(...)
    Serv->>DAO: sauvegarder(inscription)
    activate DAO
    DAO->>DB: écrire inscriptions.json
    activate DB
    DB-->>DAO: idInscription
    deactivate DB
    DAO-->>Serv: inscriptionSauvegardee
    deactivate DAO

    Serv->>Visio: genererLienSession(idSession)
    activate Visio
    Visio-->>Serv: lienConnexion
    deactivate Visio

    Serv->>Mail: envoyerConfirmation(email, session, lien)
    activate Mail
    Mail-->>Serv: emailEnvoye = true
    deactivate Mail

    Serv-->>Ctrl: inscriptionConfirmee(id)
    deactivate Serv

    Ctrl-->>UI: afficherConfirmation(inscription)
    deactivate Ctrl
    UI-->>P: ✅ "Inscription confirmée !"
    deactivate UI

    Note over P,DB: Mise à jour du planning
    P->>UI: consulter son planning
    activate UI
    UI->>Ctrl: obtenirPlanning(idParticipant)
    activate Ctrl
    Ctrl->>Serv: listerInscriptions(idParticipant)
    activate Serv
    Serv->>DAO: findAllByParticipant(idParticipant)
    activate DAO
    DAO->>DB: lire inscriptions.json
    activate DB
    DB-->>DAO: liste
    deactivate DB
    DAO-->>Serv: liste
    deactivate DAO
    Serv-->>Ctrl: liste
    deactivate Serv
    Ctrl-->>UI: liste
    deactivate Ctrl
    UI-->>P: afficher le planning à jour
    deactivate UI
