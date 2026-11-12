<?php

declare(strict_types=1);

namespace App\DAO;

use App\Exceptions\DataAccessException;

/**
 * Classe abstraite gérant la persistance dans un fichier JSON.
 *
 * Fournit les opérations CRUD de bas niveau (lecture, écriture, verrouillage)
 * et délègue à JsonDAO::getIdKey() le nom du champ identifiant spécifique
 * à chaque entité (ex: 'id' pour Theme et Session).
 *
 * Les classes concrètes (ThemeDAO, SessionDAO) héritent de JsonDAO
 * et n'ont pas à se soucier de la persistance.
 */
abstract class JsonDAO implements DAOInterface
{
    // ── Chemin absolu du fichier JSON ─────────────────────────────────────────

    private string $filePath;

    // ── Constructeur ──────────────────────────────────────────────────────────

    /**
     * @param string $filePath Chemin absolu vers le fichier JSON de données
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->initialiserFichier();
    }

    // ── Méthode abstraite ─────────────────────────────────────────────────────

    /**
     * Retourne le nom du champ identifiant dans les tableaux de données.
     * Exemple : 'id' (commun à Theme et Session).
     */
    abstract protected function getIdKey(): string;

    // ── Lecture / écriture JSON ───────────────────────────────────────────────

    /**
     * Lit et retourne tous les enregistrements du fichier JSON.
     *
     * @return array<int, array<string, mixed>>
     * @throws DataAccessException
     */
    protected function lireTout(): array
    {
        // Ouvrir en lecture/écriture pour pouvoir verrouiller
        $handle = @fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new DataAccessException(
                sprintf('Impossible d\'ouvrir le fichier : %s', $this->filePath)
            );
        }

        try {
            // Verrou partagé (lecture simultanée autorisée)
            flock($handle, LOCK_SH);
            $contenu = stream_get_contents($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        if ($contenu === false || $contenu === '') {
            return [];
        }

        $donnees = json_decode($contenu, true);
        if (!is_array($donnees)) {
            throw new DataAccessException(
                sprintf('Le fichier JSON est corrompu ou invalide : %s', $this->filePath)
            );
        }

        return $donnees;
    }

    /**
     * Écrit le tableau complet dans le fichier JSON (écrase le contenu).
     *
     * @param  array<int, array<string, mixed>> $donnees
     * @throws DataAccessException
     */
    protected function ecrireTout(array $donnees): void
    {
        $handle = @fopen($this->filePath, 'c+');
        if ($handle === false) {
            throw new DataAccessException(
                sprintf('Impossible d\'ouvrir le fichier en écriture : %s', $this->filePath)
            );
        }

        // Verrou exclusif pendant toute l'écriture
        flock($handle, LOCK_EX);

        try {
            ftruncate($handle, 0);
            rewind($handle);

            $json = json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new DataAccessException('Impossible d\'encoder les données en JSON.');
            }

            if (fwrite($handle, $json) === false) {
                throw new DataAccessException(
                    sprintf('Écriture impossible dans le fichier : %s', $this->filePath)
                );
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Génère le prochain identifiant auto-incrémenté.
     *
     * @param  array<int, array<string, mixed>> $donnees Enregistrements existants
     * @return int
     */
    protected function prochainId(array $donnees): int
    {
        if (empty($donnees)) {
            return 1;
        }

        $ids = array_column($donnees, $this->getIdKey());
        return (int) max($ids) + 1;
    }

    /**
     * Crée le fichier JSON s'il n'existe pas encore.
     *
     * @throws DataAccessException
     */
    private function initialiserFichier(): void
    {
        $repertoire = dirname($this->filePath);

        if (!is_dir($repertoire) && !mkdir($repertoire, 0755, true)) {
            throw new DataAccessException(
                sprintf('Impossible de créer le répertoire de données : %s', $repertoire)
            );
        }

        if (!file_exists($this->filePath)) {
            if (file_put_contents($this->filePath, '[]') === false) {
                throw new DataAccessException(
                    sprintf('Impossible de créer le fichier JSON : %s', $this->filePath)
                );
            }
        }
    }

    // ── Implémentation de DAOInterface ────────────────────────────────────────

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->lireTout();
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?array
    {
        $donnees = $this->lireTout();
        $idKey   = $this->getIdKey();

        foreach ($donnees as $enregistrement) {
            if ((int)$enregistrement[$idKey] === $id) {
                return $enregistrement;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $data): array
    {
        $donnees        = $this->lireTout();
        $data[$this->getIdKey()] = $this->prochainId($donnees);
        $donnees[]      = $data;
        $this->ecrireTout($donnees);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): ?array
    {
        $donnees = $this->lireTout();
        $idKey   = $this->getIdKey();
        $trouve  = false;
        $resultat = null;

        foreach ($donnees as &$enregistrement) {
            if ((int)$enregistrement[$idKey] === $id) {
                // Préserver l'identifiant existant
                $data[$idKey]  = $id;
                $enregistrement = $data;
                $resultat       = $data;
                $trouve         = true;
                break;
            }
        }
        unset($enregistrement);

        if (!$trouve) {
            return null;
        }

        $this->ecrireTout($donnees);
        return $resultat;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $donnees  = $this->lireTout();
        $idKey    = $this->getIdKey();
        $originale = count($donnees);

        $donnees = array_values(
            array_filter($donnees, fn($e) => (int)$e[$idKey] !== $id)
        );

        if (count($donnees) === $originale) {
            return false; // Rien n'a été supprimé
        }

        $this->ecrireTout($donnees);
        return true;
    }
}
