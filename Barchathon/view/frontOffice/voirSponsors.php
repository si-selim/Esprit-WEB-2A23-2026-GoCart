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
        .btn-export {
    background:#102a43;
    color:#fff;
    border:1px solid #102a43;
    font-weight:700;
}

.btn-export:hover {
    background:#0b1d2a;
    border-color:#0b1d2a;
    transform:translateY(-1px);
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





    /* 🔥 CONTENEUR GLOBAL */
.chat-box {
    position: fixed;
    bottom: 20px;
    right: 20px;

    width: 350px;
    height: 350px;

    max-width: 90%;
    border: 1px solid #ccc;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    font-family: Arial, sans-serif;
    background: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);

    z-index: 9999; /* 🔥 important pour rester au-dessus */
}

/* 📜 ZONE DES MESSAGES */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #f7f7f8;
    display: flex;
    flex-direction: column;
}

/* 💬 BULLES */
.message {
    margin: 10px 0;
    padding: 12px 14px;
    border-radius: 12px;
    max-width: 75%;
    font-size: 1.2em;
    line-height: 1.4;
    word-wrap: break-word;
}

/* 👤 UTILISATEUR */
.user {
    background: #007bff;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}

/* 🤖 IA */
.bot {
    background: #e5e5ea;
    color: black;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}

/* ⌨️ ZONE INPUT */
.chat-input-area {
    display: flex;
    border-top: 1px solid #ccc;
    background: #fff;
}

/* 📝 INPUT */
.chat-input-area input {
    flex: 1;
    padding: 12px;
    border: none;
    outline: none;
    font-size: 1.1em;
    border-bottom-left-radius: 12px;
}

/* 🔘 BOUTON */
.chat-input-area button {
    padding: 12px 18px;
    background: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1.1em;
    border-bottom-right-radius: 12px;
    transition: background 0.2s;
}

.chat-input-area button:hover {
    background: #0056b3;
}

/* 📱 RESPONSIVE (mobile) */
@media (max-width: 600px) {
    .chat-box {
        width: 95%;
        height: 450px;
    }

    .message {
        font-size: 1.1em;
    }
}

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
            <a class="fo-link " href="accueil.php">Accueil</a>
            <a class="fo-link " href="listMarathons.php">Catalogue</a>
            <a class="fo-link active" href="voirSponsors.php">Sponsors</a>
            <a class="fo-link" href="register.php">S'inscrire</a>
            <a class="fo-cta" href="login.php">Se connecter</a>
        </nav>
    </div>
    </div>
    
    
    
    
    <div class="page">
        <div class="section-title">
            <div>
                <h1>Voir sponsors</h1>
                <span>Consultez les sponsors et les sponsoring</span>
           

        <section id="sponsors" class="section-card">
            <h2>Sponsors</h2>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:22px; gap:20px;">
                <div class="search-box" style="width:125px;">
                    <label>
                        Rechercher un sponsor
                    <input type="search" id="searchSponsorView" placeholder="rechercher par nom">
                    </label>
                </div>
                <div class="filter-group">
                    <label>
                            Trier sponsors
                            <select id="sortSponsorsVoiSponsors">
                                <option value="az">A-Z</option>
                                <option value="za">Z-A</option>
                            </select>
                        </label>
                </div>
            </div>
            <div class="table-shell">
                <table id="sponsorsTableView">
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
                    <input type="search" id="searchSponsoringView" placeholder="rechercher par nom">
                    </label>
                </div>
                <div class="filter-group">
                    <label>
                            Filtrer par état
                            <select id="filterEtatVoirSponsors">
                                <option value="tout">Tout</option>
                                <option value="actif">Actif</option>
                                <option value="termine">Terminé</option>
                            </select>
                        </label>

                        <label>
                            Trier par montant
                            <select id="sortMontantVoirSponsors">
                                <option value="asc">Croissant</option>
                                <option value="desc">Décroissant</option>
                            </select>
                        </label>

                        <label>
                            Trier par date de fin
                            <select id="sortDateFinVoirSponsors">
                                <option value="asc">Croissant</option>
                                <option value="desc">Décroissant</option>
                            </select>
                        </label>
                </div>
            </div>
            <div class="table-shell">
                <table id="sponsoringTableView">
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

        

            <div class="chat-box">

                <div id="chatMessages" class="chat-messages"></div>

                <div class="chat-input-area">
                    <input type="text" id="chatInput" placeholder="Pose ta question...">
                    <button onclick="sendChat()">Envoyer</button>
                </div>



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

        // Fonction de recherche en temps réel pour sponsors (Vue)
        const searchSponsorViewInput = document.getElementById('searchSponsorView');
        const sponsorsViewTable = document.getElementById('sponsorsTableView');
        
        if (searchSponsorViewInput && sponsorsViewTable) {
            searchSponsorViewInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = sponsorsViewTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        // Chercher dans la cellule "Nom" (index 1)
                        const nomCell = cells[1] ? cells[1].textContent.toLowerCase() : '';
                        if (nomCell.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        }

        // Fonction de recherche en temps réel pour sponsoring (Vue)
        const searchSponsoringViewInput = document.getElementById('searchSponsoringView');
        const sponsoringViewTable = document.getElementById('sponsoringTableView');
        
        if (searchSponsoringViewInput && sponsoringViewTable) {
            searchSponsoringViewInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = sponsoringViewTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        // Chercher dans la cellule "Nom Sponsoring" (index 1)
                        const nomCell = cells[1] ? cells[1].textContent.toLowerCase() : '';
                        if (nomCell.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        }



        const sortSponsorsView = document.getElementById('sortSponsorsVoiSponsors');

        if (sortSponsorsView && sponsorsViewTable) {
            sortSponsorsView.addEventListener('change', function () {
                const rows = Array.from(sponsorsViewTable.querySelector('tbody').querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const nameA = a.cells[1].textContent.trim().toLowerCase();
                    const nameB = b.cells[1].textContent.trim().toLowerCase();

                    if (this.value === 'az') return nameA.localeCompare(nameB);
                    if (this.value === 'za') return nameB.localeCompare(nameA);
                    return 0;
                });

                const tbody = sponsorsViewTable.querySelector('tbody');
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            });
        }

        const filterEtatVoirSponsors = document.getElementById('filterEtatVoirSponsors');

        if (filterEtatVoirSponsors && sponsoringViewTable) {
            filterEtatVoirSponsors.addEventListener('change', function () {
                const value = this.value;
                const rows = sponsoringViewTable.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const etat = row.cells[5].textContent.trim().toLowerCase();

                    if (value === 'tout') {
                        row.style.display = '';
                    } else if (value === 'actif') {
                        row.style.display = etat === 'actif' ? '' : 'none';
                    } else if (value === 'termine') {
                        row.style.display = (etat === 'terminé' || etat === 'termine') ? '' : 'none';
                    }
                });
            });
        }

        const sortMontantVoirSponsors = document.getElementById('sortMontantVoirSponsors');

        if (sortMontantVoirSponsors && sponsoringViewTable) {
            sortMontantVoirSponsors.addEventListener('change', function () {
                const rows = Array.from(sponsoringViewTable.querySelector('tbody').querySelectorAll('tr'));

                rows.sort((a, b) => {
                    let mA = a.cells[4].textContent.replace(/[^\d.-]/g, '');
                    let mB = b.cells[4].textContent.replace(/[^\d.-]/g, '');

                    mA = parseFloat(mA) || 0;
                    mB = parseFloat(mB) || 0;

                    if (this.value === 'asc') return mA - mB;
                    if (this.value === 'desc') return mB - mA;
                    return 0;
                });

                const tbody = sponsoringViewTable.querySelector('tbody');
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            });
        }

        const sortDateFinVoirSponsors = document.getElementById('sortDateFinVoirSponsors');

        if (sortDateFinVoirSponsors && sponsoringViewTable) {
            sortDateFinVoirSponsors.addEventListener('change', function () {
                const rows = Array.from(sponsoringViewTable.querySelector('tbody').querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const dateA = new Date(a.cells[3].textContent.trim());
                    const dateB = new Date(b.cells[3].textContent.trim());

                    if (this.value === 'asc') return dateA - dateB;
                    if (this.value === 'desc') return dateB - dateA;
                    return 0;
                });

                const tbody = sponsoringViewTable.querySelector('tbody');
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            });
        }






       function addMessage(text, type) {
    let container = document.getElementById("chatMessages");

    let msg = document.createElement("div");
    msg.classList.add("message", type);
    msg.innerText = text;

    container.appendChild(msg);
    container.scrollTop = container.scrollHeight;

    return msg;
    }

    function sendChat() {

        let input = document.getElementById("chatInput");
        let msg = input.value;

        if (!msg.trim()) return;

        // 🔹 Message utilisateur
        addMessage(msg, "user");

        input.value = "";

        // 🔹 Message temporaire "Réflexion..."
        let thinkingMsg = addMessage("🤔 Réflexion...", "bot");

        fetch("chatSponsor_process.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "message=" + encodeURIComponent(msg)
        })
        .then(res => res.text())
        .then(data => {
            // 🔥 remplacer "Réflexion..." par vraie réponse
            thinkingMsg.innerText = data;
        })
        .catch(error => {
            thinkingMsg.innerText = "Erreur : " + error.message;
        });
    }
    </script>
</body>
</html>
