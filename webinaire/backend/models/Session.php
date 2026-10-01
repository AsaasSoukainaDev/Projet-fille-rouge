<?php

/**
 * Classe Session — Entité représentant une session de webinaire
 * 
 * Attributs encapsulés (privés), accessibles via getters/setters.
 */
class Session
{
    // -------------------------------------------------------------------------
    // Attributs privés
    // -------------------------------------------------------------------------
    private int    $idSession;
    private string $titre;
    private string $description;
    private string $dateSession;     // Format ISO 8601 : YYYY-MM-DDTHH:MM
    private int    $dureeMinutes;
    private string $lienConnexion;
    private int    $placesMax;
    private string $statut;          // "planifiée" | "en cours" | "terminée"
    private int    $idTheme;         // Référence vers Theme

    // Valeurs autorisées pour le statut
    public const STATUTS_VALIDES = ['planifiée', 'en cours', 'terminée'];

    // -------------------------------------------------------------------------
    // Constructeur
    // -------------------------------------------------------------------------
    /**
     * @param int    $idSession     Identifiant unique (0 = non encore persisté)
     * @param string $titre         Titre de la session
     * @param string $description   Description de la session
     * @param string $dateSession   Date/heure au format YYYY-MM-DDTHH:MM
     * @param int    $dureeMinutes  Durée en minutes
     * @param string $lienConnexion Lien de connexion (URL)
     * @param int    $placesMax     Nombre maximum de participants
     * @param string $statut        Statut de la session
     * @param int    $idTheme       Référence vers le thème associé
     */
    public function __construct(
        int    $idSession    = 0,
        string $titre        = '',
        string $description  = '',
        string $dateSession  = '',
        int    $dureeMinutes = 0,
        string $lienConnexion = '',
        int    $placesMax    = 0,
        string $statut       = 'planifiée',
        int    $idTheme      = 0
    ) {
        $this->idSession    = $idSession;
        $this->titre        = $titre;
        $this->description  = $description;
        $this->dateSession  = $dateSession;
        $this->dureeMinutes = $dureeMinutes;
        $this->lienConnexion = $lienConnexion;
        $this->placesMax    = $placesMax;
        $this->statut       = $statut;
        $this->idTheme      = $idTheme;
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------
    public function getIdSession(): int      { return $this->idSession; }
    public function getTitre(): string       { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getDateSession(): string { return $this->dateSession; }
    public function getDureeMinutes(): int   { return $this->dureeMinutes; }
    public function getLienConnexion(): string { return $this->lienConnexion; }
    public function getPlacesMax(): int      { return $this->placesMax; }
    public function getStatut(): string      { return $this->statut; }
    public function getIdTheme(): int        { return $this->idTheme; }

    // -------------------------------------------------------------------------
    // Setters
    // -------------------------------------------------------------------------
    public function setIdSession(int $idSession): void          { $this->idSession    = $idSession; }
    public function setTitre(string $titre): void               { $this->titre        = $titre; }
    public function setDescription(string $description): void   { $this->description  = $description; }
    public function setDateSession(string $dateSession): void   { $this->dateSession  = $dateSession; }
    public function setDureeMinutes(int $dureeMinutes): void    { $this->dureeMinutes = $dureeMinutes; }
    public function setLienConnexion(string $lienConnexion): void { $this->lienConnexion = $lienConnexion; }
    public function setPlacesMax(int $placesMax): void          { $this->placesMax    = $placesMax; }
    public function setStatut(string $statut): void             { $this->statut       = $statut; }
    public function setIdTheme(int $idTheme): void              { $this->idTheme      = $idTheme; }

    // -------------------------------------------------------------------------
    // Méthodes de sérialisation
    // -------------------------------------------------------------------------

    /**
     * Convertit l'objet en tableau associatif (pour stockage JSON).
     */
    public function toArray(): array
    {
        return [
            'id_session'    => $this->idSession,
            'titre'         => $this->titre,
            'description'   => $this->description,
            'date_session'  => $this->dateSession,
            'duree_minutes' => $this->dureeMinutes,
            'lien_connexion'=> $this->lienConnexion,
            'places_max'    => $this->placesMax,
            'statut'        => $this->statut,
            'id_theme'      => $this->idTheme,
        ];
    }

    /**
     * Crée un objet Session à partir d'un tableau associatif (depuis JSON).
     *
     * @param array $data Tableau de données
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int)   ($data['id_session']    ?? 0),
                    ($data['titre']         ?? ''),
                    ($data['description']   ?? ''),
                    ($data['date_session']  ?? ''),
            (int)   ($data['duree_minutes'] ?? 0),
                    ($data['lien_connexion']?? ''),
            (int)   ($data['places_max']    ?? 0),
                    ($data['statut']        ?? 'planifiée'),
            (int)   ($data['id_theme']      ?? 0)
        );
    }

    /**
     * Retourne une représentation textuelle de la session.
     */
    public function afficher(): string
    {
        return sprintf(
            '[Session #%d] %s | %s | %d min | %d places | Statut : %s | Thème #%d',
            $this->idSession,
            $this->titre,
            $this->dateSession,
            $this->dureeMinutes,
            $this->placesMax,
            $this->statut,
            $this->idTheme
        );
    }
}
