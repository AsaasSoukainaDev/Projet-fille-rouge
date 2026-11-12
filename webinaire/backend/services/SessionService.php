<?php

declare(strict_types=1);

namespace App\Services;

use App\DAO\ThemeDAO;
use App\Exceptions\{ValidationException, BusinessRuleException};

/**
 * Service métier pour l'entité Session.
 *
 * Règles de validation :
 *  - titre        : obligatoire, 2–200 caractères
 *  - description  : obligatoire, non vide
 *  - date_session : obligatoire, format YYYY-MM-DD valide
 *  - heure_debut  : obligatoire, format HH:MM valide
 *  - duree        : entier entre 1 et 480 (minutes)
 *  - lien_visio   : obligatoire, URL commençant par http:// ou https://
 *  - id_theme     : entier > 0, doit référencer un thème existant
 */
class SessionService extends BaseService
{
    // ── Dépendance vers ThemeDAO (pour vérifier l'existence du thème) ─────────

    private ThemeDAO $themeDao;

    // ── Constructeur ──────────────────────────────────────────────────────────

    /**
     * @param \App\DAO\SessionDAO $sessionDao DAO des sessions
     * @param ThemeDAO            $themeDao   DAO des thèmes (vérification id_theme)
     */
    public function __construct(\App\DAO\SessionDAO $sessionDao, ThemeDAO $themeDao)
    {
        parent::__construct($sessionDao);
        $this->themeDao = $themeDao;
    }

    // ── Implémentation des méthodes abstraites ────────────────────────────────

    /**
     * {@inheritdoc}
     */
    protected function getNomRessource(): string
    {
        return 'Session';
    }

    /**
     * Valide les données d'une session.
     *
     * @param  array<string, mixed> $data
     * @throws ValidationException
     * @throws BusinessRuleException Si le thème référencé n'existe pas
     */
    protected function validate(array $data): void
    {
        $erreurs = [];

        // ── Titre ─────────────────────────────────────────────────────────────
        $titre = trim((string)($data['titre'] ?? ''));
        if ($titre === '') {
            $erreurs[] = 'Le titre est obligatoire.';
        } elseif (strlen($titre) < 2) {
            $erreurs[] = 'Le titre doit contenir au moins 2 caractères.';
        } elseif (strlen($titre) > 200) {
            $erreurs[] = 'Le titre ne peut pas dépasser 200 caractères.';
        }

        // ── Description ───────────────────────────────────────────────────────
        $description = trim((string)($data['description'] ?? ''));
        if ($description === '') {
            $erreurs[] = 'La description est obligatoire.';
        }

        // ── Date de session ───────────────────────────────────────────────────
        $dateSession = trim((string)($data['date_session'] ?? ''));
        if ($dateSession === '') {
            $erreurs[] = 'La date de session est obligatoire.';
        } elseif (!$this->validerDate($dateSession)) {
            $erreurs[] = 'La date de session doit être au format YYYY-MM-DD valide.';
        }

        // ── Heure de début ────────────────────────────────────────────────────
        $heureDebut = trim((string)($data['heure_debut'] ?? ''));
        if ($heureDebut === '') {
            $erreurs[] = 'L\'heure de début est obligatoire.';
        } elseif (!$this->validerHeure($heureDebut)) {
            $erreurs[] = 'L\'heure de début doit être au format HH:MM valide (00:00 – 23:59).';
        }

        // ── Durée ─────────────────────────────────────────────────────────────
        $duree = (int)($data['duree'] ?? 0);
        if ($duree < 1 || $duree > 480) {
            $erreurs[] = 'La durée doit être un entier entre 1 et 480 minutes.';
        }

        // ── Lien visio ────────────────────────────────────────────────────────
        $lienVisio = trim((string)($data['lien_visio'] ?? ''));
        if ($lienVisio === '') {
            $erreurs[] = 'Le lien de visioconférence est obligatoire.';
        } elseif (!preg_match('#^https?://.+#i', $lienVisio)) {
            $erreurs[] = 'Le lien de visioconférence doit être une URL valide (http:// ou https://).';
        }

        // ── Identifiant du thème ───────────────────────────────────────────────
        $idTheme = (int)($data['id_theme'] ?? 0);
        if ($idTheme <= 0) {
            $erreurs[] = 'Un thème doit être sélectionné.';
        }

        // Si des erreurs de format, on les remonte avant la vérification BDD
        if (!empty($erreurs)) {
            throw new ValidationException($erreurs);
        }

        // Vérifier que le thème existe réellement (règle métier, pas de validation)
        if ($this->themeDao->findById($idTheme) === null) {
            throw new BusinessRuleException(
                sprintf('Le thème avec l\'identifiant %d n\'existe pas.', $idTheme)
            );
        }
    }

    // ── Méthodes de validation de format privées ──────────────────────────────

    /**
     * Vérifie que la chaîne est une date valide au format YYYY-MM-DD.
     */
    private function validerDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$annee, $mois, $jour] = explode('-', $date);
        return checkdate((int)$mois, (int)$jour, (int)$annee);
    }

    /**
     * Vérifie que la chaîne est une heure valide au format HH:MM (00:00 – 23:59).
     */
    private function validerHeure(string $heure): bool
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $heure)) {
            return false;
        }
        [$h, $m] = explode(':', $heure);
        return (int)$h >= 0 && (int)$h <= 23 && (int)$m >= 0 && (int)$m <= 59;
    }
}
