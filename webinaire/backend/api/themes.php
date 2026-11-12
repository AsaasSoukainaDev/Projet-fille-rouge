<?php

require_once __DIR__ . '/../../autoload.php';

use App\DAO\ThemeDAO;
use App\DAO\SessionDAO;
use App\Exceptions\AppException;
use App\Exceptions\ValidationException;
use App\Services\ThemeService;

/**
 * API REST — Thèmes
 * 
 * Endpoints disponibles :
 *   GET    /api/themes.php          → Liste tous les thèmes
 *   GET    /api/themes.php?id=X     → Récupère un thème par ID
 *   POST   /api/themes.php          → Crée un thème (body JSON)
 *   PUT    /api/themes.php?id=X     → Modifie un thème (body JSON)
 *   DELETE /api/themes.php?id=X     → Supprime un thème
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$themeDao = new ThemeDAO(__DIR__ . '/../data/themes.json');
$sessionDao = new SessionDAO(__DIR__ . '/../data/sessions.json');
$service = new ThemeService($themeDao, $sessionDao);
$methode = $_SERVER['REQUEST_METHOD'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── Routage par méthode HTTP ────────────────────────────────────────────────
try {
    switch ($methode) {

        // ── GET : liste ou détail ─────────────────────────────────────────
        case 'GET':
            if ($id !== null) {
                $theme = $service->getById($id);
                repondre(200, $theme);
            }
            $themes = $service->getAll();
            repondre(200, $themes);
            break;

        case 'POST':
            $data = lireCorpsJson();
            $theme = $service->create($data);
            repondre(201, [
                'message' => 'Thème créé avec succès.',
                'theme' => $theme,
            ]);
            break;

        case 'PUT':
            if ($id === null) {
                repondre(400, ['erreur' => 'Paramètre id manquant.']);
            }
            $data = lireCorpsJson();
            $theme = $service->update($id, $data);
            repondre(200, [
                'message' => 'Thème modifié avec succès.',
                'theme' => $theme,
            ]);
            break;

        case 'DELETE':
            if ($id === null) {
                repondre(400, ['erreur' => 'Paramètre id manquant.']);
            }
            $service->delete($id);
            repondre(200, ['message' => "Thème #$id supprimé avec succès."]);
            break;

        // ── Méthode non supportée ─────────────────────────────────────────
        default:
            repondre(405, ['erreur' => "Méthode HTTP '$methode' non supportée."]);
    }

} catch (ValidationException $e) {
    repondre(422, ['erreurs' => $e->getErrors()]);
} catch (AppException $e) {
    repondre($e->getCode(), ['erreur' => $e->getMessage()]);
} catch (\Throwable $e) {
    repondre(500, ['erreur' => 'Erreur interne : ' . $e->getMessage()]);
}

// ── Fonctions utilitaires ───────────────────────────────────────────────────

/**
 * Envoie une réponse JSON avec le code HTTP approprié et termine le script.
 *
 * @param int   $code Code HTTP
 * @param mixed $data Données à encoder en JSON
 */
function repondre(int $code, mixed $data): never
{
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Lit et décode le corps de la requête en tant que JSON.
 *
 * @return array Données décodées
 */
function lireCorpsJson(): array
{
    $corps = file_get_contents('php://input');
    if (empty($corps)) {
        repondre(400, ['erreur' => 'Corps de requête JSON vide ou absent.']);
    }
    $data = json_decode($corps, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        repondre(400, ['erreur' => 'JSON invalide : ' . json_last_error_msg()]);
    }
    return $data ?? [];
}
