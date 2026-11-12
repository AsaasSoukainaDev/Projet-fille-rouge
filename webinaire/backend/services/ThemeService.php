<?php

declare(strict_types=1);

namespace App\Services;

use App\DAO\SessionDAO;
use App\Exceptions\{BusinessRuleException, ValidationException};

/**
 * Service métier pour l'entité Thème.
 *
 * Règles de validation :
 *  - libelle  : obligatoire, non vide, entre 2 et 100 caractères
 *
 * Règle métier supplémentaire :
 *  - Un thème ne peut pas être supprimé s'il contient encore des sessions.
 *    Cette vérification utilise SessionDAO injecté en dépendance.
 */
class ThemeService extends BaseService
{
    // ── Dépendance vers SessionDAO (pour la règle de suppression) ─────────────

    private SessionDAO $sessionDao;

    // ── Constructeur ──────────────────────────────────────────────────────────

    /**
     * @param \App\DAO\ThemeDAO $themeDao   DAO des thèmes
     * @param SessionDAO        $sessionDao DAO des sessions (vérification contrainte)
     */
    public function __construct(\App\DAO\ThemeDAO $themeDao, SessionDAO $sessionDao)
    {
        parent::__construct($themeDao);
        $this->sessionDao = $sessionDao;
    }

    // ── Implémentation des méthodes abstraites ────────────────────────────────

    /**
     * {@inheritdoc}
     */
    protected function getNomRessource(): string
    {
        return 'Thème';
    }

    /**
     * Valide les données d'un thème.
     *
     * @param  array<string, mixed> $data
     * @throws ValidationException
     */
    protected function validate(array $data): void
    {
        $erreurs = [];

        // ── Libellé ───────────────────────────────────────────────────────────
        $libelle = trim((string)($data['libelle'] ?? ''));

        if ($libelle === '') {
            $erreurs[] = 'Le libellé est obligatoire.';
        } elseif (strlen($libelle) < 2) {
            $erreurs[] = 'Le libellé doit contenir au moins 2 caractères.';
        } elseif (strlen($libelle) > 100) {
            $erreurs[] = 'Le libellé ne peut pas dépasser 100 caractères.';
        }

        if (!empty($erreurs)) {
            throw new ValidationException($erreurs);
        }
    }

    // ── Surcharge de delete avec règle métier ──────────────────────────────────

    /**
     * Supprime un thème uniquement s'il ne possède aucune session associée.
     *
     * @param  int  $id
     * @throws BusinessRuleException Si des sessions sont encore liées à ce thème
     * @throws \App\Exceptions\NotFoundException
     */
    public function delete(int $id): void
    {
        // Vérifier l'existence du thème
        $this->getById($id);

        // Vérifier qu'aucune session n'est liée
        $sessionsLiees = $this->sessionDao->findByThemeId($id);
        if (!empty($sessionsLiees)) {
            throw new BusinessRuleException(sprintf(
                'Impossible de supprimer ce thème : %d session(s) lui sont encore associée(s).',
                count($sessionsLiees)
            ));
        }

        parent::delete($id);
    }
}
