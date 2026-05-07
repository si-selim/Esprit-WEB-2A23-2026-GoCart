(function () {
    'use strict';

    if (window.__BarchaVoiceLoaded) return;
    window.__BarchaVoiceLoaded = true;

    var LS_KEY = 'voice-nav';
    var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    var recognition = null;
    var listening = false;
    var manualStop = false;
    var hud = null;
    var hudText = null;
    var hudPulse = null;
    var helpOverlay = null;

    function norm(s) {
        return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    }

    function getRole() {
        return (document.body && document.body.dataset.userRole) || 'visiteur';
    }

    function pathTo(target) {
        var script = document.currentScript || document.querySelector('script[src*="voice-nav.js"]');
        var base = '';
        if (script) {
            var src = script.getAttribute('src') || '';
            base = src.replace(/\/assets\/js\/voice-nav\.js.*$/, '');
        }
        return base + '/' + target.replace(/^\//, '');
    }

    function go(relFromAssetsParent) {
        var script = document.querySelector('script[src*="voice-nav.js"]');
        var src = script ? script.getAttribute('src') : '';
        var base = src.replace(/assets\/js\/voice-nav\.js.*$/, '');
        window.location.href = base + relFromAssetsParent;
    }

    function goFrontOffice(page) { go('FrontOffice/' + page); }
    function goBackOffice(page) { go('BackOffice/' + page); }

    function speak(text) {
        try {
            if (!window.speechSynthesis) return;
            window.speechSynthesis.cancel();
            var u = new SpeechSynthesisUtterance(text);
            u.lang = 'fr-FR';
            var voices = window.speechSynthesis.getVoices();
            var fr = voices.find(function (v) { return v.lang && v.lang.toLowerCase().indexOf('fr') === 0; });
            if (fr) u.voice = fr;
            u.rate = 1.05;
            window.speechSynthesis.speak(u);
        } catch (e) {}
    }

    function setHud(state, text) {
        if (!hud) return;
        hud.setAttribute('data-state', state);
        hudText.textContent = text;
        if (state === 'listening') {
            hudPulse.style.display = 'block';
        } else {
            hudPulse.style.display = 'none';
        }
    }

    function flashHud(state, text, revertMs) {
        setHud(state, text);
        if (revertMs && listening) {
            setTimeout(function () {
                if (listening) setHud('listening', 'Écoute... dites une commande');
            }, revertMs);
        }
    }

    function clickByLabel(label) {
        var wanted = norm(label);
        if (!wanted) return { ok: false, reason: 'empty' };
        var nodes = document.querySelectorAll('button, a, [role=button], input[type=submit], input[type=button]');
        var matches = [];
        for (var i = 0; i < nodes.length; i++) {
            var el = nodes[i];
            if (el.offsetParent === null && el.tagName !== 'A') continue;
            var t = norm(el.textContent || el.value || el.getAttribute('aria-label') || '');
            if (t && t.indexOf(wanted) !== -1) matches.push({ el: el, text: t });
        }
        if (matches.length === 0) return { ok: false, reason: 'none' };
        matches.sort(function (a, b) { return a.text.length - b.text.length; });
        matches[0].el.click();
        return { ok: true };
    }

    function toggleTheme() {
        var html = document.documentElement;
        var cur = html.getAttribute('data-theme') || 'light';
        var next = cur === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        try { localStorage.setItem('theme', next); } catch (e) {}
    }

    var COMMANDS = [
        { patterns: ['accueil', 'page d accueil', 'home'], tts: 'Accueil', run: function () { goFrontOffice('accueil.php'); } },
        { patterns: ['catalogue', 'marathons'], tts: 'Ouverture du catalogue', run: function () { goFrontOffice('listMarathons.php'); } },
        { patterns: ['inscription', 's inscrire', 'creer un compte'], roles: ['visiteur'], tts: 'Inscription', run: function () { goFrontOffice('register.php'); } },
        { patterns: ['connexion', 'se connecter', 'login'], roles: ['visiteur'], tts: 'Connexion', run: function () { goFrontOffice('login.php'); } },
        { patterns: ['face id', 'reconnaissance faciale', 'visage'], tts: 'Face ID', run: function () {
            if (getRole() === 'visiteur') goFrontOffice('face_login.php');
            else goFrontOffice('profile.php?section=face');
        }},
        { patterns: ['mon profil', 'profil'], roles: ['participant', 'organisateur', 'admin'], tts: 'Mon profil', run: function () { goFrontOffice('profile.php'); } },
        { patterns: ['modifier profil', 'editer profil', 'edition profil'], roles: ['participant', 'organisateur', 'admin'], tts: 'Modification du profil', run: function () { goFrontOffice('profile.php?section=edit'); } },
        { patterns: ['mot de passe', 'parametres'], roles: ['participant', 'organisateur', 'admin'], tts: 'Paramètres', run: function () { goFrontOffice('profile.php?section=settings'); } },
        { patterns: ['accessibilite'], roles: ['participant', 'organisateur', 'admin'], tts: 'Accessibilité', run: function () { goFrontOffice('profile.php?section=accessibility'); } },
        { patterns: ['deconnexion', 'se deconnecter', 'logout'], roles: ['participant', 'organisateur', 'admin'], tts: 'Déconnexion', run: function () { goFrontOffice('logout.php'); } },
        { patterns: ['tableau de bord', 'dashboard'], roles: ['admin'], tts: 'Tableau de bord', run: function () { goBackOffice('dashboard.php'); } },
        { patterns: ['utilisateurs'], roles: ['admin'], tts: 'Utilisateurs', run: function () { goBackOffice('dashboard.php?tab=utilisateurs'); } },
        { patterns: ['marathons admin', 'gestion marathons'], roles: ['admin'], tts: 'Marathons', run: function () { goBackOffice('dashboard.php?tab=marathons'); } },
        { patterns: ['stands'], roles: ['admin'], tts: 'Stands', run: function () { goBackOffice('dashboard.php?tab=stands'); } },
        { patterns: ['commandes'], roles: ['admin'], tts: 'Commandes', run: function () { goBackOffice('dashboard.php?tab=commandes'); } },
        { patterns: ['sponsors'], roles: ['admin'], tts: 'Sponsors', run: function () { goBackOffice('dashboard.php?tab=sponsors'); } },
        { patterns: ['parcours', 'gestion parcours'], roles: ['admin'], tts: 'Parcours', run: function () { goBackOffice('dashboard.php?tab=parcours'); } },
        { patterns: ['accueil admin', 'home admin', 'vue ensemble', 'statistiques admin'], roles: ['admin'], tts: 'Accueil admin', run: function () { goBackOffice('dashboard.php?tab=home'); } },
        { patterns: ['exporter csv', 'telecharger csv', 'exporter utilisateurs'], roles: ['admin'], tts: 'Export CSV en cours', run: function () {
            var a = document.querySelector('a[href*="export_users"]');
            if (a) a.click();
            else { flashHud('error', '✗ Allez d\'abord dans l\'onglet Utilisateurs', 2500); }
        }},
        { patterns: ['exporter pdf', 'telecharger pdf', 'exporter rapport'], roles: ['admin'], tts: 'Export PDF en cours', run: function () {
            var a = document.querySelector('a.btn-pdf');
            if (a) a.click();
            else { flashHud('error', '✗ Allez dans Marathons ou Parcours d\'abord', 2500); }
        }},
        { patterns: ['rechercher', 'chercher', 'barre de recherche', 'focus recherche'], roles: ['admin'], tts: 'Recherche', run: function () {
            var inp = document.querySelector('input[type=search], input[name=searchU], input[name=searchM], input[name=searchP], input[name=searchC]');
            if (inp) { inp.focus(); inp.select(); }
            else { flashHud('error', '✗ Aucune barre de recherche sur cette page', 2000); }
        }},
        { patterns: ['fermer modal', 'annuler action', 'fermer confirmation', 'non annuler'], roles: ['admin'], tts: 'Annulé', run: function () {
            var m = document.getElementById('confirm-modal');
            if (m) m.classList.remove('active');
        }},
        { patterns: ['quitter admin', 'retour site', 'retour front office'], roles: ['admin'], tts: 'Retour au site', run: function () { go('FrontOffice/accueil.php'); } },

        /* ── CRUD: modal confirmation ── */
        { patterns: ['confirmer', 'confirme', 'oui supprimer', 'confirmer suppression', 'confirme suppression', 'valider suppression', 'valide suppression'], roles: ['admin'], tts: 'Confirmé', run: function () {
            var btn = document.getElementById('confirm-yes');
            if (!btn) btn = document.querySelector('.modal-overlay.active .btn-danger, .modal-overlay .btn-danger');
            if (btn) btn.click();
            else flashHud('error', '✗ Aucune confirmation en attente', 1800);
        }},

        /* ── CRUD: sélection / cases à cocher ── */
        { patterns: ['selectionner tout', 'tout selectionner', 'cocher tout'], roles: ['admin'], tts: 'Tout sélectionné', run: function () {
            var master = document.querySelector('#selectAllCommandes, #selectAllLignes');
            if (master) { master.checked = true; master.dispatchEvent(new Event('change')); }
            else { document.querySelectorAll('tbody input[type=checkbox]').forEach(function (c) { c.checked = true; }); }
        }},
        { patterns: ['deselectionner tout', 'tout decocher', 'decocher tout'], roles: ['admin'], tts: 'Tout désélectionné', run: function () {
            document.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = false; });
            ['deleteCommandesArea','deleteLignesArea'].forEach(function(id){ var el=document.getElementById(id); if(el) el.style.display='none'; });
        }},
        { patterns: ['supprimer selection', 'supprimer selectionnes', 'supprimer coches'], roles: ['admin'], tts: 'Suppression de la sélection', run: function () {
            var btn = document.querySelector('#deleteCommandesArea .btn-danger, #deleteLignesArea .btn-danger');
            if (btn && btn.offsetParent !== null) btn.click();
            else flashHud('error', '✗ Aucun élément sélectionné', 1800);
        }},

        /* ── CRUD: filtres & recherche ── */
        { patterns: ['appliquer filtre', 'appliquer', 'lancer recherche', 'filtrer maintenant'], roles: ['admin'], tts: 'Filtre appliqué', run: function () {
            var btn = document.querySelector('button[type=submit].btn-primary');
            if (btn) btn.click();
            else flashHud('error', '✗ Aucun filtre sur cette page', 1800);
        }},
        { patterns: ['reinitialiser filtre', 'effacer filtre', 'reset filtre'], roles: ['admin'], tts: 'Filtre réinitialisé', run: function () {
            var a = document.querySelector('a.btn-secondary[href*="tab="]');
            if (a) window.location.href = a.href;
            else flashHud('error', '✗ Aucun filtre à réinitialiser', 1800);
        }},

        /* ── CRUD: utilisateurs ── */
        { patterns: ['modifier utilisateur', 'modifie utilisateur', 'editer utilisateur', 'edite utilisateur', 'modifier premier utilisateur', 'modifier premier'], roles: ['admin'], tts: 'Modification utilisateur', run: function () {
            var a = document.querySelector('a[href*="edit_user"]');
            if (a) window.location.href = a.href;
            else flashHud('error', '✗ Allez dans l\'onglet Utilisateurs', 2200);
        }},
        { patterns: ['bloquer utilisateur', 'bloque utilisateur', 'bannir utilisateur', 'bloquer premier', 'bloque premier', 'bloquer', 'bloque', 'bannir'], roles: ['admin'], tts: 'Blocage', run: function () {
            var btns = document.querySelectorAll('button.btn-sm');
            for (var i = 0; i < btns.length; i++) {
                var t = norm(btns[i].textContent);
                if (t.indexOf('bloquer') !== -1 || t.indexOf('bloque') !== -1) { btns[i].click(); return; }
            }
            flashHud('error', '✗ Aucun utilisateur à bloquer visible', 2200);
        }},
        { patterns: ['debloquer utilisateur', 'debloque utilisateur', 'reactiver utilisateur', 'debloquer premier', 'debloque premier', 'debloquer', 'debloque'], roles: ['admin'], tts: 'Déblocage', run: function () {
            var btns = document.querySelectorAll('button.btn-sm');
            for (var i = 0; i < btns.length; i++) {
                var t = norm(btns[i].textContent);
                if (t.indexOf('debloquer') !== -1 || t.indexOf('debloque') !== -1) { btns[i].click(); return; }
            }
            flashHud('error', '✗ Aucun utilisateur bloqué visible', 2200);
        }},

        /* ── CRUD: voir détails ── */
        { patterns: ['voir details', 'ouvrir details', 'details premier'], roles: ['admin'], tts: 'Détails', run: function () {
            var a = document.querySelector('a[href*="edit_user"], a[href*="orderDetailsAdmin"], a[href*="voirdetails"]');
            if (a) window.location.href = a.href;
            else flashHud('error', '✗ Aucun détail disponible sur cette page', 2200);
        }},

        /* ── CRUD: supprimer premier résultat ── */
        { patterns: ['supprimer premier', 'supprime premier', 'supprimer premier resultat', 'supprime premier resultat'], roles: ['admin'], tts: 'Confirmation requise', run: function () {
            var btn = document.querySelector('button.btn-danger.btn-sm, a.btn-danger.btn-sm');
            if (btn) btn.click();
            else flashHud('error', '✗ Aucun élément à supprimer sur cette page', 2200);
        }},
        { patterns: ['retour', 'page precedente'], tts: 'Retour', run: function () { history.back(); } },
        { patterns: ['suivant', 'page suivante'], tts: 'Page suivante', run: function () { history.forward(); } },
        { patterns: ['actualiser', 'recharger'], tts: 'Actualisation', run: function () { location.reload(); } },
        { patterns: ['mode sombre', 'theme sombre', 'mode clair'], tts: 'Changement de thème', run: toggleTheme },
        { patterns: ['defiler vers le bas', 'descendre'], tts: 'Défilement', run: function () { window.scrollBy({ top: 500, behavior: 'smooth' }); } },
        { patterns: ['defiler vers le haut', 'monter', 'haut de page'], tts: 'Haut de page', run: function () { window.scrollTo({ top: 0, behavior: 'smooth' }); } },
        { patterns: ['aide', 'commandes disponibles'], tts: 'Aide', run: showHelp },
        { patterns: ['arreter', 'stop', 'eteindre'], tts: 'Mode voix désactivé', run: deactivate }
    ];

    function match(transcript) {
        var t = norm(transcript);
        var clique = t.match(/^clique(?:r)?(?:\s+sur)?\s+(.+)$/);
        if (clique) {
            return { special: 'click', arg: clique[1] };
        }
        for (var i = 0; i < COMMANDS.length; i++) {
            var cmd = COMMANDS[i];
            for (var j = 0; j < cmd.patterns.length; j++) {
                var p = norm(cmd.patterns[j]);
                if (t === p || t.indexOf(p) !== -1) {
                    return { cmd: cmd };
                }
            }
        }
        return null;
    }

    function handleTranscript(transcript) {
        var res = match(transcript);
        if (!res) {
            flashHud('error', '✗ Commande non reconnue : ' + transcript, 1800);
            return;
        }
        if (res.special === 'click') {
            var click = clickByLabel(res.arg);
            if (click.ok) {
                speak('Clic');
                flashHud('success', '✓ Clic sur ' + res.arg, 1500);
            } else {
                flashHud('error', '✗ Bouton introuvable : ' + res.arg, 1800);
            }
            return;
        }
        var cmd = res.cmd;
        if (cmd.roles && cmd.roles.indexOf(getRole()) === -1) {
            flashHud('warn', '⚠ Commande non autorisée pour votre rôle', 2200);
            return;
        }
        speak(cmd.tts || 'OK');
        flashHud('success', '✓ ' + (cmd.tts || 'OK'), 900);
        setTimeout(cmd.run, 350);
    }

    function buildHud() {
        if (hud) return;
        hud = document.createElement('div');
        hud.id = 'voice-nav-hud';
        hud.setAttribute('data-state', 'listening');
        hud.innerHTML =
            '<div class="vn-pulse"></div>' +
            '<svg class="vn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>' +
            '<span class="vn-text">Écoute...</span>' +
            '<button class="vn-help" type="button" aria-label="Aide">?</button>' +
            '<button class="vn-close" type="button" aria-label="Désactiver">✕</button>';
        document.body.appendChild(hud);
        hudText = hud.querySelector('.vn-text');
        hudPulse = hud.querySelector('.vn-pulse');
        hud.querySelector('.vn-close').addEventListener('click', function (e) { e.stopPropagation(); deactivate(); });
        hud.querySelector('.vn-help').addEventListener('click', function (e) { e.stopPropagation(); showHelp(); });
    }

    function removeHud() {
        if (hud && hud.parentNode) hud.parentNode.removeChild(hud);
        hud = null;
    }

    function showHelp() {
        if (helpOverlay) return;
        helpOverlay = document.createElement('div');
        helpOverlay.id = 'voice-nav-help';
        var groups = [
            { title: 'Navigation', items: [
                ['« accueil »', 'Page d\'accueil'],
                ['« catalogue »', 'Liste des marathons'],
                ['« connexion »', 'Se connecter'],
                ['« inscription »', 'Créer un compte'],
                ['« mon profil »', 'Votre profil'],
                ['« modifier profil »', 'Éditer le profil'],
                ['« accessibilité »', 'Modes d\'accessibilité'],
                ['« face id »', 'Reconnaissance faciale'],
                ['« déconnexion »', 'Se déconnecter']
            ]},
            { title: 'Admin — Navigation', items: [
                ['« tableau de bord »', 'Dashboard admin (accueil)'],
                ['« accueil admin »', 'Vue d\'ensemble & stats'],
                ['« utilisateurs »', 'Gestion utilisateurs'],
                ['« marathons admin »', 'Gestion marathons'],
                ['« parcours »', 'Gestion parcours'],
                ['« stands »', 'Gestion stands'],
                ['« commandes »', 'Gestion commandes'],
                ['« sponsors »', 'Gestion sponsors'],
                ['« quitter admin »', 'Retour au site public']
            ]},
            { title: 'Admin — Actions', items: [
                ['« rechercher »', 'Activer la barre de recherche'],
                ['« exporter csv »', 'Télécharger CSV utilisateurs'],
                ['« exporter pdf »', 'Télécharger PDF (marathons/parcours)'],
                ['« fermer modal »', 'Fermer la fenêtre de confirmation'],
                ['« annuler action »', 'Annuler une action en cours']
            ]},
            { title: 'Admin — CRUD', items: [
                ['« modifier utilisateur »', 'Ouvrir l\'édition du 1er utilisateur'],
                ['« bloquer utilisateur »', 'Bloquer le 1er utilisateur visible'],
                ['« débloquer utilisateur »', 'Débloquer le 1er utilisateur bloqué'],
                ['« voir détails »', 'Ouvrir les détails du 1er résultat'],
                ['« sélectionner tout »', 'Cocher toutes les cases'],
                ['« désélectionner tout »', 'Décocher toutes les cases'],
                ['« supprimer sélection »', 'Supprimer les éléments cochés'],
                ['« supprimer premier »', 'Supprimer le 1er résultat (confirmation requise)'],
                ['« confirmer »', 'Confirmer la suppression dans la modale'],
                ['« appliquer filtre »', 'Soumettre le formulaire de filtre'],
                ['« réinitialiser filtre »', 'Effacer le filtre actif']
            ]},
            { title: 'Actions', items: [
                ['« retour »', 'Page précédente'],
                ['« suivant »', 'Page suivante'],
                ['« actualiser »', 'Recharger la page'],
                ['« mode sombre »', 'Basculer le thème'],
                ['« défiler vers le bas / haut »', 'Faire défiler la page'],
                ['« clique [texte] »', 'Clique sur un bouton/lien portant ce texte'],
                ['« aide »', 'Afficher cette aide'],
                ['« arrêter »', 'Désactiver le mode voix']
            ]}
        ];
        var html = '<div class="vn-help-card"><div class="vn-help-head"><h2>Commandes vocales</h2><button type="button" class="vn-help-x" aria-label="Fermer">✕</button></div>';
        for (var g = 0; g < groups.length; g++) {
            html += '<h3>' + groups[g].title + '</h3><table class="vn-help-tbl">';
            for (var i = 0; i < groups[g].items.length; i++) {
                html += '<tr><td>' + groups[g].items[i][0] + '</td><td>' + groups[g].items[i][1] + '</td></tr>';
            }
            html += '</table>';
        }
        html += '<p class="vn-help-note">Activé par <kbd>Ctrl</kbd> + <kbd>G</kbd>. Fonctionne sur Chrome, Edge et Safari.</p></div>';
        helpOverlay.innerHTML = html;
        document.body.appendChild(helpOverlay);
        helpOverlay.addEventListener('click', function (e) { if (e.target === helpOverlay) closeHelp(); });
        helpOverlay.querySelector('.vn-help-x').addEventListener('click', closeHelp);
    }

    function closeHelp() {
        if (helpOverlay && helpOverlay.parentNode) helpOverlay.parentNode.removeChild(helpOverlay);
        helpOverlay = null;
    }

    function unsupportedNotice() {
        buildHud();
        setHud('error', 'Reconnaissance vocale non supportée — utilisez Chrome, Edge ou Safari');
        setTimeout(function () { removeHud(); }, 4000);
    }

    function activate() {
        console.log('[voice-nav] activate() called. SR=' + !!SR + ' listening=' + listening);
        if (!SR) { unsupportedNotice(); return; }
        if (listening) return;
        try {
            recognition = new SR();
            recognition.lang = 'fr-FR';
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.maxAlternatives = 3;
            recognition.onstart = function () { console.log('[voice-nav] recognition started'); };
            recognition.onaudiostart = function () { console.log('[voice-nav] audio started'); };
            recognition.onspeechstart = function () { console.log('[voice-nav] speech detected'); };
            recognition.onspeechend = function () { console.log('[voice-nav] speech ended'); };
            recognition.onnomatch = function () { console.log('[voice-nav] no match'); };
            recognition.onresult = function (ev) {
                for (var i = ev.resultIndex; i < ev.results.length; i++) {
                    var r = ev.results[i];
                    var txt = r[0].transcript;
                    if (r.isFinal) {
                        console.log('[voice-nav] final:', txt);
                        handleTranscript(txt);
                    } else {
                        if (listening && hud && hud.getAttribute('data-state') === 'listening') {
                            hudText.textContent = '… ' + txt;
                        }
                    }
                }
            };
            recognition.onerror = function (e) {
                var err = (e && e.error) || 'unknown';
                console.warn('[voice-nav] error:', err, e);
                if (err === 'not-allowed' || err === 'service-not-allowed') {
                    flashHud('error', '✗ Microphone refusé. Autorisez-le dans le navigateur.', 4000);
                    setTimeout(deactivate, 3200);
                } else if (err === 'no-speech') {
                    // silent; will auto-restart via onend
                } else if (err === 'audio-capture') {
                    flashHud('error', '✗ Micro introuvable', 3500);
                } else if (err === 'network') {
                    flashHud('error', '✗ Erreur réseau (la reconnaissance Chrome utilise Google)', 3500);
                } else if (err === 'aborted') {
                    // ignore
                } else {
                    flashHud('error', '✗ Erreur reconnaissance : ' + err, 3000);
                }
            };
            recognition.onend = function () {
                console.log('[voice-nav] recognition ended. listening=' + listening + ' manualStop=' + manualStop);
                if (listening && !manualStop) {
                    setTimeout(function () {
                        if (listening) {
                            try { recognition.start(); } catch (e) { console.warn('[voice-nav] restart failed:', e); }
                        }
                    }, 200);
                }
                manualStop = false;
            };
            listening = true;
            try { localStorage.setItem(LS_KEY, 'on'); } catch (e) {}
            buildHud();
            setHud('listening', 'Écoute... dites une commande');
            if (window.speechSynthesis) {
                window.speechSynthesis.cancel();
                var greet = new SpeechSynthesisUtterance('Mode voix activé');
                greet.lang = 'fr-FR';
                greet.rate = 1.05;
                var voices = window.speechSynthesis.getVoices();
                var frv = voices.find(function(v){ return v.lang && v.lang.toLowerCase().indexOf('fr') === 0; });
                if (frv) greet.voice = frv;
                greet.onend = function() { if (listening) { try { recognition.start(); } catch(e){} } };
                window.speechSynthesis.speak(greet);
            } else {
                try { recognition.start(); } catch(e) {}
            }
        } catch (err) {
            flashHud('error', 'Erreur : ' + (err.message || err), 3000);
            listening = false;
        }
    }

    function deactivate() {
        console.log('[voice-nav] deactivate() called. listening=' + listening, new Error('stack').stack);
        try { localStorage.setItem(LS_KEY, 'off'); } catch (e) {}
        if (recognition && listening) {
            manualStop = true;
            try { recognition.stop(); } catch (e) {}
        }
        listening = false;
        speak('Mode voix désactivé');
        removeHud();
        closeHelp();
    }

    var lastToggle = 0;
    function toggle() {
        var now = Date.now();
        if (now - lastToggle < 400) { console.log('[voice-nav] toggle debounced'); return; }
        lastToggle = now;
        console.log('[voice-nav] toggle() called. listening=' + listening);
        if (listening) deactivate();
        else activate();
    }

    function injectStyles() {
        if (document.getElementById('voice-nav-styles')) return;
        var style = document.createElement('style');
        style.id = 'voice-nav-styles';
        style.textContent = [
            '#voice-nav-hud{position:fixed;right:18px;bottom:18px;z-index:99999;display:flex;align-items:center;gap:10px;padding:10px 14px 10px 18px;border-radius:999px;font-family:"Segoe UI",sans-serif;font-size:.9rem;font-weight:700;color:#fff;background:linear-gradient(135deg,#0f766e,#14b8a6);box-shadow:0 10px 30px rgba(15,118,110,.35);max-width:min(420px,calc(100vw - 36px));animation:vnIn .3s ease}',
            '#voice-nav-hud[data-state=success]{background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 10px 30px rgba(16,185,129,.4)}',
            '#voice-nav-hud[data-state=error]{background:linear-gradient(135deg,#b91c1c,#dc2626);box-shadow:0 10px 30px rgba(220,38,38,.4)}',
            '#voice-nav-hud[data-state=warn]{background:linear-gradient(135deg,#b45309,#d97706);box-shadow:0 10px 30px rgba(217,119,6,.4)}',
            '#voice-nav-hud .vn-icon{flex-shrink:0}',
            '#voice-nav-hud .vn-text{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}',
            '#voice-nav-hud .vn-pulse{position:absolute;left:8px;top:50%;transform:translateY(-50%);width:14px;height:14px;border-radius:50%;background:rgba(255,255,255,.6);animation:vnPulse 1.4s ease-in-out infinite}',
            '#voice-nav-hud{position:fixed;padding-left:32px}',
            '#voice-nav-hud .vn-help,#voice-nav-hud .vn-close{background:rgba(255,255,255,.2);color:#fff;border:none;width:26px;height:26px;border-radius:50%;cursor:pointer;font-weight:700;font-size:.85rem;display:inline-flex;align-items:center;justify-content:center;transition:background .15s}',
            '#voice-nav-hud .vn-help:hover,#voice-nav-hud .vn-close:hover{background:rgba(255,255,255,.35)}',
            '@keyframes vnIn{from{opacity:0;transform:translateY(20px) scale(.9)}to{opacity:1;transform:translateY(0) scale(1)}}',
            '@keyframes vnPulse{0%,100%{transform:translateY(-50%) scale(.8);opacity:.6}50%{transform:translateY(-50%) scale(1.3);opacity:1}}',
            '#voice-nav-help{position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.72);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:20px;animation:vnIn .2s ease}',
            '#voice-nav-help .vn-help-card{background:#fff;border-radius:20px;padding:28px 32px;max-width:620px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 30px 80px rgba(0,0,0,.35);color:#102a43;font-family:"Segoe UI",sans-serif}',
            '#voice-nav-help .vn-help-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}',
            '#voice-nav-help h2{font-size:1.35rem;margin:0;color:#0f766e}',
            '#voice-nav-help h3{font-size:.95rem;font-weight:800;color:#334155;margin:18px 0 6px;text-transform:uppercase;letter-spacing:.5px}',
            '#voice-nav-help .vn-help-x{background:#f1f5f9;color:#334155;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-weight:700}',
            '#voice-nav-help .vn-help-x:hover{background:#e2e8f0}',
            '#voice-nav-help .vn-help-tbl{width:100%;border-collapse:collapse}',
            '#voice-nav-help .vn-help-tbl td{padding:7px 10px;border-bottom:1px solid #e2e8f0;font-size:.9rem;vertical-align:top}',
            '#voice-nav-help .vn-help-tbl td:first-child{font-weight:700;color:#0f766e;white-space:nowrap;width:42%}',
            '#voice-nav-help .vn-help-note{margin-top:18px;padding-top:14px;border-top:1px solid #e2e8f0;font-size:.85rem;color:#627d98}',
            '#voice-nav-help kbd{background:#f1f5f9;padding:2px 7px;border-radius:5px;border:1px solid #cbd5e1;font-family:"Segoe UI",sans-serif;font-weight:700}',
            'html[data-theme="dark"] #voice-nav-help .vn-help-card{background:#1e293b;color:#e2e8f0}',
            'html[data-theme="dark"] #voice-nav-help h2{color:#5eead4}',
            'html[data-theme="dark"] #voice-nav-help h3{color:#94a3b8}',
            'html[data-theme="dark"] #voice-nav-help .vn-help-x{background:rgba(255,255,255,.08);color:#e2e8f0}',
            'html[data-theme="dark"] #voice-nav-help .vn-help-tbl td{border-bottom-color:rgba(255,255,255,.08)}',
            'html[data-theme="dark"] #voice-nav-help .vn-help-tbl td:first-child{color:#5eead4}',
            'html[data-theme="dark"] #voice-nav-help kbd{background:#162032;border-color:rgba(255,255,255,.12);color:#e2e8f0}'
        ].join('\n');
        document.head.appendChild(style);
    }

    function onReady() {
        injectStyles();
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && (e.key === 'g' || e.key === 'G')) {
                e.preventDefault();
                toggle();
            }
        });
        try {
            if (localStorage.getItem(LS_KEY) === 'on') {
                setTimeout(activate, 300);
            }
        } catch (e) {}
        if (window.speechSynthesis && typeof speechSynthesis.getVoices === 'function') {
            speechSynthesis.getVoices();
            speechSynthesis.onvoiceschanged = function () { speechSynthesis.getVoices(); };
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }

    window.BarchaVoice = { activate: activate, deactivate: deactivate, toggle: toggle };
})();
