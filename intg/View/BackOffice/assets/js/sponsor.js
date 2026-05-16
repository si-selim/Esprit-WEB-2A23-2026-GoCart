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