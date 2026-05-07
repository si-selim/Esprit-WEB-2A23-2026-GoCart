document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[data-validate]');
    if (!form) return;

    var fields = {
        nom_complet: document.getElementById('nom_complet'),
        nom_user: document.getElementById('nom_user'),
        mot_de_passe: document.getElementById('mot_de_passe'),
        email: document.getElementById('email'),
        age: document.getElementById('age'),
        poids: document.getElementById('poids'),
        taille: document.getElementById('taille'),
        tel: document.getElementById('tel'),
        pays: document.getElementById('pays'),
        ville: document.getElementById('ville'),
        profile_picture: form.querySelector('input[name="profile_picture"]')
    };

    function setFeedback(id, message, type) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = message;
        el.className = 'feedback ' + (type || '');
    }

    var alphaRegex = /^[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\s\-'\.]+$/;
    var usernameRegex = /^[a-zA-Z0-9_]+$/;
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var phoneRegex = /^\d{8}$/;

    function validateNomComplet() {
        if (!fields.nom_complet) return true;
        var v = fields.nom_complet.value.trim();
        if (v.length === 0) { setFeedback('nomCompletFeedback', '\u274C Le nom complet est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomCompletFeedback', '\u274C Le nom complet doit contenir au moins 3 caracteres.', 'error'); return false; }
        if (!alphaRegex.test(v)) { setFeedback('nomCompletFeedback', '\u274C Le nom complet doit contenir uniquement des lettres.', 'error'); return false; }
        setFeedback('nomCompletFeedback', '\u2705 Nom complet valide.', 'success'); return true;
    }

    function validateNomUser() {
        if (!fields.nom_user) return true;
        var v = fields.nom_user.value.trim();
        if (v.length === 0) { setFeedback('nomUserFeedback', '\u274C Le nom d\'utilisateur est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomUserFeedback', '\u274C Le nom d\'utilisateur doit contenir au moins 3 caracteres.', 'error'); return false; }
        if (!usernameRegex.test(v)) { setFeedback('nomUserFeedback', '\u274C Lettres, chiffres et underscores uniquement.', 'error'); return false; }
        setFeedback('nomUserFeedback', '\u2705 Nom d\'utilisateur valide.', 'success'); return true;
    }

    function validateMotDePasse() {
        if (!fields.mot_de_passe) return true;
        var v = fields.mot_de_passe.value;
        if (v.length === 0) { setFeedback('motDePasseFeedback', '\u274C Le mot de passe est obligatoire.', 'error'); return false; }
        if (v.length < 6) { setFeedback('motDePasseFeedback', '\u274C Le mot de passe doit contenir au moins 6 caracteres.', 'error'); return false; }
        setFeedback('motDePasseFeedback', '\u2705 Mot de passe valide.', 'success'); return true;
    }

    function validateEmail() {
        if (!fields.email) return true;
        var v = fields.email.value.trim();
        if (v.length === 0) { setFeedback('emailFeedback', '\u274C L\'email est obligatoire.', 'error'); return false; }
        if (!emailRegex.test(v)) { setFeedback('emailFeedback', '\u274C Veuillez entrer une adresse email valide.', 'error'); return false; }
        setFeedback('emailFeedback', '\u2705 Email valide.', 'success'); return true;
    }

    function validateAge() {
        if (!fields.age) return true;
        var v = fields.age.value.trim();
        if (v === '') { setFeedback('ageFeedback', '', ''); return true; }
        var n = parseInt(v, 10);
        if (isNaN(n) || n < 1 || n > 120) { setFeedback('ageFeedback', '\u274C L\'age doit etre entre 1 et 120.', 'error'); return false; }
        setFeedback('ageFeedback', '\u2705 Age valide.', 'success'); return true;
    }

    function validatePoids() {
        if (!fields.poids) return true;
        var v = fields.poids.value.trim();
        if (v === '') { setFeedback('poidsFeedback', '', ''); return true; }
        var n = parseFloat(v);
        if (isNaN(n) || n < 1 || n > 500) { setFeedback('poidsFeedback', '\u274C Le poids doit etre entre 1 et 500 kg.', 'error'); return false; }
        setFeedback('poidsFeedback', '\u2705 Poids valide.', 'success'); return true;
    }

    function validateTaille() {
        if (!fields.taille) return true;
        var v = fields.taille.value.trim();
        if (v === '') { setFeedback('tailleFeedback', '', ''); return true; }
        var n = parseInt(v, 10);
        if (isNaN(n) || n < 1 || n > 300) { setFeedback('tailleFeedback', '\u274C La taille doit etre entre 1 et 300 cm.', 'error'); return false; }
        setFeedback('tailleFeedback', '\u2705 Taille valide.', 'success'); return true;
    }

    function validateTel() {
        if (!fields.tel) return true;
        var v = fields.tel.value.trim();
        if (v === '') { setFeedback('telFeedback', '', ''); return true; }
        if (!phoneRegex.test(v)) { setFeedback('telFeedback', '\u274C Le telephone doit contenir exactement 8 chiffres.', 'error'); return false; }
        setFeedback('telFeedback', '\u2705 Telephone valide.', 'success'); return true;
    }

    function validateProfilePicture() {
        if (!fields.profile_picture) return true;
        if (!fields.profile_picture.files || fields.profile_picture.files.length === 0) return true;
        var file = fields.profile_picture.files[0];
        var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (allowed.indexOf(file.type) === -1) { setFeedback('profilePictureFeedback', '\u274C Type de fichier non autorise (JPG, PNG, GIF, WEBP).', 'error'); return false; }
        if (file.size > 2 * 1024 * 1024) { setFeedback('profilePictureFeedback', '\u274C La photo ne doit pas depasser 2 Mo.', 'error'); return false; }
        setFeedback('profilePictureFeedback', '\u2705 Photo selectionnee : ' + file.name, 'success'); return true;
    }

    if (fields.profile_picture) {
        fields.profile_picture.addEventListener('change', function () {
            validateProfilePicture();
            var preview = document.getElementById('profilePicturePreview');
            if (preview && this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
            var nameSpan = this.closest('.file-upload') ? this.closest('.file-upload').querySelector('.file-upload-name') : null;
            if (nameSpan) {
                nameSpan.textContent = this.files[0] ? this.files[0].name : 'Aucun fichier';
            }
        });
    }

    if (fields.age) {
        fields.age.addEventListener('keypress', function (e) {
            if (!/[\d]/.test(e.key)) e.preventDefault();
        });
    }
    if (fields.poids) {
        fields.poids.addEventListener('keypress', function (e) {
            if (!/[\d\.]/.test(e.key)) e.preventDefault();
            if (e.key === '.' && this.value.includes('.')) e.preventDefault();
        });
    }
    if (fields.taille) {
        fields.taille.addEventListener('keypress', function (e) {
            if (!/[\d]/.test(e.key)) e.preventDefault();
        });
    }
    if (fields.tel) {
        fields.tel.addEventListener('keypress', function (e) {
            if (!/[\d]/.test(e.key)) e.preventDefault();
        });
    }

    var bindings = [
        [fields.nom_complet, validateNomComplet],
        [fields.nom_user, validateNomUser],
        [fields.mot_de_passe, validateMotDePasse],
        [fields.email, validateEmail],
        [fields.age, validateAge],
        [fields.poids, validatePoids],
        [fields.taille, validateTaille],
        [fields.tel, validateTel]
    ];

    bindings.forEach(function (pair) {
        if (pair[0]) {
            pair[0].addEventListener('input', pair[1]);
            pair[0].addEventListener('blur', pair[1]);
        }
    });

    form.addEventListener('submit', function (e) {
        var valid = [
            validateNomComplet(),
            validateNomUser(),
            validateMotDePasse(),
            validateEmail(),
            validateAge(),
            validatePoids(),
            validateTaille(),
            validateTel(),
            validateProfilePicture()
        ];
        if (valid.indexOf(false) !== -1) e.preventDefault();
    });
});
