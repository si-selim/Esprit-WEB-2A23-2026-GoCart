<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un sponsoring</title>
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
        input, select, .button-group { width:100%; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink); }
        .button-group { display:flex; gap:12px; }
        .button-group a { display:inline-flex; align-items:center; justify-content:center; padding:0 14px; border-radius:14px; background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; text-decoration:none; font-weight:700; min-height:44px; }
        .full-width { grid-column:1 / -1; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:24px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 18px; border-radius:14px; font-weight:700; border:0; cursor:pointer; text-decoration:none; }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        .error { color:#d92d20; font-size:0.9rem; display:block; margin-top:4px; }
        @media (max-width:760px) { .grid { grid-template-columns:1fr; } }
    
    
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
    <?php
    include '../../config.php';
    include '../../controller/sponsoringController.php';
    include '../../controller/sponsorController.php';

    $selectedSponsor = null;
    $selectedMarathon = null;
    $idSponsor = null;
    $idMarathon = null;
    $formName = isset($_GET['formName']) ? $_GET['formName'] : '';
    $formDateDebut = isset($_GET['formDateDebut']) ? $_GET['formDateDebut'] : '';
    $formDateFin = isset($_GET['formDateFin']) ? $_GET['formDateFin'] : '';
    $formMontant = isset($_GET['formMontant']) ? $_GET['formMontant'] : '';
    $formEtat = isset($_GET['formEtat']) ? $_GET['formEtat'] : '';

    $sponsoringCtrl = new sponsoringController();
    $sponsorCtrl = new sponsorController();

    if (isset($_GET['idSponsor'])) {
        $idSponsor = $_GET['idSponsor'];
        $selectedSponsor = $sponsorCtrl->showSponsor($idSponsor);
    }
    if (isset($_GET['idMarathon'])) {
        $idMarathon = $_GET['idMarathon'];
        $selectedMarathon = $sponsoringCtrl->showMarathon($idMarathon);
    }
    ?>


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
            <h1>Ajouter un sponsoring</h1>
            <p>Formulaire pour enregistrer un nouveau contrat de sponsoring. Les sélections ouvrent des pages de choix de sponsor et de marathon.</p>
            <form id="sponsoringForm" method="POST" action="addSponsoring_process.php">
                <?php if ($selectedSponsor): ?>
                    <input type="hidden" name="idSponsor" value="<?php echo $idSponsor; ?>">
                <?php endif; ?>
                <?php if ($selectedMarathon): ?>
                    <input type="hidden" name="idMarathon" value="<?php echo $idMarathon; ?>">
                <?php endif; ?>
                <div class="grid">
                    <div class="field full-width">
                        <label for="name">Nom Sponsoring</label>
                        <input id="name" name="name" type="text" placeholder="Nom du sponsoring" value="<?php echo htmlspecialchars($formName); ?>">
                        <span id="name-error" class="error"></span>
                    </div>
                    <div class="field full-width">
                        <label>Sponsor</label>
                        <?php if ($selectedSponsor): ?>
                            <input type="text" value="<?php echo htmlspecialchars($selectedSponsor['nom']); ?>" readonly>
                            <a class="btn btn-secondary" href="#" data-preserve-form data-target="chooseSponsorSponsoring.php">Changer</a>
                        <?php else: ?>
                            <div class="button-group">
                                <a href="#" data-preserve-form data-target="chooseSponsorSponsoring.php">Choisir un sponsor</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="field full-width">
                        <label>Marathon sponsorisé</label>
                        <?php if ($selectedMarathon): ?>
                            <input type="text" value="<?php echo htmlspecialchars($selectedMarathon['nom_marathon']); ?>" readonly>
                            <a class="btn btn-secondary" href="#" data-preserve-form data-target="chooseMarathon.php">Changer</a>
                        <?php else: ?>
                            <div class="button-group">
                                <a href="#" data-preserve-form data-target="chooseMarathon.php">Choisir un marathon</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="dateDebut">Date Début</label>
                        <input id="dateDebut" name="dateDebut" type="date" value="<?php echo htmlspecialchars($formDateDebut); ?>">
                    </div>
                    <div class="field">
                        <label for="dateFin">Date Fin</label>
                        <input id="dateFin" name="dateFin" type="date" value="<?php echo htmlspecialchars($formDateFin); ?>">
                        <span id="date-error" class="error"></span>
                    </div>
                    <div class="field">
                        <label for="montant">Montant</label>
                        <input id="montant" name="montant" type="number" step="0.01" placeholder="12000.00" value="<?php echo htmlspecialchars($formMontant); ?>">
                        <span id="montant-error" class="error"></span>
                    </div>
                    <div class="field">
                        <label for="etat">État</label>
                        <select id="etat" name="etat">
                            <option<?php echo $formEtat === 'Actif' ? ' selected' : ''; ?>>Actif</option>
                            <option<?php echo $formEtat === 'Terminé' ? ' selected' : ''; ?>>Terminé</option>
                            <option<?php echo $formEtat === 'Annulé' ? ' selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Confirmer</button>
                    <a class="btn btn-secondary" href="mesSponsors.php">Retour</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const preserveLinks = document.querySelectorAll('a[data-preserve-form]');
            
            preserveLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('data-target');
                    const name = document.getElementById('name').value;
                    const dateDebut = document.getElementById('dateDebut').value;
                    const dateFin = document.getElementById('dateFin').value;
                    const montant = document.getElementById('montant').value;
                    const etat = document.getElementById('etat').value;
                    
                    const params = new URLSearchParams();
                    if (name) params.append('formName', name);
                    if (dateDebut) params.append('formDateDebut', dateDebut);
                    if (dateFin) params.append('formDateFin', dateFin);
                    if (montant) params.append('formMontant', montant);
                    if (etat) params.append('formEtat', etat);
                    
                    const idMarathon = '<?php echo $idMarathon; ?>';
                    const idSponsor = '<?php echo $idSponsor; ?>';
                    
                    let url = target;
                    if (idMarathon && target === 'chooseSponsorSponsoring.php') {
                        url += '?idMarathon=' + idMarathon + '&' + params.toString();
                    } else if (idSponsor && target === 'chooseMarathon.php') {
                        url += '?idSponsor=' + idSponsor + '&' + params.toString();
                    } else if (params.toString()) {
                        url += '?' + params.toString();
                    }
                    
                    window.location.href = url;
                });
            });
        });

        document.getElementById('sponsoringForm').addEventListener('submit', function(event) {
            var nameField = document.getElementById('name');
            var dateDebutField = document.getElementById('dateDebut');
            var dateFinField = document.getElementById('dateFin');
            var montantField = document.getElementById('montant');

            var nameError = document.getElementById('name-error');
            var dateError = document.getElementById('date-error');
            var montantError = document.getElementById('montant-error');

            nameError.textContent = '';
            dateError.textContent = '';
            montantError.textContent = '';

            var name = nameField.value.trim();
            var dateDebut = dateDebutField.value;
            var dateFin = dateFinField.value;
            var montant = montantField.value.trim();

            var hasError = false;

            if (name.length === 0 || name.length >= 51) {
                nameError.textContent = 'Le nom doit contenir entre 1 et 50 caractères.';
                hasError = true;
            }
            if (dateDebut === '' || dateFin === '') {
                dateError.textContent = 'Les deux dates doivent être renseignées.';
                hasError = true;
            } else if (dateDebut >= dateFin) {
                dateError.textContent = 'La date de début doit être antérieure à la date de fin.';
                hasError = true;
            }
            if (montant === '' || isNaN(montant) || parseFloat(montant) <= 0) {
                montantError.textContent = 'Le montant doit être un nombre strictement supérieur à 0.';
                hasError = true;
            }

            if (hasError) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>