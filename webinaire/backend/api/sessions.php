<?php

require_once __DIR__ . '/../../autoload.php';

use App\DAO\SessionDAO;
use App\DAO\ThemeDAO;
use App\Exceptions\AppException;
use App\Exceptions\ValidationException;
use App\Services\SessionService;

/**
 * API REST — Sessions de webinaires
 * 
 * Endpoints disponibles :
 *   GET    /api/sessions.php          → Liste toutes les sessions
 *   GET    /api/sessions.php?id=X     → Récupère une session par ID
 *   POST   /api/sessions.php          → Crée une session (body JSON)
 *   PUT    /api/sessions.php?id=X     → Modifie une session (body JSON)
 *   DELETE /api/sessions.php?id=X     → Supprime une session
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$sessionDao = new SessionDAO(__DIR__ . '/../data/sessions.json');
$themeDao = new ThemeDAO(__DIR__ . '/../data/themes.json');
$service = new SessionService($sessionDao, $themeDao);
$methode = $_SERVER['REQUEST_METHOD'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── Routage par méthode HTTP ────────────────────────────────────────────────
try {
    switch ($methode) {

        // ── GET : liste ou détail ─────────────────────────────────────────
        case 'GET':
            if ($id !== null) {
                $session = $service->getById($id);
                repondre(200, $session);
            }
            $sessions = $service->getAll();
            repondre(200, $sessions);
            break;

        case 'POST':
            $data = lireCorpsJson();
            $session = $service->create($data);
            repondre(201, [
                'message' => 'Session créée avec succès.',
                'session' => $session,
            ]);
            break;

        case 'PUT':
            if ($id === null) {
                repondre(400, ['erreur' => 'Paramètre id manquant.']);
            }
            $data = lireCorpsJson();
            $session = $service->update($id, $data);
            repondre(200, [
                'message' => 'Session modifiée avec succès.',
                'session' => $session,
            ]);
            break;

        case 'DELETE':
            if ($id === null) {
                repondre(400, ['erreur' => 'Paramètre id manquant.']);
            }
            $service->delete($id);
            repondre(200, ['message' => "Session #$id supprimée avec succès."]);
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
