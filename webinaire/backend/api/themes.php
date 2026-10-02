<?php

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
require_once __DIR__ . '/../services/ThemeService.php';

$service = new ThemeService();
$methode = $_SERVER['REQUEST_METHOD'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── Routage par méthode HTTP ────────────────────────────────────────────────
try {
    switch ($methode) {

        // ── GET : liste ou détail ─────────────────────────────────────────
        case 'GET':
            if ($id !== null) {
                // Détail d'un thème par ID
                $theme = $service->trouver($id);
                if ($theme === null) {
                    repondre(404, ['erreur' => "Thème #$id introuvable."]);
                }
                repondre(200, $theme->toArray());
            } else {
                // Liste de tous les thèmes
                $themes = $service->lister();
                $data   = array_map(fn($t) => $t->toArray(), $themes);
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
                'message' => 'Thème créé avec succès.',
                'theme'   => $res['theme']->toArray(),
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
                'message' => 'Thème modifié avec succès.',
                'theme'   => $res['theme']->toArray(),
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
            repondre(200, ['message' => "Thème #$id supprimé avec succès."]);
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
