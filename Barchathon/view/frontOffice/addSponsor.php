<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un sponsor</title>
    <style>
        :root {
            --ink:#102a43;
            --teal:#0f766e;
            --sun:#ffb703;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --line:#d9e2ec;
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fefaf0 0%,var(--bg)100%); }
        .page { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:28px 0 56px; }
        .card { background:var(--card); border-radius:28px; padding:28px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        h1 { margin:0 0 12px; font-size:2.2rem; }
        p { color:var(--muted); line-height:1.7; margin:0 0 24px; }
        .grid { display:grid; gap:18px; grid-template-columns:repeat(2,minmax(0,1fr)); }
        .field { display:grid; gap:8px; }
        label { font-weight:700; }
        input, select { width:100%; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink); }
        .full-width { grid-column:1 / -1; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:24px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 18px; border-radius:14px; font-weight:700; border:0; cursor:pointer; text-decoration:none; }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        .btn-back { color:var(--teal); }
        @media (max-width:760px) { .grid { grid-template-columns:1fr; } }
        .error { color: #d92d20; font-size: 0.9rem; display: block; margin-top: 4px; }
    
    
        .fo-topbar {
        position:sticky; top:0; z-index:1000;
        backdrop-filter:blur(16px);
        background:rgba(255,255,255,0.95);
        border-bottom:1px solid rgba(16,42,67,0.08);
        box-shadow:0 4px 18px rgba(16,42,67,0.06);
    }
    .fo-topbar-shell {
        width:min(1200px,calc(100% - 32px));
        margin:0 auto; min-height:72px;
        display:flex; align-items:center;
        justify-content:space-between; gap:16px;
    }
    .fo-brand { display:inline-flex; align-items:center; gap:12px; text-decoration:none; color:#102a43; font-weight:900; font-size:1.1rem; flex-shrink:0; }
    .fo-brand img { height:50px; border-radius:10px; object-fit:cover; }
    .fo-nav { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
    .fo-link, .fo-cta, .fo-user {
        text-decoration:none; border-radius:999px; padding:9px 16px;
        font-weight:700; font-size:0.88rem;
        transition:transform .15s,background .15s,box-shadow .15s;
        white-space:nowrap;
    }
    .fo-link { color:#102a43; border:1px solid rgba(16,42,67,0.12); background:transparent; }
    .fo-link:hover { background:rgba(16,42,67,0.05); transform:translateY(-1px); }
    .fo-link.active { color:white; background:#102a43; border-color:#102a43; }
    .fo-cta { color:white; background:linear-gradient(135deg,#0f766e,#14b8a6); border:none; box-shadow:0 5px 16px rgba(15,118,110,.22); }
    .fo-cta:hover { transform:translateY(-1px); }
    .fo-user { background:linear-gradient(135deg,#fff7ed,#fff); border:1px solid rgba(255,183,3,.3); color:#102a43; display:flex; align-items:center; gap:7px; pointer-events:none; }
    .fo-role-badge { background:rgba(15,118,110,.12); color:#0f766e; border-radius:999px; padding:2px 8px; font-size:0.75rem; font-weight:700; }
    @media(max-width:768px){ .fo-topbar-shell{flex-wrap:wrap;padding:10px 0;min-height:auto;} .fo-nav{width:100%;} }


    
    
    
    </style>
</head>
<body>


    <div class="fo-topbar">
    <div class="fo-topbar-shell">
        <a class="fo-brand" href="accueil.php">
            <img src="../assets/images/logo_barchathon.jpg" alt="BarchaThon">
            BarchaThon
        </a>
        <nav class="fo-nav">
            <a class="fo-link active" href="accueil.php">Accueil</a>
            <a class="fo-link " href="listMarathons.php">Catalogue</a>
            <a class="fo-link" href="mesSponsors.php">Sponsors</a>
            <a class="fo-link" href="register.php">S'inscrire</a>
            <a class="fo-cta" href="login.php">Se connecter</a>
        </nav>
    </div>
    </div>



    <div class="page">
        <div class="card">
            <h1>Ajouter un sponsor</h1>
            <p>Formulaire de saisie simplifié pour créer un sponsor.</p>
            <form id="sponsorForm" action="addSponsor_process.php" method="post" enctype="multipart/form-data">
                <div class="grid">
                    <div class="field">
                        <label for="name">Nom</label>
                        <input id="name" name="name" type="text" placeholder="Nom du sponsor">
                        <span id="name-error" class="error"></span>
                    </div>
                    <div class="field">
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            <option>Entreprise</option>
                            <option>Association</option>
                            <option>Particulier</option>
                        </select>
                    </div>
                    <div class="field full-width">
                        <label for="address">Adresse</label>
                        <input id="address" name="address" type="text" placeholder="Adresse complète du sponsor">
                        <span id="address-error" class="error"></span>
                    </div>
                    <div class="field">
                        <label for="contact">Contact</label>
                        <input id="contact" name="contact" type="text" placeholder="0612345678">
                        <span id="contact-error" class="error"></span>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="text" placeholder="contact@sponsor.com">
                        <span id="email-error" class="error"></span>
                    </div>
                    
                    <div class="field full-width">
                        <label for="website">PageWeb</label>
                        <input id="website" name="website" type="text" placeholder="https://sponsor.fr">
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Confirmer</button>
                    <a class="btn btn-secondary btn-back" href="mesSponsors.php">Retour</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('sponsorForm').addEventListener('submit', function(event) {
            // Clear previous errors
            document.getElementById('name-error').textContent = '';
            document.getElementById('name-error').style.display = 'none';
            document.getElementById('address-error').textContent = '';
            document.getElementById('address-error').style.display = 'none';
            document.getElementById('contact-error').textContent = '';
            document.getElementById('contact-error').style.display = 'none';
            document.getElementById('email-error').textContent = '';
            document.getElementById('email-error').style.display = 'none';

            const name = document.getElementById('name').value.trim();
            const address = document.getElementById('address').value.trim();
            const contact = document.getElementById('contact').value.trim();
            const email = document.getElementById('email').value.trim();

            let hasErrors = false;

            if (name.length <= 0 || name.length >= 51) {
                document.getElementById('name-error').textContent = 'Le nom doit contenir entre 1 et 50 caractères.';
                document.getElementById('name-error').style.display = 'block';
                hasErrors = true;
            }
            if (address.length <= 0 || address.length >= 151) {
                document.getElementById('address-error').textContent = 'L\'adresse doit contenir entre 1 et 150 caractères.';
                document.getElementById('address-error').style.display = 'block';
                hasErrors = true;
            }
            if (!/^\d{8}$/.test(contact)) {
                document.getElementById('contact-error').textContent = 'Le contact doit être composé exactement de 8 chiffres.';
                document.getElementById('contact-error').style.display = 'block';
                hasErrors = true;
            }
            if (!/^[a-zA-Z0-9+._\u00C0-\u017F-]+@[a-zA-Z0-9+._\u00C0-\u017F-]+\.[a-zA-Z0-9+._\u00C0-\u017F-]+$/.test(email)) {
                document.getElementById('email-error').textContent = 'L\'email ne doit contenir que des lettres (y compris accentuées), chiffres, +, ., _, - et respecter le format xxxx@xxxx.xxxx.';
                document.getElementById('email-error').style.display = 'block';
                hasErrors = true;
            }

            if (hasErrors) {
                event.preventDefault();
            } else {
                // Allow form submission
            }
        });
    </script>
</body>
</html>