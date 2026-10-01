/**
 * api.js — Couche d'abstraction Fetch API
 *
 * Centralise tous les appels HTTP vers le backend PHP.
 * Aucun appel fetch ne doit être fait en dehors de ce fichier.
 */

/** Base URL du backend API (relatif à frontend/) */
const API_URL = '../backend/api';

/**
 * GET — Récupère une liste ou un élément unique.
 *
 * @param {string}      endpoint  Nom de l'endpoint (ex: 'themes')
 * @param {number|null} id        Identifiant optionnel pour un détail
 * @returns {Promise<any>}
 */
async function apiGet(endpoint, id = null) {
    const url = id !== null
        ? `${API_URL}/${endpoint}.php?id=${id}`
        : `${API_URL}/${endpoint}.php`;

    const rep = await fetch(url, { method: 'GET', headers: { 'Content-Type': 'application/json' } });

    if (!rep.ok) {
        const err = await rep.json().catch(() => ({}));
        throw new Error(err.erreur || `Erreur HTTP ${rep.status}`);
    }
    return rep.json();
}

/**
 * POST — Crée une nouvelle ressource.
 *
 * @param {string} endpoint  Nom de l'endpoint
 * @param {Object} data      Données à envoyer en JSON
 * @returns {Promise<any>}
 */
async function apiPost(endpoint, data) {
    const rep = await fetch(`${API_URL}/${endpoint}.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    const corps = await rep.json().catch(() => ({}));
    if (!rep.ok) {
        const msg = corps.erreurs ? corps.erreurs.join('\n') : (corps.erreur || `Erreur HTTP ${rep.status}`);
        throw new Error(msg);
    }
    return corps;
}

/**
 * PUT — Met à jour une ressource existante.
 *
 * @param {string} endpoint  Nom de l'endpoint
 * @param {number} id        Identifiant de la ressource
 * @param {Object} data      Nouvelles données
 * @returns {Promise<any>}
 */
async function apiPut(endpoint, id, data) {
    const rep = await fetch(`${API_URL}/${endpoint}.php?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    const corps = await rep.json().catch(() => ({}));
    if (!rep.ok) {
        const msg = corps.erreurs ? corps.erreurs.join('\n') : (corps.erreur || `Erreur HTTP ${rep.status}`);
        throw new Error(msg);
    }
    return corps;
}

/**
 * DELETE — Supprime une ressource.
 *
 * @param {string} endpoint  Nom de l'endpoint
 * @param {number} id        Identifiant de la ressource
 * @returns {Promise<any>}
 */
async function apiDelete(endpoint, id) {
    const rep = await fetch(`${API_URL}/${endpoint}.php?id=${id}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
    });
    const corps = await rep.json().catch(() => ({}));
    if (!rep.ok) {
        const msg = corps.erreurs ? corps.erreurs.join('\n') : (corps.erreur || `Erreur HTTP ${rep.status}`);
        throw new Error(msg);
    }
    return corps;
}
