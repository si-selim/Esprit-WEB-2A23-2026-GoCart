function calculerPrix() {
    var nb      = document.getElementById("nb_personnes");
    var circuit = document.getElementById("circuit");
    var champ   = document.getElementById("prix_total");
    if (!nb || !circuit || !champ) return;
    var nombre = parseInt(nb.value);
    if (isNaN(nombre) || nombre <= 0) { champ.value = "0 TND"; return; }
    var prix = { "1": 20, "2": 40, "3": 60 };
    var pu = prix[circuit.value];
    if (!pu) { champ.value = "0 TND"; return; }
    var total = pu * nombre;
    if (nombre >= 5) total *= 0.8;
    else if (nombre >= 3) total *= 0.9;
    champ.value = total.toFixed(2) + " TND";
}

function setTodayDate() {
    var d = document.getElementById("date_paiement");
    if (d) d.value = new Date().toISOString().split('T')[0];
}

window.fillForm = function(id, nbVal, modeVal, dateVal, parcours) {
    document.getElementById("id_inscription").value = id;
    document.getElementById("nb_personnes").value   = nbVal;
    document.getElementById("mode_paiement").value  = modeVal;
    document.getElementById("date_paiement").value  = dateVal;
    document.getElementById("circuit").value        = String(parcours);
    calculerPrix();
};

function applyFilters() {
    var idVal      = document.getElementById("search_id")      ? document.getElementById("search_id").value.trim()  : "";
    var statutVal  = document.getElementById("filter_statut")  ? document.getElementById("filter_statut").value     : "";
    var circuitVal = document.getElementById("filter_circuit") ? document.getElementById("filter_circuit").value    : "";
    var nbVal      = document.getElementById("filter_nb")      ? document.getElementById("filter_nb").value         : "";

    var rows = document.querySelectorAll("#table-body tr");
    rows.forEach(function(row) {
        var cells = row.querySelectorAll("td");
        if (cells.length < 6) { row.style.display = ""; return; }

        var rowId      = row.getAttribute("data-id")      || "";
        var rowStatut  = row.getAttribute("data-statut")  || "";
        var rowCircuit = row.getAttribute("data-circuit") || "";
        var rowNb      = parseInt(row.getAttribute("data-nb") || "0");

        
        if (!rowStatut) {
            var txt = cells[5].textContent.trim().toLowerCase();
            rowStatut = (txt.indexOf("non") !== -1) ? "unpaid" : "paid";
        }
        if (!rowCircuit) rowCircuit = cells[2].textContent.trim();
        if (!rowNb)      rowNb      = parseInt(cells[3].textContent.trim()) || 0;

        var show = true;
        if (idVal      !== "" && rowId.indexOf(idVal) !== 0)    show = false;
        if (statutVal  !== "" && rowStatut  !== statutVal)       show = false;
        if (circuitVal !== "" && rowCircuit !== circuitVal)      show = false;
        if (nbVal === "1"   && rowNb !== 1)                      show = false;
        if (nbVal === "2-4" && (rowNb < 2 || rowNb > 4))        show = false;
        if (nbVal === "5+"  && rowNb < 5)                        show = false;

        row.style.display = show ? "" : "none";
    });
}

console.log("inscription.js chargé OK");

document.addEventListener("DOMContentLoaded", function () {
    setTodayDate();
    calculerPrix();

    var form    = document.querySelector("form");
    var nb      = document.getElementById("nb_personnes");
    var circuit = document.getElementById("circuit");
    var mode    = document.getElementById("mode_paiement");
    var date    = document.getElementById("date_paiement");

    function validateNb() {
        var e = document.getElementById("error-nb_personnes");
        var ok = nb.value.trim() !== "" && Number(nb.value) > 0;
        e.innerText = ok ? "OK" : "Nombre invalide";
        e.style.color = ok ? "green" : "red";
        return ok;
    }
    function validateCircuit() {
        var e = document.getElementById("error-circuit");
        var ok = circuit.value !== "";
        e.innerText = ok ? "OK" : "Choisir circuit";
        e.style.color = ok ? "green" : "red";
        return ok;
    }
    function validateMode() {
        var e = document.getElementById("error-mode_paiement");
        var ok = mode.value !== "";
        e.innerText = ok ? "OK" : "Choisir mode";
        e.style.color = ok ? "green" : "red";
        return ok;
    }
    function validateDate() {
        var e = document.getElementById("error-date_paiement");
        if (!e) return true;
        var ok = date.value !== "";
        e.innerText = ok ? "OK" : "Date obligatoire";
        e.style.color = ok ? "green" : "red";
        return ok;
    }

    nb.addEventListener("input",       function() { validateNb();      calculerPrix(); });
    circuit.addEventListener("change", function() { validateCircuit(); calculerPrix(); });
    mode.addEventListener("change",    validateMode);
    date.addEventListener("change",    validateDate);

    form.addEventListener("submit", function(e) {
        var ok = validateNb() && validateCircuit() && validateMode() && validateDate();
        if (!ok) { e.preventDefault(); alert("Vérifie les champs !"); }
    });

    var s  = document.getElementById("search_id");
    var fs = document.getElementById("filter_statut");
    var fc = document.getElementById("filter_circuit");
    var fn = document.getElementById("filter_nb");

    if (s)  s.addEventListener("input",   applyFilters);
    if (fs) fs.addEventListener("change", applyFilters);
    if (fc) fc.addEventListener("change", applyFilters);
    if (fn) fn.addEventListener("change", applyFilters);
});