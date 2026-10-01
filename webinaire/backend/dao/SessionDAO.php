<?php

require_once __DIR__ . '/../models/Session.php';

/**
 * SessionDAO — Data Access Object pour les sessions de webinaires
 * 
 * Responsabilité unique : lecture et écriture dans sessions.json.
 * Aucune logique métier ici, uniquement l'accès aux données.
 */
class SessionDAO
{
    /** @var string Chemin absolu vers le fichier de données */
    private string $fichier;

    public function __construct()
    {
        // Chemin vers sessions.json, résolu depuis ce fichier
        $this->fichier = __DIR__ . '/../data/sessions.json';
    }

    // -------------------------------------------------------------------------
    // Lecture
    // -------------------------------------------------------------------------

    /**
     * Retourne toutes les sessions sous forme d'objets Session.
     *
     * @return Session[]
     */
    public function findAll(): array
    {
        $donnees = $this->lireFichier();
        return array_map(fn($item) => Session::fromArray($item), $donnees);
    }

    /**
     * Retourne une session par son identifiant, ou null si introuvable.
     *
     * @param int $id Identifiant de la session
     * @return Session|null
     */
    public function findById(int $id): ?Session
    {
        $donnees = $this->lireFichier();
        foreach ($donnees as $item) {
            if ((int)$item['id_session'] === $id) {
                return Session::fromArray($item);
            }
        }
        return null;
    }

    /**
     * Retourne toutes les sessions associées à un thème donné.
     *
     * @param int $idTheme Identifiant du thème
     * @return Session[]
     */
    public function findByTheme(int $idTheme): array
    {
        $donnees = $this->lireFichier();
        $resultat = array_filter($donnees, fn($item) => (int)$item['id_theme'] === $idTheme);
        return array_map(fn($item) => Session::fromArray($item), array_values($resultat));
    }

    // -------------------------------------------------------------------------
    // Écriture
    // -------------------------------------------------------------------------

    /**
     * Enregistre une nouvelle session dans le fichier JSON.
     * Attribue automatiquement un nouvel ID.
     *
     * @param Session $session Objet Session à persister
     * @return Session L'objet avec son ID attribué
     */
    public function save(Session $session): Session
    {
        $donnees = $this->lireFichier();
        $session->setIdSession($this->getNextId($donnees));
        $donnees[] = $session->toArray();
        $this->ecrireFichier($donnees);
        return $session;
    }

    /**
     * Met à jour une session existante dans le fichier JSON.
     *
     * @param Session $session Objet Session modifié
     * @return bool true si la mise à jour a réussi, false si introuvable
     */
    public function update(Session $session): bool
    {
        $donnees = $this->lireFichier();
        $trouve  = false;

        foreach ($donnees as &$item) {
            if ((int)$item['id_session'] === $session->getIdSession()) {
                $item   = $session->toArray();
                $trouve = true;
                break;
            }
        }
        unset($item); // Nettoyage de la référence

        if ($trouve) {
            $this->ecrireFichier($donnees);
        }
        return $trouve;
    }

    /**
     * Supprime une session du fichier JSON par son identifiant.
     *
     * @param int $id Identifiant de la session
     * @return bool true si supprimée, false si introuvable
     */
    public function delete(int $id): bool
    {
        $donnees  = $this->lireFichier();
        $initiale = count($donnees);

        $donnees = array_values(
            array_filter($donnees, fn($item) => (int)$item['id_session'] !== $id)
        );

        if (count($donnees) < $initiale) {
            $this->ecrireFichier($donnees);
            return true;
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // Auto-incrémentation
    // -------------------------------------------------------------------------

    /**
     * Calcule le prochain identifiant disponible.
     *
     * @param array $donnees Données actuelles
     * @return int Prochain ID
     */
    public function getNextId(array $donnees = []): int
    {
        if (empty($donnees)) {
            $donnees = $this->lireFichier();
        }
        if (empty($donnees)) {
            return 1;
        }
        $ids = array_column($donnees, 'id_session');
        return max($ids) + 1;
    }

    // -------------------------------------------------------------------------
    // Méthodes privées — gestion du fichier
    // -------------------------------------------------------------------------

    /**
     * Lit le fichier JSON et retourne un tableau PHP.
     *
     * @return array
     */
    private function lireFichier(): array
    {
        if (!file_exists($this->fichier)) {
            return [];
        }
        $contenu = file_get_contents($this->fichier);
        if ($contenu === false || trim($contenu) === '') {
            return [];
        }
        $donnees = json_decode($contenu, true);
        return is_array($donnees) ? $donnees : [];
    }

    /**
     * Écrit un tableau PHP dans le fichier JSON avec verrouillage exclusif.
     *
     * @param array $donnees Données à persister
     * @throws RuntimeException Si l'écriture échoue
     */
    private function ecrireFichier(array $donnees): void
    {
        $json = json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Ouverture en mode écriture avec verrouillage exclusif
        $handle = fopen($this->fichier, 'c');
        if ($handle === false) {
            throw new RuntimeException("Impossible d'ouvrir le fichier : {$this->fichier}");
        }

        if (flock($handle, LOCK_EX)) {
            ftruncate($handle, 0);  // Vider le fichier
            rewind($handle);
            fwrite($handle, $json);
            fflush($handle);
            flock($handle, LOCK_UN); // Libérer le verrou
        } else {
            fclose($handle);
            throw new RuntimeException("Impossible de verrouiller le fichier : {$this->fichier}");
        }
        fclose($handle);
    }
}
