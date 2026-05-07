// =====================
// 🔥 FONCTIONS UTILITAIRES
// =====================
function showError(element, message) {
  element.innerText = message;
  element.style.color = "red";
}

function showSuccess(element, message) {
  element.innerText = message;
  element.style.color = "green";
}


// =====================
// ✅ VALIDATION PRODUIT (SUBMIT)
// =====================
function validateProduit(event) {
  // reset erreurs
  document.getElementById('error-nomProduit').innerText = '';
  document.getElementById('error-type').innerText = '';
  document.getElementById('error-idProduit').innerText = '';
  document.getElementById('error-idStand').innerText = '';
  document.getElementById('error-prixProduit').innerText = '';
  document.getElementById('error-quantiteStock').innerText = '';
  document.getElementById('error-stock').innerText = '';

  let nom = document.getElementById('nomProduit').value.trim();
  let type = document.getElementById('type').value.trim();
  let idProd = document.getElementById('idProduit').value.trim();
  let idStand = document.getElementById('idStand').value.trim();
  let prix = document.getElementById('prixProduit').value;
  let quant = document.getElementById('quantiteStock').value;
  let stock = document.querySelector('input[name="stock"]:checked');

  if (nom.length < 3) {
    showError(document.getElementById('error-nomProduit'), "Nom trop court");
    event.preventDefault();
    return false;
  }

  if (type === "" || type.length < 3) {
    showError(document.getElementById('error-type'), "Type obligatoire");
    event.preventDefault();
    return false;
  }

  // ID Produit optionnel si vide (auto-incrément), mais valide si rempli
  if (idProd !== "" && (!/^\d+$/.test(idProd) || parseInt(idProd) <= 0)) {
    showError(document.getElementById('error-idProduit'), "ID Produit invalide");
    event.preventDefault();
    return false;
  }

  if (!/^\d+$/.test(idStand) || parseInt(idStand) <= 0) {
    showError(document.getElementById('error-idStand'), "ID Stand invalide");
    event.preventDefault();
    return false;
  }

  if (prix === "" || parseFloat(prix) <= 0) {
    showError(document.getElementById('error-prixProduit'), "Prix invalide");
    event.preventDefault();
    return false;
  }

  if (quant === "" || parseInt(quant) < 0) {
    showError(document.getElementById('error-quantiteStock'), "Quantité invalide");
    event.preventDefault();
    return false;
  }

  if (!stock) {
    showError(document.getElementById('error-stock'), "Choisir stock");
    event.preventDefault();
    return false;
  }

  return true;
}


// =====================
// ✅ VALIDATION STAND (SUBMIT)
// =====================
function validateStand(event) {
  event.preventDefault();

  document.getElementById('error-idStand').innerText = '';
  document.getElementById('error-idParcours').innerText = '';
  document.getElementById('error-nomStand').innerText = '';
  document.getElementById('error-position').innerText = '';
  document.getElementById('error-description').innerText = '';

  let idStand = document.getElementById('idStand').value.trim();
  let idParc = document.getElementById('idParcours').value.trim();
  let nom = document.getElementById('nomStand').value.trim();
  let pos = document.getElementById('position').value.trim();
  let desc = document.getElementById('description').value.trim();

  if (!/^\d+$/.test(idStand) || parseInt(idStand) <= 0) {
    showError(document.getElementById('error-idStand'), "ID Stand invalide");
    return false;
  }

  if (!/^\d+$/.test(idParc) || parseInt(idParc) <= 0) {
    showError(document.getElementById('error-idParcours'), "ID Parcours invalide");
    return false;
  }

  if (nom.length < 3) {
    showError(document.getElementById('error-nomStand'), "Nom trop court");
    return false;
  }

  if (pos === "") {
    showError(document.getElementById('error-position'), "Position obligatoire");
    return false;
  }

  if (desc === "") {
    showError(document.getElementById('error-description'), "Description obligatoire");
    return false;
  }

  alert("✅ Stand validé !");
  return true;
}


// =====================
// 🔥 VALIDATION DYNAMIQUE (KEYUP)
// =====================
document.addEventListener("DOMContentLoaded", function () {

  // ===== PRODUIT =====
  let nomProduit = document.getElementById("nomProduit");
  let type = document.getElementById("type");
  let idProduit = document.getElementById("idProduit");
  let idStand = document.getElementById("idStand");
  let prix = document.getElementById("prixProduit");
  let quantite = document.getElementById("quantiteStock");

  // Nom Produit
  if (nomProduit) {
    nomProduit.addEventListener("keyup", function () {
      let e = document.getElementById("error-nomProduit");
      if (nomProduit.value.trim().length < 3) {
        showError(e, "Min 3 caractères");
      } else {
        showSuccess(e, "Nom valide ✔");
      }
    });
  }

  // Type
  if (type) {
    type.addEventListener("keyup", function () {
      let e = document.getElementById("error-type");
      if (type.value.trim() === "") {
        showError(e, "Type obligatoire");
      } else {
        showSuccess(e, "Type valide ✔");
      }
    });
  }

  // ID Produit
  if (idProduit) {
    idProduit.addEventListener("keyup", function () {
      let e = document.getElementById("error-idProduit");
      if (!/^\d+$/.test(idProduit.value)) {
        showError(e, "ID invalide");
      } else {
        showSuccess(e, "ID valide ✔");
      }
    });
  }

  // ID Stand
  if (idStand) {
    idStand.addEventListener("keyup", function () {
      let e = document.getElementById("error-idStand");
      if (!/^\d+$/.test(idStand.value)) {
        showError(e, "ID invalide");
      } else {
        showSuccess(e, "ID valide ✔");
      }
    });
  }

  // Prix
  if (prix) {
    prix.addEventListener("keyup", function () {
      let e = document.getElementById("error-prixProduit");
      if (prix.value === "" || parseFloat(prix.value) <= 0) {
        showError(e, "Prix invalide");
      } else {
        showSuccess(e, "Prix valide ✔");
      }
    });
  }

  // Quantité
  if (quantite) {
    quantite.addEventListener("keyup", function () {
      let e = document.getElementById("error-quantiteStock");
      if (quantite.value === "" || parseInt(quantite.value) < 0) {
        showError(e, "Quantité invalide");
      } else {
        showSuccess(e, "Quantité valide ✔");
      }
    });
  }

});

// =====================
// 🔍 RECHERCHE AJAX PRODUIT
// =====================
function searchProduitAjax() {
    let searchVal = document.getElementById('searchProduitInput').value.trim();
    if (searchVal === "") {
        alert("Veuillez entrer une valeur à rechercher.");
        return;
    }
    
    let formData = new FormData();
    formData.append('searchVal', searchVal);
    
    fetch('searchProduit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('searchModalContainer').innerHTML = html;
    })
    .catch(error => {
        console.error('Erreur de recherche:', error);
        alert('Erreur lors de la recherche.');
    });
}