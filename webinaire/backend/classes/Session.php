<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modèle représentant une Session de webinaire.
 *
 * Encapsule toutes les propriétés d'une session et fournit
 * les méthodes de conversion vers/depuis un tableau associatif.
 */
class Session
{
    // ── Attributs privés ────────────────────────────────────────────────────

    private int    $id;
    private string $titre;
    private string $description;
    private string $dateSession;  // Format : YYYY-MM-DD
    private string $heureDebut;   // Format : HH:MM
    private int    $duree;        // En minutes
    private string $lienVisio;    // URL de la visioconférence
    private int    $idTheme;      // Clé étrangère vers Theme::id

    // ── Constructeur ─────────────────────────────────────────────────────────

    public function __construct(
        int    $id          = 0,
        string $titre       = '',
        string $description = '',
        string $dateSession = '',
        string $heureDebut  = '',
        int    $duree       = 0,
        string $lienVisio   = '',
        int    $idTheme     = 0
    ) {
        $this->id          = $id;
        $this->titre       = $titre;
        $this->description = $description;
        $this->dateSession = $dateSession;
        $this->heureDebut  = $heureDebut;
        $this->duree       = $duree;
        $this->lienVisio   = $lienVisio;
        $this->idTheme     = $idTheme;
    }

    // ── Getters ──────────────────────────────────────────────────────────────

    public function getId(): int          { return $this->id; }
    public function getTitre(): string    { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getDateSession(): string { return $this->dateSession; }
    public function getHeureDebut(): string  { return $this->heureDebut; }
    public function getDuree(): int          { return $this->duree; }
    public function getLienVisio(): string   { return $this->lienVisio; }
    public function getIdTheme(): int        { return $this->idTheme; }

    // ── Setters ──────────────────────────────────────────────────────────────

    public function setId(int $id): void                   { $this->id = $id; }
    public function setTitre(string $titre): void          { $this->titre = trim($titre); }
    public function setDescription(string $d): void        { $this->description = trim($d); }
    public function setDateSession(string $date): void     { $this->dateSession = $date; }
    public function setHeureDebut(string $heure): void     { $this->heureDebut = $heure; }
    public function setDuree(int $duree): void             { $this->duree = $duree; }
    public function setLienVisio(string $lien): void       { $this->lienVisio = trim($lien); }
    public function setIdTheme(int $idTheme): void         { $this->idTheme = $idTheme; }

    // ── Sérialisation ─────────────────────────────────────────────────────────

    /**
     * Convertit l'objet en tableau associatif (pour l'encodage JSON).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'titre'        => $this->titre,
            'description'  => $this->description,
            'date_session' => $this->dateSession,
            'heure_debut'  => $this->heureDebut,
            'duree'        => $this->duree,
            'lien_visio'   => $this->lienVisio,
            'id_theme'     => $this->idTheme,
        ];
    }

    /**
     * Crée une instance Session depuis un tableau associatif (lu depuis JSON).
     *
     * @param  array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int)   ($data['id']           ?? 0),
            (string)($data['titre']        ?? ''),
            (string)($data['description']  ?? ''),
            (string)($data['date_session'] ?? ''),
            (string)($data['heure_debut']  ?? ''),
            (int)   ($data['duree']        ?? 0),
            (string)($data['lien_visio']   ?? ''),
            (int)   ($data['id_theme']     ?? 0)
        );
    }
}
