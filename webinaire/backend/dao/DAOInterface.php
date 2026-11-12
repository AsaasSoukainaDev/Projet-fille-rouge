<?php

declare(strict_types=1);

namespace App\DAO;

/**
 * Interface définissant le contrat CRUD pour tous les DAO.
 *
 * Chaque méthode travaille avec des tableaux associatifs
 * afin de rester indépendant du modèle concret.
 */
interface DAOInterface
{
    /**
     * Retourne tous les enregistrements.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array;

    /**
     * Retourne un enregistrement par son identifiant, ou null s'il n'existe pas.
     *
     * @param  int   $id
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array;

    /**
     * Insère un nouvel enregistrement et retourne le tableau avec l'id assigné.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function insert(array $data): array;

    /**
     * Met à jour un enregistrement existant et retourne le tableau mis à jour.
     *
     * @param  int                  $id
     * @param  array<string, mixed> $data
     * @return array<string, mixed>|null  Null si l'id n'existe pas
     */
    public function update(int $id, array $data): ?array;

    /**
     * Supprime un enregistrement. Retourne true si supprimé, false si introuvable.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;
}
