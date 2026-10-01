/**
 * sessions.js — Logique de l'onglet Sessions
 *
 * Responsabilités :
 *  - Charger et afficher les sessions dans le tableau
 *  - Alimenter le <select> de thèmes dynamiquement
 *  - Gérer le formulaire de création / modification
 *  - Gérer la suppression avec confirmation
 *
 * Dépendances : api.js, themes.js (pour la liste de thèmes), app.js
 */

// ── Badges de statut ─────────────────────────────────────────────────────────
const STATUT_BADGE = {
    'planifiée': 'bg-blue-100 text-blue-700',
    'en cours':  'bg-yellow-100 text-yellow-700',
    'terminée':  'bg-green-100 text-green-700',
};

// ── Cache des thèmes (pour afficher le libellé dans le tableau) ───────────────
let _themesCache = {};

// ── Références DOM ───────────────────────────────────────────────────────────
const _formSession       = () => document.getElementById('form-session');
const _tbodySessions     = () => document.getElementById('tbody-sessions');
const _compteurSessions  = () => document.getElementById('compteur-sessions');
const _badgeSessions     = () => document.getElementById('badge-sessions');
const _selectTheme       = () => document.getElementById('session-id-theme');
const _btnSessionSubmit  = () => document.getElementById('btn-session-submit');
const _btnSessionAnnuler = () => document.getElementById('btn-session-annuler');
const _formSessionTitre  = () => document.getElementById('form-session-titre');

/** ID de la session en cours d'édition, ou null si création */
let _sessionEnEdition = null;

// ── Chargement ───────────────────────────────────────────────────────────────

/**
 * Charge toutes les sessions depuis l'API et rafraîchit l'affichage.
 * Exposé globalement pour le bouton "Rafraîchir".
 */
async function chargerSessions() {
    try {
        const sessions = await apiGet('sessions');
        _afficherSessions(sessions);
        // Mise à jour badge sidebar et stats
        const badge = _badgeSessions();
        if (badge) badge.textContent = sessions.length;
        _mettreAJourStats(sessions);
        _mettreAJourDashboard(sessions);
    } catch (err) {
        afficherToast('Erreur chargement des sessions : ' + err.message, 'erreur');
    }
}

/**
 * Charge les thèmes dans le <select> du formulaire session.
 * Met également à jour le cache interne pour le tableau.
 */
async function chargerThemesPourSelect() {
    try {
        const themes = await apiGet('themes');
        // Mettre à jour le cache nom → libellé
        _themesCache = {};
        themes.forEach(t => { _themesCache[t.id_theme] = t.libelle; });

        const sel = _selectTheme();
        if (!sel) return;
        const valActuelle = sel.value;
        sel.innerHTML = `<option value="">— Sélectionner un thème —</option>`;
        themes.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id_theme;
            opt.textContent = t.libelle;
            sel.appendChild(opt);
        });
        // Restaurer la sélection si possible
        if (valActuelle) sel.value = valActuelle;
    } catch (err) {
        afficherToast('Impossible de charger les thèmes : ' + err.message, 'erreur');
    }
}

// ── Affichage tableau ────────────────────────────────────────────────────────

/**
 * Génère les lignes du tableau HTML à partir des données sessions.
 * @param {Array} sessions
 */
function _afficherSessions(sessions) {
    const tbody    = _tbodySessions();
    const compteur = _compteurSessions();
    if (!tbody) return;

    if (compteur) compteur.textContent = sessions.length;

    if (sessions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-8 h-8 text-slate-300">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5"/>
                        </svg>
                        <p class="text-sm text-slate-400">Aucune session. Créez la première ci-dessus.</p>
                    </div>
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = sessions.map(s => {
        const badgeCss = STATUT_BADGE[s.statut] || 'bg-slate-100 text-slate-600';
        const dateStr  = s.date_session
            ? new Date(s.date_session).toLocaleString('fr-FR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
              })
            : '—';
        const themeName = _themesCache[s.id_theme] || `#${s.id_theme}`;

        return `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-6 py-4 text-sm text-slate-400 font-mono">#${s.id_session}</td>
            <td class="px-6 py-4">
                <div>
                    <p class="text-sm font-medium text-slate-900 truncate max-w-[200px]">${_escSess(s.titre)}</p>
                    <p class="text-xs text-slate-400 truncate max-w-[200px]">${_escSess(themeName)}</p>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap">${dateStr}</td>
            <td class="px-6 py-4 text-sm text-slate-600">${s.duree_minutes} min</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeCss}">
                    ${_escSess(s.statut)}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-1">
                    <!-- Modifier -->
                    <button onclick="_editerSession(${s.id_session})"
                        title="Modifier"
                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                        </svg>
                    </button>
                    <!-- Supprimer -->
                    <button onclick="_supprimerSession(${s.id_session}, '${_escSess(s.titre)}')"
                        title="Supprimer"
                        class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ── Mise à jour des statistiques du dashboard ────────────────────────────────

function _mettreAJourStats(sessions) {
    const planifiees = sessions.filter(s => s.statut === 'planifiée').length;
    const terminees  = sessions.filter(s => s.statut === 'terminée').length;

    const el = (id) => document.getElementById(id);
    if (el('stat-total-sessions')) el('stat-total-sessions').textContent = sessions.length;
    if (el('stat-planifiees'))     el('stat-planifiees').textContent     = planifiees;
    if (el('stat-terminees'))      el('stat-terminees').textContent      = terminees;
}

function _mettreAJourDashboard(sessions) {
    const tbody = document.getElementById('tbody-dashboard');
    if (!tbody) return;

    // 5 prochaines sessions (planifiées en premier, puis par date)
    const prochaines = [...sessions]
        .sort((a, b) => new Date(a.date_session) - new Date(b.date_session))
        .slice(0, 5);

    if (prochaines.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-10 text-center text-slate-400 text-sm">Aucune session enregistrée.</td></tr>`;
        return;
    }

    tbody.innerHTML = prochaines.map(s => {
        const badgeCss = STATUT_BADGE[s.statut] || 'bg-slate-100 text-slate-600';
        const dateStr  = s.date_session
            ? new Date(s.date_session).toLocaleString('fr-FR', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
              })
            : '—';
        return `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-6 py-4 text-sm font-medium text-slate-900">${_escSess(s.titre)}</td>
            <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap">${dateStr}</td>
            <td class="px-6 py-4 text-sm text-slate-600">${s.duree_minutes} min</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeCss}">
                    ${_escSess(s.statut)}
                </span>
            </td>
        </tr>`;
    }).join('');
}

// ── Formulaire ───────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const form = _formSession();
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            titre:          document.getElementById('session-titre').value.trim(),
            description:    document.getElementById('session-description').value.trim(),
            date_session:   document.getElementById('session-date').value,
            duree_minutes:  parseInt(document.getElementById('session-duree').value, 10) || 0,
            lien_connexion: document.getElementById('session-lien').value.trim(),
            places_max:     parseInt(document.getElementById('session-places').value, 10) || 0,
            statut:         document.getElementById('session-statut').value,
            id_theme:       Number(_selectTheme()?.value) || 0,
        };
        try {
            if (_sessionEnEdition !== null) {
                await apiPut('sessions', _sessionEnEdition, data);
                afficherToast('Session modifiée avec succès.', 'succes');
            } else {
                await apiPost('sessions', data);
                afficherToast('Session créée avec succès.', 'succes');
            }
            _reinitFormSession();
            await chargerSessions();
        } catch (err) {
            afficherToast(err.message, 'erreur');
        }
    });

    _btnSessionAnnuler()?.addEventListener('click', _reinitFormSession);
});

// ── Édition ──────────────────────────────────────────────────────────────────

async function _editerSession(id) {
    try {
        const s = await apiGet('sessions', id);
        _sessionEnEdition = id;

        document.getElementById('session-id').value          = id;
        document.getElementById('session-titre').value       = s.titre;
        document.getElementById('session-description').value = s.description;
        document.getElementById('session-date').value        = s.date_session ? s.date_session.substring(0, 16) : '';
        document.getElementById('session-duree').value       = s.duree_minutes;
        document.getElementById('session-lien').value        = s.lien_connexion;
        document.getElementById('session-places').value      = s.places_max;
        document.getElementById('session-statut').value      = s.statut;
        if (_selectTheme()) _selectTheme().value             = s.id_theme;

        _formSessionTitre() && (_formSessionTitre().textContent = 'Modifier la session');
        _btnSessionSubmit() && (_btnSessionSubmit().innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            Enregistrer`);
        _btnSessionAnnuler()?.classList.remove('hidden');

        changerOnglet('sessions');
        document.getElementById('form-session')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        afficherToast('Impossible de charger la session : ' + err.message, 'erreur');
    }
}

// ── Suppression ──────────────────────────────────────────────────────────────

async function _supprimerSession(id, titre) {
    if (!confirm(`Supprimer la session "${titre}" ?`)) return;
    try {
        await apiDelete('sessions', id);
        afficherToast('Session supprimée.', 'succes');
        await chargerSessions();
    } catch (err) {
        afficherToast(err.message, 'erreur');
    }
}

// ── Réinitialisation formulaire ──────────────────────────────────────────────

function _reinitFormSession() {
    _sessionEnEdition = null;
    _formSession()?.reset();
    document.getElementById('session-id').value = '';

    _formSessionTitre() && (_formSessionTitre().textContent = 'Nouvelle session');
    _btnSessionSubmit() && (_btnSessionSubmit().innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Créer la session`);
    _btnSessionAnnuler()?.classList.add('hidden');
}

// ── Utilitaire XSS ───────────────────────────────────────────────────────────

function _escSess(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(str ?? '')));
    return d.innerHTML;
}
