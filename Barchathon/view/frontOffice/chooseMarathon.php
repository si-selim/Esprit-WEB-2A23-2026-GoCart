<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir un marathon</title>
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
        .table-shell { overflow:auto; }
        table { width:100%; min-width:780px; border-collapse:collapse; background:#fff; }
        th, td { padding:14px 12px; text-align:left; border-bottom:1px solid #e6edf3; }
        th { background:#102a43; color:#fff; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 18px; border-radius:14px; font-weight:700; border:0; cursor:pointer; text-decoration:none; }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        .actions { margin-top:24px; display:flex; gap:12px; }
        @media (max-width:760px) { .page { padding:20px; } table { min-width:100%; } }
    
    
    
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
            <h1>Choisir un marathon</h1>
            <p>Sélectionnez le marathon sponsorisé. Une fois votre choix effectué, retournez au formulaire pour compléter les informations.</p>
            <?php
            $idSponsor = isset($_GET['idSponsor']) ? $_GET['idSponsor'] : null;
            $formName = isset($_GET['formName']) ? $_GET['formName'] : '';
            $formDateDebut = isset($_GET['formDateDebut']) ? $_GET['formDateDebut'] : '';
            $formDateFin = isset($_GET['formDateFin']) ? $_GET['formDateFin'] : '';
            $formMontant = isset($_GET['formMontant']) ? $_GET['formMontant'] : '';
            $formEtat = isset($_GET['formEtat']) ? $_GET['formEtat'] : '';
            ?>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Marathon</th>
                            <th>Ville</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            include '../../controller/sponsoringController.php'; 
                            $controller = new sponsoringController(); 
                            $extraParams = $idSponsor ? "&idSponsor=$idSponsor" : ''; 
                            $formParamsArray = [];
                            if ($formName) $formParamsArray[] = 'formName=' . urlencode($formName);
                            if ($formDateDebut) $formParamsArray[] = 'formDateDebut=' . $formDateDebut;
                            if ($formDateFin) $formParamsArray[] = 'formDateFin=' . $formDateFin;
                            if ($formMontant) $formParamsArray[] = 'formMontant=' . $formMontant;
                            if ($formEtat) $formParamsArray[] = 'formEtat=' . $formEtat;
                            $formParams = $formParamsArray ? '&' . implode('&', $formParamsArray) : '';
                            $controller->afficherMarathon(true, $extraParams, $formParams); 
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="actions">
                <a class="btn btn-secondary" href="addSponsoring.php<?php 
                    $params = [];
                    if ($idSponsor) $params[] = 'idSponsor=' . $idSponsor;
                    if ($formName) $params[] = 'formName=' . urlencode($formName);
                    if ($formDateDebut) $params[] = 'formDateDebut=' . $formDateDebut;
                    if ($formDateFin) $params[] = 'formDateFin=' . $formDateFin;
                    if ($formMontant) $params[] = 'formMontant=' . $formMontant;
                    if ($formEtat) $params[] = 'formEtat=' . $formEtat;
                    echo $params ? '?' . implode('&', $params) : '';
                ?>">Retour au formulaire</a>
            </div>
        </div>
    </div>
</body>
</html>