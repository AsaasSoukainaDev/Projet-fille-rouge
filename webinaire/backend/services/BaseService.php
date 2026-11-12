<?php

declare(strict_types=1);

namespace App\Services;

use App\DAO\DAOInterface;
use App\Exceptions\{NotFoundException, ValidationException};

/**
 * Classe de service abstraite générique (couche métier).
 *
 * Fournit les opérations CRUD de haut niveau en combinant :
 *  - La persistance déléguée au DAO injecté dans le constructeur
 *  - La validation déléguée à la méthode abstraite validate()
 *    que chaque service concret doit implémenter
 *
 * Aucun accès direct aux fichiers JSON ici : tout passe par le DAO.
 */
abstract class BaseService
{
    // ── Dépendance injectée ───────────────────────────────────────────────────

    protected DAOInterface $dao;

    // ── Constructeur ──────────────────────────────────────────────────────────

    /**
     * @param DAOInterface $dao DAO concret injecté (ThemeDAO ou SessionDAO)
     */
    public function __construct(DAOInterface $dao)
    {
        $this->dao = $dao;
    }

    // ── Méthode abstraite de validation ───────────────────────────────────────

    /**
     * Valide les données d'entrée et lève ValidationException si invalides.
     *
     * Implémentée par ThemeService et SessionService avec leurs règles propres.
     *
     * @param  array<string, mixed> $data
     * @throws ValidationException
     */
    abstract protected function validate(array $data): void;

    /**
     * Retourne le nom lisible de la ressource gérée (pour les messages d'erreur).
     * Exemple : 'Thème', 'Session'
     */
    abstract protected function getNomRessource(): string;

    // ── CRUD public ───────────────────────────────────────────────────────────

    /**
     * Retourne tous les enregistrements.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return $this->dao->findAll();
    }

    /**
     * Retourne un enregistrement par son identifiant.
     *
     * @param  int   $id
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function getById(int $id): array
    {
        $enregistrement = $this->dao->findById($id);
        if ($enregistrement === null) {
            throw new NotFoundException($this->getNomRessource(), $id);
        }
        return $enregistrement;
    }

    /**
     * Valide puis crée un nouvel enregistrement.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed> Enregistrement créé avec son nouvel id
     * @throws ValidationException
     */
    public function create(array $data): array
    {
        $this->validate($data);
        return $this->dao->insert($data);
    }

    /**
     * Valide puis met à jour un enregistrement existant.
     *
     * @param  int                  $id
     * @param  array<string, mixed> $data
     * @return array<string, mixed> Enregistrement mis à jour
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function update(int $id, array $data): array
    {
        // Vérifier l'existence avant de valider
        $this->getById($id);
        $this->validate($data);

        $resultat = $this->dao->update($id, $data);
        if ($resultat === null) {
            throw new NotFoundException($this->getNomRessource(), $id);
        }

        return $resultat;
    }

    /**
     * Supprime un enregistrement existant.
     *
     * @param  int  $id
     * @throws NotFoundException
     */
    public function delete(int $id): void
    {
        // Vérifier l'existence avant la suppression
        $this->getById($id);

        if (!$this->dao->delete($id)) {
            throw new NotFoundException($this->getNomRessource(), $id);
        }
    }
}
