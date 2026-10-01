<?php

require_once __DIR__ . '/../dao/ThemeDAO.php';
require_once __DIR__ . '/../models/Theme.php';

/**
 * ThemeService — Couche métier pour la gestion des thèmes
 * 
 * Responsabilités :
 *  - Validation des données entrantes
 *  - Orchestration des opérations via ThemeDAO
 *  - Aucun accès direct aux fichiers (délégué au DAO)
 */
class ThemeService
{
    private ThemeDAO $dao;

    public function __construct()
    {
        $this->dao = new ThemeDAO();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    /**
     * Valide les données d'un thème.
     *
     * @param array $data Données brutes
     * @return array Tableau d'erreurs (vide si valide)
     */
    public function valider(array $data): array
    {
        $erreurs = [];

        // Libellé obligatoire et non vide
        if (empty($data['libelle']) || trim($data['libelle']) === '') {
            $erreurs[] = 'Le libellé est obligatoire.';
        } elseif (strlen(trim($data['libelle'])) > 100) {
            $erreurs[] = 'Le libellé ne doit pas dépasser 100 caractères.';
        }

        // Description obligatoire
        if (empty($data['description']) || trim($data['description']) === '') {
            $erreurs[] = 'La description est obligatoire.';
        } elseif (strlen(trim($data['description'])) > 500) {
            $erreurs[] = 'La description ne doit pas dépasser 500 caractères.';
        }

        return $erreurs;
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Crée un nouveau thème après validation.
     *
     * @param array $data Données brutes
     * @return array ['succes' => bool, 'erreurs' => [], 'theme' => Theme|null]
     */
    public function creer(array $data): array
    {
        $erreurs = $this->valider($data);
        if (!empty($erreurs)) {
            return ['succes' => false, 'erreurs' => $erreurs, 'theme' => null];
        }

        $theme = new Theme(
            0,
            trim($data['libelle']),
            trim($data['description'])
        );

        $theme = $this->dao->save($theme);

        return ['succes' => true, 'erreurs' => [], 'theme' => $theme];
    }

    /**
     * Modifie un thème existant.
     *
     * @param int   $id   Identifiant du thème
     * @param array $data Nouvelles données
     * @return array ['succes' => bool, 'erreurs' => [], 'theme' => Theme|null]
     */
    public function modifier(int $id, array $data): array
    {
        // Vérifier que le thème existe
        $theme = $this->dao->findById($id);
        if ($theme === null) {
            return ['succes' => false, 'erreurs' => ["Thème #$id introuvable."], 'theme' => null];
        }

        $erreurs = $this->valider($data);
        if (!empty($erreurs)) {
            return ['succes' => false, 'erreurs' => $erreurs, 'theme' => null];
        }

        $theme->setLibelle(trim($data['libelle']));
        $theme->setDescription(trim($data['description']));

        $succes = $this->dao->update($theme);

        return ['succes' => $succes, 'erreurs' => [], 'theme' => $theme];
    }

    /**
     * Supprime un thème par son identifiant.
     *
     * @param int $id Identifiant du thème
     * @return array ['succes' => bool, 'erreurs' => []]
     */
    public function supprimer(int $id): array
    {
        $theme = $this->dao->findById($id);
        if ($theme === null) {
            return ['succes' => false, 'erreurs' => ["Thème #$id introuvable."]];
        }

        $succes = $this->dao->delete($id);
        return ['succes' => $succes, 'erreurs' => []];
    }

    /**
     * Retourne tous les thèmes.
     *
     * @return Theme[]
     */
    public function lister(): array
    {
        return $this->dao->findAll();
    }

    /**
     * Retourne un thème par son identifiant.
     *
     * @param int $id Identifiant du thème
     * @return Theme|null
     */
    public function trouver(int $id): ?Theme
    {
        return $this->dao->findById($id);
    }
}
