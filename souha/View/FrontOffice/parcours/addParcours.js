document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const isUpdate = !!document.querySelector('.btn-save');

    const fields = {
        marathon: document.getElementById('id_marathon'),
        nom: document.getElementById('nom_parcours'),
        depart: document.getElementById('point_depart'),
        arrivee: document.getElementById('point_arrivee'),
        distance: document.getElementById('distance'),
        difficulte: document.getElementById('difficulte'),
        heure: document.getElementById('heure_depart')
    };

    function setFeedback(id, msg, type) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = msg;
        el.className = 'feedback ' + (type || '');
    }

    const alphaNumRegex = /^(?=.*[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF])[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF0-9\s\-'\.]+$/;

    function isPlaceInRegion(value) {
        if (!value || value.trim().length < 2) return false;
        if (typeof CITY_PLACES === 'undefined' || typeof currentCity === 'undefined') return true;

        const cityParts = currentCity.split(/[-,]/).map(s => s.trim()).filter(Boolean);
        let allPlaces = [];
        cityParts.forEach(part => {
            const key = Object.keys(CITY_PLACES).find(k => k.toLowerCase() === part.toLowerCase());
            if (key) allPlaces = allPlaces.concat(CITY_PLACES[key]);
        });

        if (allPlaces.length === 0) return true;

        const v = value.trim().toLowerCase();
        return allPlaces.some(p => {
            const pn = p.nom.toLowerCase();
            return pn === v || (v.length >= 3 && pn === v);
        });
    }

    function validateMarathon() {
        if (!fields.marathon) return true;
        if (!fields.marathon.value) { setFeedback('marathonFeedback', '❌ Veuillez choisir un marathon.', 'error'); return false; }
        setFeedback('marathonFeedback', '✅ Marathon sélectionné.', 'success'); return true;
    }

    function validateNom() {
        const v = fields.nom.value.trim();
        if (v.length === 0) { setFeedback('nomFeedback', '❌ Le nom du parcours est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomFeedback', '❌ Le nom doit contenir au moins 3 caractères.', 'error'); return false; }
        setFeedback('nomFeedback', '✅ Nom valide.', 'success'); return true;
    }

    function validateDepart() {
        const v = fields.depart.value.trim();
        if (v.length === 0) { setFeedback('departFeedback', '❌ Le point de départ est obligatoire.', 'error'); return false; }
        if (v.length < 2) { setFeedback('departFeedback', '❌ Sélectionnez un lieu dans la liste.', 'error'); return false; }
        // Si marqueur placé sur la carte (y compris via generateTrajet) → toujours valide
        if (window._departMarkerPlaced) {
            setFeedback('departFeedback', '✅ Point de départ valide.', 'success'); return true;
        }
        if (!isPlaceInRegion(v)) {
            setFeedback('departFeedback', '❌ Sélectionnez un lieu dans la liste de suggestions.', 'error');
            return false;
        }
        setFeedback('departFeedback', '✅ Point de départ valide.', 'success');
        return true;
    }

    function validateArrivee() {
        const v = fields.arrivee.value.trim();
        if (v.length === 0) { setFeedback('arriveeFeedback', "❌ Le point d'arrivée est obligatoire.", 'error'); return false; }
        if (v.length < 2) { setFeedback('arriveeFeedback', '❌ Sélectionnez un lieu dans la liste.', 'error'); return false; }
        // Si marqueur placé sur la carte (y compris via generateTrajet) → toujours valide
        if (window._arriveeMarkerPlaced) {
            setFeedback('arriveeFeedback', "✅ Point d'arrivée valide.", 'success'); return true;
        }
        if (!isPlaceInRegion(v)) {
            setFeedback('arriveeFeedback', '❌ Sélectionnez un lieu dans la liste de suggestions.', 'error');
            return false;
        }
        setFeedback('arriveeFeedback', "✅ Point d'arrivée valide.", 'success');
        return true;
    }

    function validateDistance() {
        const raw = fields.distance.value.trim();
        if (raw === '') { setFeedback('distanceFeedback', '❌ La distance est obligatoire.', 'error'); return false; }
        if (!/^\d+(\.\d+)?$/.test(raw)) { setFeedback('distanceFeedback', '❌ Veuillez saisir uniquement des chiffres (ex: 10 ou 10.5).', 'error'); return false; }
        if (parseFloat(raw) <= 0) { setFeedback('distanceFeedback', '❌ La distance doit être supérieure à 0.', 'error'); return false; }
        setFeedback('distanceFeedback', '✅ Distance valide.', 'success'); return true;
    }

    function validateDifficulte() {
        const diffEl = fields.difficulte;
        // Si désactivé (set par generateTrajet), récupérer la valeur du hidden
        const hid = document.getElementById('difficulte_hidden');
        const val = (diffEl && diffEl.disabled && hid) ? hid.value : (diffEl ? diffEl.value : '');
        if (!val) { setFeedback('difficulteFeedback', '❌ Veuillez choisir une difficulté.', 'error'); return false; }
        setFeedback('difficulteFeedback', '✅ Difficulté sélectionnée.', 'success'); return true;
    }

    function validateHeure() {
        if (!fields.heure) return true;
        const v = fields.heure.value.trim();
        if (v === '') { setFeedback('heureFeedback', "❌ L'heure de départ est obligatoire.", 'error'); return false; }
        setFeedback('heureFeedback', '✅ Heure valide.', 'success'); return true;
    }

    if (fields.distance) fields.distance.addEventListener('keypress', function (e) {
        if (!/[\d\.]/.test(e.key)) e.preventDefault();
        if (e.key === '.' && this.value.includes('.')) e.preventDefault();
    });

    if (fields.marathon) fields.marathon.addEventListener('change', validateMarathon);
    if (fields.nom) { fields.nom.addEventListener('input', validateNom); fields.nom.addEventListener('blur', validateNom); }
    if (fields.heure) { fields.heure.addEventListener('change', validateHeure); fields.heure.addEventListener('blur', validateHeure); }
    if (fields.depart) fields.depart.addEventListener('blur', validateDepart);
    if (fields.arrivee) fields.arrivee.addEventListener('blur', validateArrivee);
    if (fields.distance) { fields.distance.addEventListener('input', validateDistance); fields.distance.addEventListener('blur', validateDistance); }
    if (fields.difficulte) fields.difficulte.addEventListener('change', validateDifficulte);

    form.addEventListener('submit', function (e) {
        const validMarathon = isUpdate ? true : validateMarathon();
        const valid = [
            validMarathon,
            validateNom(),
            validateDepart(),
            validateArrivee(),
            validateDistance(),
            validateDifficulte(),
            validateHeure()
        ];
        if (valid.includes(false)) e.preventDefault();
    });
});
