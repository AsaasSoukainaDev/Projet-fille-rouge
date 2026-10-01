<?php

require_once __DIR__ . '/../models/Theme.php';

/**
 * ThemeDAO — Data Access Object pour les thèmes
 * 
 * Responsabilité unique : lecture et écriture dans themes.json.
 * Aucune logique métier ici, uniquement l'accès aux données.
 */
class ThemeDAO
{
    /** @var string Chemin absolu vers le fichier de données */
    private string $fichier;

    public function __construct()
    {
        // Chemin vers themes.json, résolu depuis ce fichier
        $this->fichier = __DIR__ . '/../data/themes.json';
    }

    // -------------------------------------------------------------------------
    // Lecture
    // -------------------------------------------------------------------------

    /**
     * Retourne tous les thèmes sous forme d'objets Theme.
     *
     * @return Theme[]
     */
    public function findAll(): array
    {
        $donnees = $this->lireFichier();
        return array_map(fn($item) => Theme::fromArray($item), $donnees);
    }

    /**
     * Retourne un thème par son identifiant, ou null si introuvable.
     *
     * @param int $id Identifiant du thème
     * @return Theme|null
     */
    public function findById(int $id): ?Theme
    {
        $donnees = $this->lireFichier();
        foreach ($donnees as $item) {
            if ((int)$item['id_theme'] === $id) {
                return Theme::fromArray($item);
            }
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Écriture
    // -------------------------------------------------------------------------

    /**
     * Enregistre un nouveau thème dans le fichier JSON.
     * Attribue automatiquement un nouvel ID.
     *
     * @param Theme $theme Objet Theme à persister
     * @return Theme L'objet avec son ID attribué
     */
    public function save(Theme $theme): Theme
    {
        $donnees = $this->lireFichier();
        $theme->setIdTheme($this->getNextId($donnees));
        $donnees[] = $theme->toArray();
        $this->ecrireFichier($donnees);
        return $theme;
    }

    /**
     * Met à jour un thème existant dans le fichier JSON.
     *
     * @param Theme $theme Objet Theme modifié
     * @return bool true si la mise à jour a réussi, false si introuvable
     */
    public function update(Theme $theme): bool
    {
        $donnees = $this->lireFichier();
        $trouve  = false;

        foreach ($donnees as &$item) {
            if ((int)$item['id_theme'] === $theme->getIdTheme()) {
                $item   = $theme->toArray();
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
     * Supprime un thème du fichier JSON par son identifiant.
     *
     * @param int $id Identifiant du thème
     * @return bool true si supprimé, false si introuvable
     */
    public function delete(int $id): bool
    {
        $donnees  = $this->lireFichier();
        $initiale = count($donnees);

        $donnees = array_values(
            array_filter($donnees, fn($item) => (int)$item['id_theme'] !== $id)
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
        $ids = array_column($donnees, 'id_theme');
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
