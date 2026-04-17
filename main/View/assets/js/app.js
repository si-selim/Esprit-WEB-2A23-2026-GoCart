document.addEventListener('DOMContentLoaded', function () {
    injectBackgroundLayers();

    var staggerIndex = 0;
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var delay = staggerIndex * 0.08;
                entry.target.style.animationDelay = delay + 's';
                entry.target.classList.add('visible');
                staggerIndex++;
                observer.unobserve(entry.target);
                setTimeout(function () { staggerIndex = Math.max(0, staggerIndex - 1); }, 400);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.fade-in, .slide-up, .slide-left, .slide-right').forEach(function (el) {
        observer.observe(el);
    });

    assignStaggerClasses();

    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modal = btn.closest('.modal-overlay');
            if (modal) modal.classList.remove('active');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    document.querySelectorAll('.file-upload input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            var nameSpan = input.closest('.file-upload').querySelector('.file-upload-name');
            if (nameSpan) {
                nameSpan.textContent = input.files.length ? input.files[0].name : 'Aucun fichier';
            }
        });
    });

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });

    var params = new URLSearchParams(window.location.search);
    if (params.get('success')) {
        showFeedback(decodeURIComponent(params.get('success')), 'success');
    }
    if (params.get('error')) {
        showFeedback(decodeURIComponent(params.get('error')), 'error');
    }

    initTiltEffect();
    initParallaxOrbs();
});

function injectBackgroundLayers() {
    if (document.querySelector('.bg-orbs')) return;
    var orbs = document.createElement('div');
    orbs.className = 'bg-orbs';
    document.body.insertBefore(orbs, document.body.firstChild);

    var grid = document.createElement('div');
    grid.className = 'bg-grid';
    document.body.insertBefore(grid, document.body.firstChild);

    var noise = document.createElement('div');
    noise.className = 'bg-noise';
    document.body.insertBefore(noise, document.body.firstChild);
}

function assignStaggerClasses() {
    var groups = document.querySelectorAll('.info-grid, .stats-grid, .form-grid, .chart-grid, .report-list');
    groups.forEach(function (group) {
        var children = group.children;
        for (var i = 0; i < children.length && i < 8; i++) {
            children[i].classList.add('stagger-' + (i + 1));
        }
    });
}

function initTiltEffect() {
    var cards = document.querySelectorAll('.card-hover, .card-form, .info-card');
    cards.forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var centerX = rect.width / 2;
            var centerY = rect.height / 2;
            var rotateX = (y - centerY) / centerY * -4;
            var rotateY = (x - centerX) / centerX * 4;
            card.style.transform = 'perspective(800px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateY(-4px) scale(1.02)';
        });
        card.addEventListener('mouseleave', function () {
            card.style.transform = '';
        });
    });
}

function initParallaxOrbs() {
    var orbs = document.querySelector('.bg-orbs');
    if (!orbs) return;
    var ticking = false;
    document.addEventListener('mousemove', function (e) {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
            var x = (e.clientX / window.innerWidth - 0.5) * 20;
            var y = (e.clientY / window.innerHeight - 0.5) * 20;
            orbs.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
            ticking = false;
        });
    });
}

function validateForm(form) {
    var nom = form.querySelector('input[name="nom_complet"]');
    if (nom && nom.value.trim().length < 3) {
        showFeedback('Le nom complet doit contenir au moins 3 caracteres.', 'error');
        nom.focus();
        return false;
    }

    var user = form.querySelector('input[name="nom_user"]');
    if (user && user.value.trim().length < 3) {
        showFeedback('Le nom d\'utilisateur doit contenir au moins 3 caracteres.', 'error');
        user.focus();
        return false;
    }
    if (user && user.value && !/^[a-zA-Z0-9_]+$/.test(user.value)) {
        showFeedback('Le nom d\'utilisateur ne doit contenir que des lettres, chiffres et underscores.', 'error');
        user.focus();
        return false;
    }

    var pass = form.querySelector('input[name="mot_de_passe"]');
    if (pass && pass.value && pass.value.length < 6) {
        showFeedback('Le mot de passe doit contenir au moins 6 caracteres.', 'error');
        pass.focus();
        return false;
    }

    var email = form.querySelector('input[name="email"]');
    if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        showFeedback('Veuillez entrer une adresse email valide.', 'error');
        email.focus();
        return false;
    }

    var tel = form.querySelector('input[name="tel"]');
    if (tel && tel.value && !/^\d{8}$/.test(tel.value)) {
        showFeedback('Le numero de telephone doit contenir exactement 8 chiffres.', 'error');
        tel.focus();
        return false;
    }

    var age = form.querySelector('input[name="age"]');
    if (age && age.value && (parseInt(age.value) < 1 || parseInt(age.value) > 120)) {
        showFeedback('L\'age doit etre entre 1 et 120.', 'error');
        age.focus();
        return false;
    }

    var poids = form.querySelector('input[name="poids"]');
    if (poids && poids.value && (parseFloat(poids.value) < 1 || parseFloat(poids.value) > 500)) {
        showFeedback('Le poids doit etre entre 1 et 500 kg.', 'error');
        poids.focus();
        return false;
    }

    var taille = form.querySelector('input[name="taille"]');
    if (taille && taille.value && (parseInt(taille.value) < 1 || parseInt(taille.value) > 300)) {
        showFeedback('La taille doit etre entre 1 et 300 cm.', 'error');
        taille.focus();
        return false;
    }

    return true;
}

function openModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function closeModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

function showConfirm(message, onConfirm) {
    var modal = document.getElementById('confirm-modal');
    var msg = document.getElementById('confirm-message');
    var btnYes = document.getElementById('confirm-yes');
    if (msg) msg.textContent = message;
    if (modal) modal.classList.add('active');
    var newBtn = btnYes.cloneNode(true);
    btnYes.parentNode.replaceChild(newBtn, btnYes);
    newBtn.addEventListener('click', function () {
        modal.classList.remove('active');
        if (onConfirm) onConfirm();
    });
}

function showFeedback(message, type) {
    var modal = document.getElementById('feedback-modal');
    var msg = document.getElementById('feedback-message');
    var icon = document.getElementById('feedback-icon');
    if (!modal) return;
    if (msg) msg.textContent = message;
    if (icon) {
        icon.textContent = type === 'success' ? '\u2713' : '\u2717';
        icon.className = 'feedback-icon ' + type;
    }
    modal.classList.add('active');
    setTimeout(function () { modal.classList.remove('active'); }, 2500);
}
