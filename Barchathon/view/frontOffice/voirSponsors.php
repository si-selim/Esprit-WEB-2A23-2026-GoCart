<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir sponsors</title>
    <style>
        :root {
            --ink:#102a43;
            --teal:#0f766e;
            --sun:#ffb703;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --coral:#e76f51;
            --line:#d9e2ec;
            --nav:#0b2032;
        }
        * { box-sizing:border-box; }
        body {
            margin:0;
            font-family:"Segoe UI",sans-serif;
            color:var(--ink);
            background:linear-gradient(180deg,#fefaf0 0%, var(--bg) 100%);
        }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0 56px; }
        .toolbar { display:flex; flex-wrap:wrap; justify-content:space-between; gap:16px; margin-bottom:22px; align-items:center; }
        .toolbar-left { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .toolbar-right { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:14px; text-decoration:none; font-weight:700; border:0; cursor:pointer; }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        .btn-danger { background:var(--coral); color:#fff; }
        .btn-warning { background:#ff8c42; color:#102a43; }
        .export-btn { background:#102a43; color:#fff; }
        .section-card { background:var(--card); border-radius:24px; padding:22px; box-shadow:0 14px 34px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); margin-bottom:28px; }
        .section-title { display:flex; justify-content:space-between; align-items:flex-end; gap:18px; margin-bottom:18px; }
        .section-title h1 { margin:0; font-size:2rem; }
        .section-title span { color:var(--muted); }
        .search-box { flex:1; min-width:330px; display:flex; gap:10px; }
        .search-box input, .filter-group select { width:100%; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink); }
        .filter-group { display:flex; flex-wrap:wrap; gap:12px; }
        .filter-group label { display:flex; flex-direction:column; gap:6px; font-size:.92rem; color:var(--muted); }
        .table-shell { overflow:auto; }
        table { width:100%; min-width:860px; border-collapse:collapse; background:#fff; }
        th, td { padding:14px 12px; text-align:left; border-bottom:1px solid #e6edf3; vertical-align:middle; }
        th { background:#102a43; color:#fff; position:sticky; top:0; }
        .tag { display:inline-block; padding:6px 10px; border-radius:999px; background:rgba(15,118,110,.12); color:var(--teal); font-weight:800; font-size:.86rem; }
        .note { font-size:.95rem; color:var(--muted); margin-top:12px; }
        @media (max-width: 980px) {
            .page { padding:20px 0 40px; }
            .toolbar { flex-direction:column; align-items:flex-start; }
            .section-title { flex-direction:column; align-items:flex-start; }
            .search-box, .filter-group { width:100%; }
            table { min-width:720px; }
        }


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
    <?php include '../../controller/sponsorController.php'; include '../../controller/sponsoringController.php'; $controller = new sponsorController(); $sController = new sponsoringController(); ?>
    
    <div class="fo-topbar">
    <div class="fo-topbar-shell">
        <a class="fo-brand" href="accueil.php">
            <img src="../assets/images/logo_barchathon.jpg" alt="BarchaThon">
            BarchaThon
        </a>
        <nav class="fo-nav">
            <a class="fo-link active" href="accueil.php">Accueil</a>
            <a class="fo-link " href="listMarathons.php">Catalogue</a>
            <a class="fo-link" href="voirSponsors.php">Sponsors</a>
            <a class="fo-link" href="register.php">S'inscrire</a>
            <a class="fo-cta" href="login.php">Se connecter</a>
        </nav>
    </div>
    </div>
    
    
    
    
    <div class="page">
        <div class="section-title">
            <div>
                <h1>Voir sponsors</h1>
                <span>Consultez les sponsors, sponsoring et fournitures.</span>
           

        <section id="sponsors" class="section-card">
            <h2>Sponsors</h2>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:22px; gap:20px;">
                <div class="search-box" style="width:125px;">
                    <label>
                        Rechercher un sponsor
                    <input type="search" placeholder="rechercher par nom">
                    </label>
                </div>
                <div class="filter-group">
                    <label>
                        Filtrer ordre alphabétique
                        <select>
                            <option>A-Z</option>
                            <option>Z-A</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th style="width: 500px;">Adresse</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>PageWeb</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $controller->afficherSponsor(false, false, true); ?>
                    </tbody>
                </table>
            </div>
            </section>

        <section id="sponsoring" class="section-card">
            <h2>Sponsoring</h2>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:22px; gap:20px;">
                <div class="search-box" style="width:125px;">
                    <label>
                        Rechercher un sponsoring
                    <input type="search" placeholder="rechercher par nom">
                    </label>
                </div>
                <div class="filter-group">
                    <label>
                        Filtrer par date début
                        <select>
                            <option>Tout</option>
                            <option>2026-01</option>
                            <option>2026-02</option>
                            <option>2026-03</option>
                            <option>2026-04</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par date fin
                        <select>
                            <option>Tout</option>
                            <option>2026-10</option>
                            <option>2026-11</option>
                            <option>2026-12</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par montant
                        <select>
                            <option>Tout</option>
                            <option>0-5000</option>
                            <option>5000-10000</option>
                            <option>10000+</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par état
                        <select>
                            <option>Tout</option>
                            <option>Actif</option>
                            <option>Terminé</option>
                            <option>Annulé</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom Sponsoring</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Montant</th>
                            <th>État</th>
                            <th>id Sponsor</th>
                            <th>id Marathon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (isset($_GET['idSponsor'])) {
                            $sController->afficherSponsoringSponsor($_GET['idSponsor'], false);
                        } else {
                            $sController->afficherSponsoring(false, false, false);
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            </section>

        <section id="fournitures" class="section-card">
            <h2>Fournitures</h2>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:22px; gap:20px;">
                <div class="search-box" style="width:125px;">
                    <label>
                        Rechercher une fourniture
                    <input type="search" placeholder="rechercher par nom">
                    </label>
                </div>
                <div class="filter-group">
                    <label>
                        Filtrer par type
                        <select>
                            <option>Tout</option>
                            <option>Nourritures</option>
                            <option>Vêtements</option>
                            <option>Matériel médical</option>
                            <option>Caméra</option>
                            <option>Micro</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par quantité
                        <select>
                            <option>Tout</option>
                            <option>0-100</option>
                            <option>100-500</option>
                            <option>500+</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par prix unitaire
                        <select>
                            <option>Tout</option>
                            <option>0-5€</option>
                            <option>5-10€</option>
                            <option>10+€</option>
                        </select>
                    </label>
                    <label>
                        Filtrer par date fourniture
                        <select>
                            <option>Tout</option>
                            <option>2026-04</option>
                            <option>2026-05</option>
                            <option>2026-06</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Nom fourniture</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Description</th>
                            <th>Date fourniture</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>301</td>
                            <td>Nourritures</td>
                            <td>Barres énergétiques</td>
                            <td>1200</td>
                            <td>1,80 €</td>
                            <td>Collations pour coureurs</td>
                            <td>2026-04-10</td>
                        </tr>
                        <tr>
                            <td>302</td>
                            <td>Vêtements</td>
                            <td>T-shirts officiels</td>
                            <td>500</td>
                            <td>8,50 €</td>
                            <td>Maillots de l'événement</td>
                            <td>2026-04-18</td>
                        </tr>
                        <tr>
                            <td>303</td>
                            <td>Matériel médical</td>
                            <td>Trousse premiers secours</td>
                            <td>30</td>
                            <td>45,00 €</td>
                            <td>Équipement de secours</td>
                            <td>2026-04-08</td>
                        </tr>
                    </tbody>
                </table>
            </div>
           </section>
    </div>

    <script>
        // Gestionnaire pour les boutons "Voir sponsoring"
        document.querySelectorAll('.view-sponsoring-btn').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                const sponsorId = button.dataset.sponsorId;
                // Rediriger vers la page avec le paramètre du sponsor
                window.location.href = `voirSponsors.php?idSponsor=${sponsorId}#sponsoring`;
            });
        });
    </script>
</body>
</html>
