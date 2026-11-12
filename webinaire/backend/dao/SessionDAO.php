<?php

declare(strict_types=1);

namespace App\DAO;

/**
 * DAO concret pour l'entité Session.
 *
 * Hérite de JsonDAO qui implémente toutes les opérations CRUD.
 * Cette classe fournit uniquement le nom du champ identifiant
 * et le chemin du fichier JSON dédié aux sessions.
 */
class SessionDAO extends JsonDAO
{
    /**
     * @param string $filePath Chemin absolu vers data/sessions.json
     */
    public function __construct(string $filePath)
    {
        parent::__construct($filePath);
    }

    /**
     * Nom du champ identifiant dans les tableaux session.
     */
    protected function getIdKey(): string
    {
        return 'id';
    }

    /**
     * Retourne toutes les sessions appartenant à un thème donné.
     *
     * Méthode utilitaire utilisée par SessionService pour vérifier
     * si un thème est encore référencé avant suppression.
     *
     * @param  int   $idTheme
     * @return array<int, array<string, mixed>>
     */
    public function findByThemeId(int $idTheme): array
    {
        $toutes = $this->findAll();
        return array_values(
            array_filter($toutes, fn($s) => (int)$s['id_theme'] === $idTheme)
        );
    }
}
