<?php

/**
 * Classe Theme — Entité représentant un thème (catégorie) de webinaire
 * 
 * Attributs encapsulés (privés), accessibles via getters/setters.
 */
class Theme
{
    // -------------------------------------------------------------------------
    // Attributs privés
    // -------------------------------------------------------------------------
    private int    $idTheme;
    private string $libelle;
    private string $description;

    // -------------------------------------------------------------------------
    // Constructeur
    // -------------------------------------------------------------------------
    /**
     * @param int    $idTheme     Identifiant unique (0 = non encore persisté)
     * @param string $libelle     Libellé du thème
     * @param string $description Description du thème
     */
    public function __construct(int $idTheme = 0, string $libelle = '', string $description = '')
    {
        $this->idTheme     = $idTheme;
        $this->libelle     = $libelle;
        $this->description = $description;
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------
    public function getIdTheme(): int    { return $this->idTheme; }
    public function getLibelle(): string { return $this->libelle; }
    public function getDescription(): string { return $this->description; }

    // -------------------------------------------------------------------------
    // Setters
    // -------------------------------------------------------------------------
    public function setIdTheme(int $idTheme): void         { $this->idTheme     = $idTheme; }
    public function setLibelle(string $libelle): void      { $this->libelle     = $libelle; }
    public function setDescription(string $description): void { $this->description = $description; }

    // -------------------------------------------------------------------------
    // Méthodes de sérialisation
    // -------------------------------------------------------------------------

    /**
     * Convertit l'objet en tableau associatif (pour stockage JSON).
     */
    public function toArray(): array
    {
        return [
            'id_theme'    => $this->idTheme,
            'libelle'     => $this->libelle,
            'description' => $this->description,
        ];
    }

    /**
     * Crée un objet Theme à partir d'un tableau associatif (depuis JSON).
     *
     * @param array $data Tableau de données
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int)   ($data['id_theme']    ?? 0),
                    ($data['libelle']     ?? ''),
                    ($data['description'] ?? '')
        );
    }

    /**
     * Retourne une représentation textuelle du thème.
     */
    public function afficher(): string
    {
        return sprintf(
            '[Thème #%d] %s — %s',
            $this->idTheme,
            $this->libelle,
            $this->description
        );
    }
}
