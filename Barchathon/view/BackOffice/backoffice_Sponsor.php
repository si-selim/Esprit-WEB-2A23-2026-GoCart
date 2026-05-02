<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Sponsors</title>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
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
        html, body { margin:0; min-height:100%; }
        body {
            font-family: "Segoe UI", sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #fefaf0 0%, var(--bg) 100%);
        }
        .layout { min-height:100vh; display:grid; grid-template-columns:280px 1fr; }
        .sidebar {
            background: linear-gradient(180deg, #0b2032 0%, #12314a 100%);
            color: #fff;
            padding: 28px 22px;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .brand { display:grid; gap:10px; padding-bottom:20px; border-bottom:1px solid rgba(255,255,255,.14); }
        .brand-badge { width:52px; height:52px; border-radius:18px; object-fit:cover; }
        .brand small, .side-note { color:rgba(255,255,255,.72); }
        .side-nav { display:grid; gap:10px; }
        .side-link {
            text-decoration:none;
            color:#fff;
            border:1px solid rgba(255,255,255,.1);
            background:rgba(255,255,255,.05);
            border-radius:16px;
            padding:12px 14px;
            font-weight:700;
        }
        .side-link.active { background:linear-gradient(135deg,var(--teal),#14b8a6); }
        .content { padding:28px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
        .head h1 { margin:0; font-size:2.2rem; }
        .muted { color:var(--muted); line-height:1.65; max-width:760px; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; }
        .btn { text-decoration:none; padding:11px 16px; border-radius:14px; font-weight:700; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .btn-primary { background:var(--teal); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.1); }
        .btn-export { background: #102a43; color:#fff; }
        .grid { display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); margin-bottom:24px; }
        .card, .section-card { background:var(--card); border-radius:24px; padding:22px; box-shadow:0 14px 34px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .section-title { margin:0 0 14px; font-size:1.45rem; }
        .table-shell { overflow:auto; }
        table { width:100%; min-width:880px; border-collapse:collapse; background:#fff; }
        th, td { padding:14px 12px; text-align:left; border-bottom:1px solid #e6edf3; vertical-align:top; }
        th { background:#102a43; color:#fff; position:sticky; top:0; }
        .tag { display:inline-block; padding:6px 10px; border-radius:999px; background:rgba(15,118,110,.12); color:var(--teal); font-weight:800; font-size:.86rem; }
        .toolbar { display:flex; flex-wrap:wrap; justify-content:space-between; gap:16px; margin-bottom:18px; align-items:center; }
        .toolbar-row { display:flex; flex-wrap:wrap; gap:12px; align-items:center; }
        .search-box { flex:1; min-width:240px; display:flex; gap:10px; }
        .search-box input, .filter-group select { width:100%; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink); }
        .filter-group { display:flex; gap:12px; flex-wrap:wrap; }
        .filter-group label { display:flex; flex-direction:column; gap:6px; font-size:.92rem; color:var(--muted); }
        .section-actions { margin-top:16px; display:flex; justify-content:flex-end; }
        .section-note { font-size:.95rem; color:var(--muted); margin-top:6px; }
        .mobile-nav { display:none; }
        .icon-btn { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; border:1px solid rgba(16,42,67,.12); background:#fff; color:var(--ink); cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; }
        .icon-btn:hover { transform:translateY(-1px); box-shadow:0 10px 18px rgba(16,42,67,.12); }
        .icon-delete { color:#d92d20; border-color:rgba(217,45,32,.15); }
        .icon-delete::before { content:"🗑"; font-size:2rem; }
        .row-actions { display:flex; gap:10px; }
        .icon-edit { color:#d97706; border-color:rgba(217,119,6,.18); }
        .icon-edit::before { content:"✏"; font-size:1rem; }
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.35); display:none; align-items:center; justify-content:center; padding:24px; z-index:9999; }
        .modal-overlay.active { display:flex; }
        .modal { width:min(500px,100%); background:#fff; border-radius:24px; padding:28px; box-shadow:0 24px 50px rgba(16,42,67,.2); }
        .modal h3 { margin:0 0 14px; font-size:1.4rem; color:var(--ink); }
        .modal p { margin:0 0 22px; color:var(--muted); line-height:1.6; }
        .modal-actions { display:flex; gap:12px; justify-content:flex-end; flex-wrap:wrap; }
        .modal-actions .btn { min-width:120px; }
        .modal-actions .btn-secondary { background:#f0f4f8; color:var(--ink); }
        .modal-actions .btn-danger { background:var(--coral); color:#fff; }

    </style>
</head>
<body>
    <?php include '../../controller/sponsorController.php'; include '../../controller/sponsoringController.php'; $controller = new sponsorController(); $sController = new sponsoringController(); ?>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-badge" src="../assets/images/logo_barchathon.jpg" alt="Logo Barchathon">
                <div>
                    <strong>Admin Back Office</strong><br>
                    <small>Gestion des sponsors et rapports</small>
                </div>
            </div>
            <nav class="side-nav">
                <a class="side-link" href="dashboard.php">Dashboard</a>
                <a class="side-link active" href="backoffice_Sponsor.php">Sponsors</a>
                <a class="side-link" href="#">Marathons</a>
                <a class="side-link" href="#">Participants</a>
                <a class="side-link" href="#">Paramètres</a>
            </nav>
            <div class="side-note">
                Consultation des données des sponsors, sponsoring et fourniture. Pas de modification sur cette page.
            </div>
        </aside>
        <main class="content">
            <div class="mobile-nav">
                <a class="btn btn-secondary" href="dashboard.php">Dashboard</a>
                <a class="btn btn-primary" href="backoffice_Sponsor.php">Sponsors</a>
            </div>
            <div class="head">
                <div>
                    <h1>Section sponsors</h1>
                    <div class="muted">Vue administrative simple pour consulter les tables des sponsors, des sponsoring et des fournitures. Recherche, filtres et export statique sont disponibles.</div>
                </div>
                <div class="actions">
                    <span class="tag">Lecture seule</span>
                    <span class="tag">Backoffice</span>
                </div>
            </div>
            <section class="section-card">
                <h2 class="section-title">Sponsors</h2>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box">
                            <label>
                                Rechercher un sponsor
                            <input type="search" id="searchSponsorBackoffice" placeholder="rechercher par nom">
                            </label>
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <div class="filter-group">
                            
                                filtrer ordre alphabétique
                                <select id="sortSponsorsBackoffice">
                                    <option value="">--</option>
                                    <option value="az">A-Z</option>
                                    <option value="za">Z-A</option>
                                </select>
                                
                            
                        </div>
                    </div>
                </div>
                <div class="table-shell">
                    <table id="sponsorsBackofficeTable">
                        <thead>
                            <tr>
                                <th>id Organisateur</th>
                                <th>id Sponsor</th>
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
                            <?php $controller->afficherSponsor(false, true); ?>
                        </tbody>
                    </table>
                </div>
                <div class="section-actions">
                    <button class="btn btn-export" onclick="exportSponsorsExcel()">Exporter sponsors</button>
                    <a class="btn btn-export" href="sponsorStat.php">Stats Sponsors</a>
                </div>
                <div class="section-note">Affichage statique de la table des sponsors. Les actions de modification sont désactivées.</div>
            </section>

            <div class="modal-overlay" id="deleteModal">
                <div class="modal">
                    <h3>Confirmer la suppression</h3>
                    <p id="deleteMessage">Êtes-vous sûr de vouloir supprimer ce sponsor ?</p>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" id="cancelDelete">Annuler</button>
                        <button class="btn btn-danger" id="confirmDelete">Confirmer</button>
                    </div>
                </div>
            </div>

            <section class="section-card" style="margin-top:24px;">
                <h2 class="section-title">Sponsoring</h2>
                <div class="toolbar">
                    <div class="search-box">
                        <label>
                            rechercher un sponsoring
                        <input type="search" id="searchSponsoringBackoffice" placeholder="Rechercher par nom ou par état">
                        </label>
                    </div>
                    <div class="filter-group">
                        <label for="filterEtatBackoffice">
                            Filtrer par état
                            <select id="filterEtatBackoffice">
                                <option value="tout">Tout</option>
                                <option value="actif">Actif</option>
                                <option value="termine">Terminé</option>
                            </select>
                        </label>

                        <label for="sortMontantBackoffice">
                            Trier par montant
                            <select id="sortMontantBackoffice">
                                <option value="">--</option>
                                <option value="asc">Croissant</option>
                                <option value="desc">Décroissant</option>
                            </select>
                        </label>

                        <label for="sortDateFinBackoffice">
                            Trier par date de fin
                            <select id="sortDateFinBackoffice">
                                <option value="">--</option>
                                <option value="asc">Croissant</option>
                                <option value="desc">Décroissant</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="table-shell">
                    <table id="sponsoringBackofficeTable">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sController->afficherSponsoring(false, true, false); ?>
                        </tbody>
                    </table>
                </div>
                <div class="section-actions">
                    <button class="btn btn-export" onclick="exportSponsoringExcel()">Exporter sponsoring</button>
                    <a class="btn btn-export" href="sponsoringStat.php">Stats Sponsoring</a>
                </div>
                <div class="section-note">Représentation simple des contrats de sponsoring, avec recherche et filtres par état et montant.</div>
            </section>
            <section class="section-card" style="margin-top:24px;">
                <h2 class="section-title">Fourniture</h2>
                <div class="toolbar">
                    <div class="search-box">
                        <label>
                            Rechercher une fourniture
                        <input type="search" placeholder="Rechercher une fourniture ou un type">
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
                            Filtrer par disponibilité
                            <select>
                                <option>Tout</option>
                                <option>Faible stock</option>
                                <option>Stock suffisant</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Actions</th>
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
                                <td>
                                    <a href="#sponsoring" class="btn btn-secondary">Voir sponsoring</a>
                                    <a href="#fournitures" class="btn btn-secondary">Voir fourniture</a>
                                </td>
                                <td>301</td>
                                <td>Nourritures</td>
                                <td>Barres énergétiques</td>
                                <td>1200</td>
                                <td>1,80 €</td>
                                <td>Collations pour coureurs</td>
                                <td>2026-04-10</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="#sponsoring" class="btn btn-secondary">Voir sponsoring</a>
                                    <a href="#fournitures" class="btn btn-secondary">Voir fourniture</a>
                                </td>
                                <td>302</td>
                                <td>Vêtements</td>
                                <td>T-shirts officiels</td>
                                <td>500</td>
                                <td>8,50 €</td>
                                <td>Maillots de l'événement</td>
                                <td>2026-04-18</td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="#sponsoring" class="btn btn-secondary">Voir sponsoring</a>
                                    <a href="#fournitures" class="btn btn-secondary">Voir fourniture</a>
                                </td>
                                <td>303</td>
                                <td>Matériel médical</td>
                                <td>Trousse premiers secours</td>
                                <td>30</td>
                                <td>45,00 €</td>
                                <td>Équipements de secours</td>
                                <td>2026-04-08</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="section-actions">
                    <button class="btn btn-export">Exporter fourniture</button>
                </div>
                <div class="section-note">Toutes les fournitures sont affichées en mode lecture seule. Le filtre par type aide à trier rapidement.</div>
            </section>
        </main>
    </div>

    <script>
        const deleteModal = document.getElementById('deleteModal');
        const deleteMessage = document.getElementById('deleteMessage');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        let deleteHref = null;

        document.querySelectorAll('.delete-sponsor-btn, .delete-sponsoring-btn').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                deleteHref = button.getAttribute('href');
                const itemName = button.dataset.sponsorName || button.dataset.sponsoringName || 'cet élément';
                deleteMessage.textContent = `Êtes-vous sûr de vouloir supprimer ${itemName} ?`;
                deleteModal.classList.add('active');
            });
        });

        cancelDelete.addEventListener('click', () => {
            deleteHref = null;
            deleteModal.classList.remove('active');
        });

        confirmDelete.addEventListener('click', () => {
            if (deleteHref) {
                window.location.href = deleteHref;
            }
        });

        deleteModal.addEventListener('click', event => {
            if (event.target === deleteModal) {
                deleteModal.classList.remove('active');
            }
        });

        // Fonction de recherche en temps réel pour sponsors (Backoffice)
        const searchSponsorBackofficeInput = document.getElementById('searchSponsorBackoffice');
        const sponsorsBackofficeTable = document.getElementById('sponsorsBackofficeTable');
        
        if (searchSponsorBackofficeInput && sponsorsBackofficeTable) {
            searchSponsorBackofficeInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = sponsorsBackofficeTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        // Chercher dans la cellule "Nom" (index 2)
                        const nomCell = cells[2] ? cells[2].textContent.toLowerCase() : '';
                        if (nomCell.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        }

        // Fonction de recherche en temps réel pour sponsoring (Backoffice)
        const searchSponsoringBackofficeInput = document.getElementById('searchSponsoringBackoffice');
        const sponsoringBackofficeTable = document.getElementById('sponsoringBackofficeTable');
        
        if (searchSponsoringBackofficeInput && sponsoringBackofficeTable) {
            searchSponsoringBackofficeInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = sponsoringBackofficeTable.querySelectorAll('tbody tr');
                
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



        const sortSponsorsBackoffice = document.getElementById('sortSponsorsBackoffice');

        if (sortSponsorsBackoffice) {
            sortSponsorsBackoffice.addEventListener('change', function () {
                const table = document.getElementById('sponsorsBackofficeTable');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const A = a.cells[2].textContent.toLowerCase();
                    const B = b.cells[2].textContent.toLowerCase();
                    return this.value === 'az' ? A.localeCompare(B) : B.localeCompare(A);
                });

                rows.forEach(r => tbody.appendChild(r));
            });
        }

        // ===== SPONSORING FILTRES =====
        const filterEtatBackoffice = document.getElementById('filterEtatBackoffice');
        const sortMontantBackoffice = document.getElementById('sortMontantBackoffice');
        const sortDateFinBackoffice = document.getElementById('sortDateFinBackoffice');
function applyFiltersBackoffice() {
    const table = document.getElementById('sponsoringBackofficeTable');
    const tbody = table.querySelector('tbody');

    let rows = Array.from(tbody.querySelectorAll('tr'));

    // reset affichage
    rows.forEach(r => r.style.display = '');

    // ===== FILTRE ETAT =====
    if (filterEtatBackoffice.value !== 'tout') {
        rows.forEach(row => {
            const etat = row.cells[5].textContent
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");

            row.style.display = (etat === filterEtatBackoffice.value) ? '' : 'none';
        });
    }

    // garder seulement visibles pour tri
    rows = rows.filter(r => r.style.display !== 'none');

    // ===== TRI MONTANT =====
    if (sortMontantBackoffice.value) {
        rows.sort((a, b) => {
            const A = parseFloat(a.cells[4].textContent.replace(/[^\d.-]/g, '')) || 0;
            const B = parseFloat(b.cells[4].textContent.replace(/[^\d.-]/g, '')) || 0;
            return sortMontantBackoffice.value === 'asc' ? A - B : B - A;
        });
    }

    // ===== TRI DATE =====
    if (sortDateFinBackoffice.value) {
        rows.sort((a, b) => {
            const A = new Date(a.cells[3].textContent);
            const B = new Date(b.cells[3].textContent);
            return sortDateFinBackoffice.value === 'asc' ? A - B : B - A;
        });
    }

    // réinjection propre
    rows.forEach(r => tbody.appendChild(r));
}

        filterEtatBackoffice?.addEventListener('change', applyFiltersBackoffice);
        sortMontantBackoffice?.addEventListener('change', applyFiltersBackoffice);
        sortDateFinBackoffice?.addEventListener('change', applyFiltersBackoffice);



    function exportSponsorsExcel() {
    const table = document.getElementById("sponsorsBackofficeTable");

    const wb = XLSX.utils.book_new();

    // ⚡ conversion directe propre
    const ws = XLSX.utils.table_to_sheet(table);

    // supprimer colonne Actions proprement
    const range = XLSX.utils.decode_range(ws['!ref']);

    for (let R = range.s.r; R <= range.e.r; R++) {
        const addr = XLSX.utils.encode_cell({ r: R, c: range.e.c });
        delete ws[addr];
    }

    // AUTO WIDTH
    applyAutoWidth(ws);

    XLSX.utils.book_append_sheet(wb, ws, "Sponsors");
    XLSX.writeFile(wb, "sponsors.xlsx");
}
function formatExcelDate(value) {
    if (!value) return "";

    // déjà format ISO
    if (typeof value === "string" && value.includes("-")) {
        return value;
    }

    // Excel number date
    if (typeof value === "number") {
        const date = new Date(Math.round((value - 25569) * 86400 * 1000));

        if (!isNaN(date)) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, "0");
            const d = String(date.getDate()).padStart(2, "0");
            return `${y}-${m}-${d}`;
        }
    }

    return "";
}

function exportSponsoringExcel() {
    const table = document.getElementById("sponsoringBackofficeTable");
    const wb = XLSX.utils.book_new();

    const rows = [];
    const tr = table.querySelectorAll("tr");

    tr.forEach((row, rowIndex) => {
        const cells = row.querySelectorAll("th, td");

        const line = [];

        cells.forEach((cell, colIndex) => {
            const text = cell.innerText.trim();

            // ❌ supprimer colonne ACTIONS (dernière colonne)
            if (colIndex === cells.length - 1) return;

            line.push(text);
        });

        rows.push(line);
    });

    // correction dates si besoin
    for (let i = 1; i < rows.length; i++) {
        rows[i][2] = formatExcelDate(rows[i][2]); // date début
        rows[i][3] = formatExcelDate(rows[i][3]); // date fin
    }

    const ws = XLSX.utils.aoa_to_sheet(rows);

    applyAutoWidth(ws);

    XLSX.utils.book_append_sheet(wb, ws, "Sponsoring");
    XLSX.writeFile(wb, "sponsoring.xlsx");
}
function applyAutoWidth(ws) {
    const range = XLSX.utils.decode_range(ws['!ref']);
    const colWidths = [];

    for (let C = range.s.c; C <= range.e.c; C++) {
        let max = 10;

        for (let R = range.s.r; R <= range.e.r; R++) {
            const cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];

            if (cell && cell.v) {
                const len = cell.v.toString().length;
                if (len > max) max = len;
            }
        }

        colWidths.push({ wch: Math.min(max + 2, 60) });
    }

    ws["!cols"] = colWidths;
}

    </script>
</body>
</html>