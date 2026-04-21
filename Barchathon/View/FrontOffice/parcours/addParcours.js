document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const isUpdate = !!document.querySelector('.btn-save');

    const fields = {
        marathon: document.getElementById('id_marathon'),
        nom: document.getElementById('nom_parcours'),
        depart: document.getElementById('point_depart'),
        arrivee: document.getElementById('point_arrivee'),
        distance: document.getElementById('distance'),
        difficulte: document.getElementById('difficulte')
    };

    function setFeedback(id, msg, type) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = msg;
        el.className = 'feedback ' + (type || '');
    }

    // Au moins une lettre obligatoire (lettres + chiffres OK, mais pas QUE des chiffres)
    const alphaNumRegex = /^(?=.*[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF])[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF0-9\s\-'\.]+$/;

    function validateMarathon() {
        if (!fields.marathon) return true;
        if (!fields.marathon.value) { setFeedback('marathonFeedback', '\u274C Veuillez choisir un marathon.', 'error'); return false; }
        setFeedback('marathonFeedback', '\u2705 Marathon s\u00e9lectionn\u00e9.', 'success'); return true;
    }

    function validateNom() {
        const v = fields.nom.value.trim();
        if (v.length === 0) { setFeedback('nomFeedback', '\u274C Le nom du parcours est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomFeedback', '\u274C Le nom doit contenir au moins 3 caract\u00e8res.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('nomFeedback', '\u274C Le nom doit contenir au moins une lettre (pas uniquement des chiffres).', 'error'); return false; }
        setFeedback('nomFeedback', '\u2705 Nom valide.', 'success'); return true;
    }

    function validateDepart() {
        const v = fields.depart.value.trim();
        if (v.length === 0) { setFeedback('departFeedback', '\u274C Le point de d\u00e9part est obligatoire.', 'error'); return false; }
        if (v.length < 2) { setFeedback('departFeedback', '\u274C Le point de d\u00e9part doit contenir au moins 2 caract\u00e8res.', 'error'); return false; }
        setFeedback('departFeedback', '\u2705 Point de d\u00e9part valide.', 'success'); return true;
    }

    function validateArrivee() {
        const v = fields.arrivee.value.trim();
        if (v.length === 0) { setFeedback('arriveeFeedback', "\u274C Le point d'arriv\u00e9e est obligatoire.", 'error'); return false; }
        if (v.length < 2) { setFeedback('arriveeFeedback', "\u274C Le point d'arriv\u00e9e doit contenir au moins 2 caract\u00e8res.", 'error'); return false; }
        setFeedback('arriveeFeedback', "\u2705 Point d'arriv\u00e9e valide.", 'success'); return true;
    }

    function validateDistance() {
        const raw = fields.distance.value.trim();
        if (raw === '') { setFeedback('distanceFeedback', '\u274C La distance est obligatoire.', 'error'); return false; }
        if (!/^\d+(\.\d+)?$/.test(raw)) { setFeedback('distanceFeedback', '\u274C Veuillez saisir uniquement des chiffres (ex: 10 ou 10.5).', 'error'); return false; }
        if (parseFloat(raw) <= 0) { setFeedback('distanceFeedback', '\u274C La distance doit \u00eatre un nombre positif sup\u00e9rieur \u00e0 0.', 'error'); return false; }
        setFeedback('distanceFeedback', '\u2705 Distance valide.', 'success'); return true;
    }

    function validateDifficulte() {
        if (!fields.difficulte.value) { setFeedback('difficulteFeedback', '\u274C Veuillez choisir une difficult\u00e9.', 'error'); return false; }
        setFeedback('difficulteFeedback', '\u2705 Difficult\u00e9 s\u00e9lectionn\u00e9e.', 'success'); return true;
    }

    // Bloquer les lettres dans le champ distance
    fields.distance.addEventListener('keypress', function (e) {
        if (!/[\d\.]/.test(e.key)) e.preventDefault();
        if (e.key === '.' && this.value.includes('.')) e.preventDefault();
    });

    if (fields.marathon) fields.marathon.addEventListener('change', validateMarathon);
    fields.nom.addEventListener('input', validateNom);
    fields.nom.addEventListener('blur', validateNom);
    fields.depart.addEventListener('input', validateDepart);
    fields.depart.addEventListener('blur', validateDepart);
    fields.arrivee.addEventListener('input', validateArrivee);
    fields.arrivee.addEventListener('blur', validateArrivee);
    fields.distance.addEventListener('input', validateDistance);
    fields.distance.addEventListener('blur', validateDistance);
    fields.difficulte.addEventListener('change', validateDifficulte);

    form.addEventListener('submit', function (e) {
        const validMarathon = isUpdate ? true : validateMarathon();
        const valid = [validMarathon, validateNom(), validateDepart(), validateArrivee(), validateDistance(), validateDifficulte()];
        if (valid.includes(false)) e.preventDefault();
    });
    
});
