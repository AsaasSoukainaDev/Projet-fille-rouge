<?php

require_once __DIR__ . '/../dao/SessionDAO.php';
require_once __DIR__ . '/../dao/ThemeDAO.php';
require_once __DIR__ . '/../models/Session.php';

/**
 * SessionService — Couche métier pour la gestion des sessions de webinaires
 * 
 * Responsabilités :
 *  - Validation des données entrantes (règles spécifiques aux sessions)
 *  - Orchestration des opérations via SessionDAO
 *  - Vérification de l'existence du thème associé
 *  - Aucun accès direct aux fichiers (délégué au DAO)
 */
class SessionService
{
    private SessionDAO $dao;
    private ThemeDAO   $themeDao;

    public function __construct()
    {
        $this->dao      = new SessionDAO();
        $this->themeDao = new ThemeDAO();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    /**
     * Valide les données d'une session de webinaire.
     *
     * @param array $data Données brutes
     * @return array Tableau d'erreurs (vide si valide)
     */
    public function valider(array $data): array
    {
        $erreurs = [];

        // Titre obligatoire
        if (empty($data['titre']) || trim($data['titre']) === '') {
            $erreurs[] = 'Le titre est obligatoire.';
        } elseif (strlen(trim($data['titre'])) > 150) {
            $erreurs[] = 'Le titre ne doit pas dépasser 150 caractères.';
        }

        // Description obligatoire
        if (empty($data['description']) || trim($data['description']) === '') {
            $erreurs[] = 'La description est obligatoire.';
        }

        // Date de session obligatoire et valide
        if (empty($data['date_session'])) {
            $erreurs[] = 'La date de la session est obligatoire.';
        } else {
            $date = \DateTime::createFromFormat('Y-m-d\TH:i', $data['date_session'])
                 ?: \DateTime::createFromFormat('Y-m-d H:i:s', $data['date_session'])
                 ?: \DateTime::createFromFormat('Y-m-d H:i', $data['date_session']);
            if ($date === false) {
                $erreurs[] = 'Le format de la date est invalide (attendu : YYYY-MM-DDTHH:MM).';
            }
        }

        // Durée obligatoire et positive
        $duree = isset($data['duree_minutes']) ? (int)$data['duree_minutes'] : 0;
        if ($duree <= 0) {
            $erreurs[] = 'La durée doit être un entier positif (en minutes).';
        } elseif ($duree > 480) {
            $erreurs[] = 'La durée ne peut pas dépasser 480 minutes (8 heures).';
        }

        // Lien de connexion obligatoire (doit commencer par http:// ou https://)
        if (empty($data['lien_connexion']) || trim($data['lien_connexion']) === '') {
            $erreurs[] = 'Le lien de connexion est obligatoire.';
        } elseif (!preg_match('#^https?://.+#i', trim($data['lien_connexion']))) {
            $erreurs[] = 'Le lien de connexion doit commencer par http:// ou https://.';
        }

        // Places max obligatoires et positives
        $places = isset($data['places_max']) ? (int)$data['places_max'] : 0;
        if ($places <= 0) {
            $erreurs[] = 'Le nombre de places doit être un entier positif.';
        } elseif ($places > 10000) {
            $erreurs[] = 'Le nombre de places ne peut pas dépasser 10 000.';
        }

        // Statut valide
        if (empty($data['statut'])) {
            $erreurs[] = 'Le statut est obligatoire.';
        } elseif (!in_array($data['statut'], Session::STATUTS_VALIDES, true)) {
            $erreurs[] = 'Le statut doit être : ' . implode(', ', Session::STATUTS_VALIDES) . '.';
        }

        // Thème associé obligatoire et existant
        $idTheme = isset($data['id_theme']) ? (int)$data['id_theme'] : 0;
        if ($idTheme <= 0) {
            $erreurs[] = 'Un thème doit être sélectionné.';
        } else {
            $theme = $this->themeDao->findById($idTheme);
            if ($theme === null) {
                $erreurs[] = "Le thème #$idTheme n'existe pas.";
            }
        }

        return $erreurs;
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Crée une nouvelle session après validation.
     *
     * @param array $data Données brutes
     * @return array ['succes' => bool, 'erreurs' => [], 'session' => Session|null]
     */
    public function creer(array $data): array
    {
        $erreurs = $this->valider($data);
        if (!empty($erreurs)) {
            return ['succes' => false, 'erreurs' => $erreurs, 'session' => null];
        }

        $session = new Session(
            0,
            trim($data['titre']),
            trim($data['description']),
            trim($data['date_session']),
            (int)$data['duree_minutes'],
            trim($data['lien_connexion']),
            (int)$data['places_max'],
            $data['statut'],
            (int)$data['id_theme']
        );

        $session = $this->dao->save($session);

        return ['succes' => true, 'erreurs' => [], 'session' => $session];
    }

    /**
     * Modifie une session existante.
     *
     * @param int   $id   Identifiant de la session
     * @param array $data Nouvelles données
     * @return array ['succes' => bool, 'erreurs' => [], 'session' => Session|null]
     */
    public function modifier(int $id, array $data): array
    {
        // Vérifier que la session existe
        $session = $this->dao->findById($id);
        if ($session === null) {
            return ['succes' => false, 'erreurs' => ["Session #$id introuvable."], 'session' => null];
        }

        $erreurs = $this->valider($data);
        if (!empty($erreurs)) {
            return ['succes' => false, 'erreurs' => $erreurs, 'session' => null];
        }

        $session->setTitre(trim($data['titre']));
        $session->setDescription(trim($data['description']));
        $session->setDateSession(trim($data['date_session']));
        $session->setDureeMinutes((int)$data['duree_minutes']);
        $session->setLienConnexion(trim($data['lien_connexion']));
        $session->setPlacesMax((int)$data['places_max']);
        $session->setStatut($data['statut']);
        $session->setIdTheme((int)$data['id_theme']);

        $succes = $this->dao->update($session);

        return ['succes' => $succes, 'erreurs' => [], 'session' => $session];
    }

    /**
     * Supprime une session par son identifiant.
     *
     * @param int $id Identifiant de la session
     * @return array ['succes' => bool, 'erreurs' => []]
     */
    public function supprimer(int $id): array
    {
        $session = $this->dao->findById($id);
        if ($session === null) {
            return ['succes' => false, 'erreurs' => ["Session #$id introuvable."]];
        }

        $succes = $this->dao->delete($id);
        return ['succes' => $succes, 'erreurs' => []];
    }

    /**
     * Retourne toutes les sessions.
     *
     * @return Session[]
     */
    public function lister(): array
    {
        return $this->dao->findAll();
    }

    /**
     * Retourne une session par son identifiant.
     *
     * @param int $id Identifiant de la session
     * @return Session|null
     */
    public function trouver(int $id): ?Session
    {
        return $this->dao->findById($id);
    }
}
