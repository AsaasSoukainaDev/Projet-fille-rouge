/**
 * app.js — Orchestration de la SPA
 *
 * Responsabilités :
 *  - Système d'onglets (navigation entre Dashboard / Thèmes / Sessions)
 *  - Mise à jour du header selon l'onglet actif
 *  - Horloge en temps réel
 *  - Système de toasts (notifications)
 *  - Initialisation globale au chargement de la page
 */

// ── Configuration des onglets ────────────────────────────────────────────────

/** Métadonnées de chaque onglet */
const ONGLETS = {
    dashboard: {
        titre:     'Tableau de bord',
        sousTitre: 'Vue d\'ensemble de la plateforme',
    },
    themes: {
        titre:     'Thèmes',
        sousTitre: 'Gérez les catégories de vos webinaires',
    },
    sessions: {
        titre:     'Sessions',
        sousTitre: 'Planifiez et administrez vos sessions de webinaires',
    },
};

/** Onglet actuellement affiché */
let _ongletActif = 'dashboard';

// ── Navigation ───────────────────────────────────────────────────────────────

/**
 * Bascule vers l'onglet demandé.
 * Met à jour : contenu visible, lien actif dans la sidebar, titre du header.
 *
 * @param {string} id - Identifiant de l'onglet ('dashboard' | 'themes' | 'sessions')
 */
function changerOnglet(id) {
    if (!ONGLETS[id]) return;
    _ongletActif = id;

    // Masquer tous les contenus
    document.querySelectorAll('.tab-content').forEach(s => s.classList.remove('active'));

    // Afficher le contenu cible
    const cible = document.getElementById(`tab-${id}`);
    if (cible) cible.classList.add('active');

    // Mettre à jour les liens sidebar
    document.querySelectorAll('.nav-link').forEach(btn => {
        const estActif = btn.dataset.tab === id;
        if (estActif) {
            btn.classList.add('bg-indigo-600', 'text-white');
            btn.classList.remove('text-slate-300', 'hover:bg-slate-800', 'hover:text-white');
        } else {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('text-slate-300', 'hover:bg-slate-800', 'hover:text-white');
        }
    });

    // Mettre à jour le header
    const info = ONGLETS[id];
    const headerTitre     = document.getElementById('header-titre');
    const headerSousTitre = document.getElementById('header-sous-titre');
    if (headerTitre)     headerTitre.textContent     = info.titre;
    if (headerSousTitre) headerSousTitre.textContent = info.sousTitre;

    // Synchroniser l'URL avec le hash (navigation navigateur)
    history.replaceState(null, '', `#${id}`);
}

// ── Système de toasts ────────────────────────────────────────────────────────

/**
 * Affiche un toast de notification en haut à droite.
 *
 * @param {string} message              - Texte à afficher
 * @param {'succes'|'erreur'|'info'} type - Type de toast
 * @param {number} duree                - Durée en ms avant disparition (défaut : 3500)
 */
function afficherToast(message, type = 'succes', duree = 3500) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Styles selon le type
    const styles = {
        succes: {
            barre:  'bg-green-500',
            icone: `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>`,
        },
        erreur: {
            barre:  'bg-red-500',
            icone: `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>`,
        },
        info: {
            barre:  'bg-indigo-500',
            icone: `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>`,
        },
    };
    const s = styles[type] || styles.info;

    // Créer l'élément toast
    const toast = document.createElement('div');
    toast.className = 'pointer-events-auto flex items-start gap-3 bg-white border border-slate-200 rounded-xl shadow-lg px-4 py-3 max-w-sm w-full relative overflow-hidden';
    toast.style.cssText = 'opacity:0; transform:translateX(20px); transition:opacity 0.25s ease, transform 0.25s ease;';
    toast.innerHTML = `
        <!-- Barre colorée à gauche -->
        <div class="absolute left-0 top-0 bottom-0 w-1 ${s.barre} rounded-l-xl"></div>
        <div class="pl-1 flex items-start gap-3 flex-1">
            ${s.icone}
            <p class="text-sm text-slate-700 flex-1 leading-snug">${message}</p>
            <button onclick="this.closest('.pointer-events-auto').remove()"
                class="text-slate-300 hover:text-slate-500 transition-colors shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>`;

    container.appendChild(toast);

    // Animation d'entrée
    requestAnimationFrame(() => {
        toast.style.opacity   = '1';
        toast.style.transform = 'translateX(0)';
    });

    // Disparition automatique
    setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 250);
    }, duree);
}

// ── Horloge ──────────────────────────────────────────────────────────────────

function _mettreAJourHeure() {
    const el = document.getElementById('heure-courante');
    if (!el) return;
    el.textContent = new Date().toLocaleString('fr-FR', {
        weekday: 'short', day: '2-digit', month: 'short',
        hour: '2-digit', minute: '2-digit',
    });
}

// ── Initialisation ───────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', async () => {

    // Détecter l'onglet initial via le hash URL (#themes, #sessions, #dashboard)
    const hash = window.location.hash.replace('#', '') || 'dashboard';
    const ongletInitial = ONGLETS[hash] ? hash : 'dashboard';
    changerOnglet(ongletInitial);

    // Démarrer l'horloge
    _mettreAJourHeure();
    setInterval(_mettreAJourHeure, 60000);

    // Chargement initial des données
    try {
        // 1. Charger les thèmes (nécessaire pour le select de sessions + stat dashboard)
        await chargerThemes();

        // Mettre à jour stat thèmes dans le dashboard
        const themes = await apiGet('themes');
        const statThemes = document.getElementById('stat-total-themes');
        if (statThemes) statThemes.textContent = themes.length;

        // 2. Charger les thèmes dans le select du formulaire session
        await chargerThemesPourSelect();

        // 3. Charger les sessions (met à jour tableau + stats dashboard)
        await chargerSessions();

    } catch (err) {
        afficherToast('Erreur de connexion au serveur : ' + err.message, 'erreur');
    }

    // Gérer la navigation via le bouton retour du navigateur
    window.addEventListener('popstate', () => {
        const h = window.location.hash.replace('#', '') || 'dashboard';
        if (ONGLETS[h]) changerOnglet(h);
    });
});
