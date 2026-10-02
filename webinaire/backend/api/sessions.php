<?php

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

// ── En-têtes CORS et contenu JSON ──────────────────────────────────────────
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Répondre aux requêtes de pré-vol OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── Chargement des dépendances ──────────────────────────────────────────────
require_once __DIR__ . '/../services/SessionService.php';

$service = new SessionService();
$methode = $_SERVER['REQUEST_METHOD'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── Routage par méthode HTTP ────────────────────────────────────────────────
try {
    switch ($methode) {

        // ── GET : liste ou détail ─────────────────────────────────────────
        case 'GET':
            if ($id !== null) {
                // Détail d'une session par ID
                $session = $service->trouver($id);
                if ($session === null) {
                    repondre(404, ['erreur' => "Session #$id introuvable."]);
                }
                repondre(200, $session->toArray());
            } else {
                // Liste de toutes les sessions
                $sessions = $service->lister();
                $data     = array_map(fn($s) => $s->toArray(), $sessions);
                repondre(200, $data);
            }
            break;

        // ── POST : création ───────────────────────────────────────────────
        case 'POST':
            $corps = lireCorpsJson();
            $res   = $service->creer($corps);

            if (!$res['succes']) {
                repondre(422, ['erreurs' => $res['erreurs']]);
            }
            repondre(201, [
                'message'  => 'Session créée avec succès.',
                'session'  => $res['session']->toArray(),
            ]);
            break;

        // ── PUT : modification ────────────────────────────────────────────
        case 'PUT':
            if ($id === null) {
                repondre(400, ['erreur' => 'Paramètre id manquant.']);
            }
            $corps = lireCorpsJson();
            $res   = $service->modifier($id, $corps);

            if (!$res['succes']) {
                $code = str_contains($res['erreurs'][0] ?? '', 'introuvable') ? 404 : 422;
                repondre($code, ['erreurs' => $res['erreurs']]);
            }
            repondre(200, [
                'message' => 'Session modifiée avec succès.',
                'session' => $res['session']->toArray(),
            ]);
            break;

        // ── DELETE : suppression ──────────────────────────────────────────
        case 'DELETE':
            if ($id === null) {
                repondre(400, ['erreur' => 'Paramètre id manquant.']);
            }
            $res = $service->supprimer($id);

            if (!$res['succes']) {
                repondre(404, ['erreurs' => $res['erreurs']]);
            }
            repondre(200, ['message' => "Session #$id supprimée avec succès."]);
            break;

        // ── Méthode non supportée ─────────────────────────────────────────
        default:
            repondre(405, ['erreur' => "Méthode HTTP '$methode' non supportée."]);
    }

} catch (Throwable $e) {
    // Erreur serveur inattendue
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
