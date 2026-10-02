/**
 * themes.js — Logique de l'onglet Thèmes
 *
 * Responsabilités :
 *  - Charger et afficher les thèmes dans le tableau
 *  - Gérer le formulaire de création / modification
 *  - Gérer la suppression avec confirmation
 *
 * Dépendances : api.js (chargé avant), app.js (pour afficherToast)
 */

// ── Références DOM ───────────────────────────────────────────────────────────
const _formTheme       = () => document.getElementById('form-theme');
const _tbodyThemes     = () => document.getElementById('tbody-themes');
const _compteurThemes  = () => document.getElementById('compteur-themes');
const _badgeThemes     = () => document.getElementById('badge-themes');
const _btnThemeSubmit  = () => document.getElementById('btn-theme-submit');
const _btnThemeAnnuler = () => document.getElementById('btn-theme-annuler');
const _formThemeTitre  = () => document.getElementById('form-theme-titre');

/** ID du thème en cours d'édition, ou null si création */
let _themeEnEdition = null;

// ── Chargement ───────────────────────────────────────────────────────────────

/**
 * Charge tous les thèmes depuis l'API et rafraîchit l'affichage.
 * Exposé globalement pour le bouton "Rafraîchir".
 */
async function chargerThemes() {
    try {
        const themes = await apiGet('themes');
        _afficherThemes(themes);
        // Mettre à jour le badge sidebar
        const badge = _badgeThemes();
        if (badge) badge.textContent = themes.length;
    } catch (err) {
        afficherToast('Erreur chargement des thèmes : ' + err.message, 'erreur');
    }
}

// ── Affichage tableau ────────────────────────────────────────────────────────

/**
 * Génère les lignes du tableau HTML à partir des données thèmes.
 * @param {Array} themes
 */
function _afficherThemes(themes) {
    const tbody     = _tbodyThemes();
    const compteur  = _compteurThemes();
    if (!tbody) return;

    if (compteur) compteur.textContent = themes.length;

    if (themes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-8 h-8 text-slate-300">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                        </svg>
                        <p class="text-sm text-slate-400">Aucun thème. Créez le premier ci-dessus.</p>
                    </div>
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = themes.map(t => `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-6 py-4 text-sm text-slate-400 font-mono">#${t.id_theme}</td>
            <td class="px-6 py-4">
                <span class="text-sm font-medium text-slate-900">${_esc(t.libelle)}</span>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">${_esc(t.description)}</td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-1">
                    <!-- Modifier -->
                    <button onclick="_editerTheme(${t.id_theme})"
                        title="Modifier"
                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                        </svg>
                    </button>
                    <!-- Supprimer -->
                    <button onclick="_supprimerTheme(${t.id_theme}, '${_esc(t.libelle)}')"
                        title="Supprimer"
                        class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

// ── Formulaire ───────────────────────────────────────────────────────────────

/** Soumission du formulaire (création ou modification) */
document.addEventListener('DOMContentLoaded', () => {
    const form = _formTheme();
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            libelle:     document.getElementById('theme-libelle').value.trim(),
            description: document.getElementById('theme-description').value.trim(),
        };
        try {
            if (_themeEnEdition !== null) {
                await apiPut('themes', _themeEnEdition, data);
                afficherToast('Thème modifié avec succès.', 'succes');
            } else {
                await apiPost('themes', data);
                afficherToast('Thème créé avec succès.', 'succes');
            }
            _reinitFormTheme();
            await chargerThemes();
        } catch (err) {
            afficherToast(err.message, 'erreur');
        }
    });

    // Bouton annuler
    _btnThemeAnnuler()?.addEventListener('click', _reinitFormTheme);
});

// ── Édition ──────────────────────────────────────────────────────────────────

/**
 * Pré-remplit le formulaire avec les données du thème à modifier.
 * @param {number} id
 */
async function _editerTheme(id) {
    try {
        const t = await apiGet('themes', id);
        _themeEnEdition = id;
        document.getElementById('theme-id').value          = id;
        document.getElementById('theme-libelle').value     = t.libelle;
        document.getElementById('theme-description').value = t.description;

        _formThemeTitre() && (_formThemeTitre().textContent = 'Modifier le thème');
        _btnThemeSubmit() && (_btnThemeSubmit().innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            Enregistrer`);
        _btnThemeAnnuler()?.classList.remove('hidden');

        // Basculer sur l'onglet Thèmes si besoin, puis scroller
        changerOnglet('themes');
        document.getElementById('form-theme')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (err) {
        afficherToast('Impossible de charger le thème : ' + err.message, 'erreur');
    }
}

// ── Suppression ──────────────────────────────────────────────────────────────

/**
 * Demande confirmation puis supprime le thème.
 * @param {number} id
 * @param {string} libelle
 */
async function _supprimerTheme(id, libelle) {
    if (!confirm(`Supprimer le thème "${libelle}" ?`)) return;
    try {
        await apiDelete('themes', id);
        afficherToast('Thème supprimé.', 'succes');
        await chargerThemes();
    } catch (err) {
        afficherToast(err.message, 'erreur');
    }
}

// ── Réinitialisation formulaire ──────────────────────────────────────────────

function _reinitFormTheme() {
    _themeEnEdition = null;
    _formTheme()?.reset();
    document.getElementById('theme-id').value = '';

    _formThemeTitre() && (_formThemeTitre().textContent = 'Nouveau thème');
    _btnThemeSubmit() && (_btnThemeSubmit().innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Créer le thème`);
    _btnThemeAnnuler()?.classList.add('hidden');
}

// ── Utilitaire XSS ───────────────────────────────────────────────────────────

/** Échappe les caractères HTML pour prévenir les injections XSS. */
function _esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(str ?? '')));
    return d.innerHTML;
}
