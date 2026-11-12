<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modèle représentant un Thème (catégorie de webinaire).
 *
 * Encapsule l'identifiant et le libellé d'un thème.
 * Utilisé comme objet de transfert entre les couches DAO et Service.
 */
class Theme
{
    // ── Attributs privés ────────────────────────────────────────────────────

    private int    $id;
    private string $libelle;

    // ── Constructeur ─────────────────────────────────────────────────────────

    public function __construct(int $id = 0, string $libelle = '')
    {
        $this->id      = $id;
        $this->libelle = $libelle;
    }

    // ── Getters ──────────────────────────────────────────────────────────────

    public function getId(): int
    {
        return $this->id;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    // ── Setters ──────────────────────────────────────────────────────────────

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setLibelle(string $libelle): void
    {
        $this->libelle = trim($libelle);
    }

    // ── Sérialisation ─────────────────────────────────────────────────────────

    /**
     * Convertit l'objet en tableau associatif (pour l'encodage JSON).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'libelle' => $this->libelle,
        ];
    }

    /**
     * Crée une instance Theme depuis un tableau associatif (lu depuis JSON).
     *
     * @param  array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int)   ($data['id']      ?? 0),
            (string)($data['libelle'] ?? '')
        );
    }
}
