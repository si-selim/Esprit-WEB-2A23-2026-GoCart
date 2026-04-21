document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const isUpdate = !!document.querySelector('.btn-save'); // update page has btn-save

    const fields = {
        nom: document.getElementById('nom_marathon'),
        region: document.getElementById('region_marathon'),
        date: document.getElementById('date_marathon'),
        places: document.getElementById('nb_places_dispo'),
        prix: document.getElementById('prix_marathon'),
        image: document.getElementById('image_marathon')
    };

    function setFeedback(id, message, type) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = message;
        el.className = 'feedback ' + (type || '');
    }

    const alphaNumRegex = /^(?=.*[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF])[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF0-9\s\-'\.]+$/;

    function validateNom() {
        const v = fields.nom.value.trim();
        if (v.length === 0) { setFeedback('nomFeedback', '❌ Le nom est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomFeedback', '❌ Le nom doit contenir au moins 3 caractères.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('nomFeedback', '❌ Le nom doit contenir au moins une lettre.', 'error'); return false; }
        setFeedback('nomFeedback', '✅ Nom valide.', 'success'); return true;
    }

    function validateRegion() {
        const v = fields.region.value.trim();
        if (v.length === 0) { setFeedback('regionFeedback', '❌ La région est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('regionFeedback', '❌ La région doit contenir au moins 3 caractères.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('regionFeedback', '❌ La région doit contenir au moins une lettre.', 'error'); return false; }
        setFeedback('regionFeedback', '✅ Région valide.', 'success'); return true;
    }

    function validateDate() {
        const v = fields.date.value;
        if (!v) { setFeedback('dateFeedback', '\u274C La date est obligatoire.', 'error'); return false; }
        if (!isUpdate) {
            const selected = new Date(v + 'T00:00:00');
            const today = new Date(); today.setHours(0, 0, 0, 0);
            if (selected <= today) { setFeedback('dateFeedback', '\u274C La date doit \u00eatre dans le futur.', 'error'); return false; }
        }
        setFeedback('dateFeedback', '\u2705 Date valide.', 'success'); return true;
    }

    function validatePlaces() {
        const raw = fields.places.value.trim();
        if (raw === '') { setFeedback('placesFeedback', '❌ Le nombre de places est obligatoire.', 'error'); return false; }
        if (!/^\d+$/.test(raw)) { setFeedback('placesFeedback', '❌ Veuillez saisir uniquement des chiffres entiers.', 'error'); return false; }
        if (parseInt(raw, 10) < 1) { setFeedback('placesFeedback', '❌ Le nombre de places doit être ≥ 1.', 'error'); return false; }
        setFeedback('placesFeedback', '✅ Nombre de places valide.', 'success'); return true;
    }

    function validatePrix() {
        const raw = fields.prix.value.trim();
        if (raw === '') { setFeedback('prixFeedback', '❌ Le prix est obligatoire.', 'error'); return false; }
        if (!/^\d+(\.\d{1,2})?$/.test(raw)) { setFeedback('prixFeedback', '❌ Veuillez saisir un nombre valide (ex: 30 ou 30.50).', 'error'); return false; }
        if (parseFloat(raw) < 0) { setFeedback('prixFeedback', '❌ Le prix doit être positif ou zéro.', 'error'); return false; }
        setFeedback('prixFeedback', '✅ Prix valide.', 'success'); return true;
    }

    function validateImage() {
        if (!fields.image || !fields.image.files || fields.image.files.length === 0) {
            if (isUpdate) { return true; } // image optionnelle en modification
            setFeedback('imageFeedback', '❌ Veuillez sélectionner une photo pour le marathon.', 'error'); return false;
        }
        setFeedback('imageFeedback', '✅ Image sélectionnée : ' + fields.image.files[0].name, 'success'); return true;
    }

    // Bloquer les lettres dans les champs numériques
    if (fields.places) fields.places.addEventListener('keypress', function (e) {
        if (!/[\d]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Tab') e.preventDefault();
    });
    if (fields.prix) fields.prix.addEventListener('keypress', function (e) {
        if (!/[\d\.]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Tab') e.preventDefault();
        if (e.key === '.' && this.value.includes('.')) e.preventDefault();
    });

    if (fields.nom) { fields.nom.addEventListener('input', validateNom); fields.nom.addEventListener('blur', validateNom); }
    if (fields.region) { fields.region.addEventListener('input', validateRegion); fields.region.addEventListener('blur', validateRegion); }
    if (fields.date) fields.date.addEventListener('change', validateDate);
    if (fields.places) { fields.places.addEventListener('input', validatePlaces); fields.places.addEventListener('blur', validatePlaces); }
    if (fields.prix) { fields.prix.addEventListener('input', validatePrix); fields.prix.addEventListener('blur', validatePrix); }
    if (fields.image) fields.image.addEventListener('change', validateImage);

    form.addEventListener('submit', function (e) {
        const valid = [validateNom(), validateRegion(), validateDate(), validatePlaces(), validatePrix(), validateImage()];
        if (valid.includes(false)) e.preventDefault();
    });
});
