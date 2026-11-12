<?php

declare(strict_types=1);

namespace App\DAO;

/**
 * DAO concret pour l'entité Thème.
 *
 * Hérite de JsonDAO qui implémente toutes les opérations CRUD.
 * Cette classe fournit uniquement le nom du champ identifiant
 * et le chemin du fichier JSON dédié aux thèmes.
 */
class ThemeDAO extends JsonDAO
{
    /**
     * @param string $filePath Chemin absolu vers data/themes.json
     */
    public function __construct(string $filePath)
    {
        parent::__construct($filePath);
    }

    /**
     * Nom du champ identifiant dans les tableaux thème.
     */
    protected function getIdKey(): string
    {
        return 'id';
    }
}
