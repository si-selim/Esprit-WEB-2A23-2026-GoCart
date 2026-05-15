// Fonction de validation pour le formulaire Produit
function validateProduit(event) {

  // Effacer les erreurs précédentes
  document.getElementById('error-nomProduit').innerText = '';
  document.getElementById('error-type').innerText = '';
  document.getElementById('error-idProduit').innerText = '';
  document.getElementById('error-idStand').innerText = '';
  document.getElementById('error-prixProduit').innerText = '';
  document.getElementById('error-quantiteStock').innerText = '';
  document.getElementById('error-stock').innerText = '';

  // Récupération des champs
  let nom = document.getElementById('nomProduit').value.trim();
  let type = document.getElementById('type').value.trim();
  let idProd = document.getElementById('idProduit').value.trim();
  let idStand = document.getElementById('idStand').value.trim();
  let prix = document.getElementById('prixProduit').value;
  let quant = document.getElementById('quantiteStock').value;
  let stock = document.querySelector('input[name="stock"]:checked');

  // Vérifications
  if (nom === '' || nom.length <= 4) {
    document.getElementById('error-nomProduit').innerText = 'Le nom du produit doit contenir plus de 4 caractères.';
    event.preventDefault();
    return false;
  }

  if (type === '') {
    document.getElementById('error-type').innerText = 'Le type est obligatoire.';
    event.preventDefault();
    return false;
  }

  let idProdNum = parseInt(idProd, 10);
  if (idProd === '' || isNaN(idProdNum) || idProdNum <= 0) {
    document.getElementById('error-idProduit').innerText = 'L\'ID Produit doit être un entier positif non nul.';
    event.preventDefault();
    return false;
  }

  let idStandNum = parseInt(idStand, 10);
  if (idStand === '' || isNaN(idStandNum) || idStandNum <= 0) {
    document.getElementById('error-idStand').innerText = 'L\'ID Stand doit être un entier positif non nul.';
    event.preventDefault();
    return false;
  }

  if (prix === '' || parseFloat(prix) <= 0) {
    document.getElementById('error-prixProduit').innerText = 'Le prix doit être un nombre positif.';
    event.preventDefault();
    return false;
  }

  if (quant === '' || parseInt(quant, 10) < 0) {
    document.getElementById('error-quantiteStock').innerText = 'La quantité doit être positive ou zéro.';
    event.preventDefault();
    return false;
  }

  if (!stock) {
    document.getElementById('error-stock').innerText = 'Veuillez sélectionner un statut de stock.';
    event.preventDefault();
    return false;
  }

  return true;
}

// Fonction de validation pour le formulaire Stand
function validateStand(event) {

  // Permettre la soumission directe si on clique sur Rechercher, Supprimer, ou Modifier
  if (event.submitter && (
      event.submitter.classList.contains('search') || 
      event.submitter.classList.contains('delete') ||
      event.submitter.classList.contains('edit')
  )) {
      return true;
  }

  document.getElementById('error-idStand').innerText = '';
  document.getElementById('error-idParcours').innerText = '';
  document.getElementById('error-nomStand').innerText = '';
  document.getElementById('error-position').innerText = '';
  document.getElementById('error-description').innerText = '';

  let idStand = document.getElementById('idStand') ? document.getElementById('idStand').value.trim() : '1';
  let idParc = document.getElementById('idParcours').value.trim();
  let nom = document.getElementById('nomStand').value.trim();
  let pos = document.getElementById('position').value.trim();
  let desc = document.getElementById('description').value.trim();

  let idStandNum = parseInt(idStand, 10);
  if (idStand === '' || isNaN(idStandNum) || idStandNum <= 0) {
    document.getElementById('error-idStand').innerText = 'L\'ID Stand doit être un entier positif non nul.';
    event.preventDefault();
    return false;
  }

  let idParcNum = parseInt(idParc, 10);
  if (idParc === '' || isNaN(idParcNum) || idParcNum <= 0) {
    document.getElementById('error-idParcours').innerText = 'L\'ID Parcours doit être un entier positif non nul.';
    event.preventDefault();
    return false;
  }

  if (nom === '' || nom.length <= 4) {
    document.getElementById('error-nomStand').innerText = 'Le nom du stand doit contenir plus de 4 caractères.';
    event.preventDefault();
    return false;
  }

  if (pos === '') {
    document.getElementById('error-position').innerText = 'La position est obligatoire.';
    event.preventDefault();
    return false;
  }

  if (desc === '') {
    document.getElementById('error-description').innerText = 'La description est obligatoire.';
    event.preventDefault();
    return false;
  }

  return true;
}

// Tri statique (message uniquement)
function sortData() {
  let sortField = document.getElementById('sortField').value;
  let sortOrder = document.getElementById('sortOrder').value;
  alert('Tri par ' + sortField + ' en ordre ' + (sortOrder === 'asc' ? 'croissant' : 'décroissant') + '.');
}

document.addEventListener('DOMContentLoaded', function () {
  let sortBtn = document.querySelector('.btn.sort');
  if (sortBtn) {
    sortBtn.addEventListener('click', sortData);
  }
})
document.addEventListener("DOMContentLoaded", function () {

  // ===== PRODUIT =====
  let nomProduit = document.getElementById("nomProduit");
  let type = document.getElementById("type");
  let idProduit = document.getElementById("idProduit");
  let idStand = document.getElementById("idStand");
  let prix = document.getElementById("prixProduit");
  let quantite = document.getElementById("quantiteStock");

  if (nomProduit) {
    nomProduit.addEventListener("keyup", function () {
      let value = nomProduit.value.trim();
      let error = document.getElementById("error-nomProduit");

      if (value.length < 3) {
        error.innerText = "Au moins 3 caractères requis";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (type) {
    type.addEventListener("keyup", function () {
      let error = document.getElementById("error-type");

      if (type.value.trim() === "") {
        error.innerText = "Type obligatoire";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (idProduit) {
    idProduit.addEventListener("keyup", function () {
      let error = document.getElementById("error-idProduit");

      if (!/^\d+$/.test(idProduit.value)) {
        error.innerText = "ID invalide (chiffres seulement)";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (prix) {
    prix.addEventListener("keyup", function () {
      let error = document.getElementById("error-prixProduit");

      if (prix.value === "" || parseFloat(prix.value) <= 0) {
        error.innerText = "Prix invalide";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (quantite) {
    quantite.addEventListener("keyup", function () {
      let error = document.getElementById("error-quantiteStock");

      if (quantite.value === "" || parseInt(quantite.value) < 0) {
        error.innerText = "Quantité invalide";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  // ===== STAND =====
  let nomStand = document.getElementById("nomStand");
  let idParcours = document.getElementById("idParcours");
  let position = document.getElementById("position");
  let description = document.getElementById("description");

  if (nomStand) {
    nomStand.addEventListener("keyup", function () {
      let value = nomStand.value.trim();
      let error = document.getElementById("error-nomStand");

      // 🎯 TON EXIGENCE (Title)
      if (value.length < 3) {
        error.innerText = "Le titre doit contenir au moins 3 caractères";
        error.style.color = "red";
      } else {
        error.innerText = "Titre valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (idStand) {
    idStand.addEventListener("keyup", function () {
      let error = document.getElementById("error-idStand");

      if (!/^\d+$/.test(idStand.value)) {
        error.innerText = "ID Stand invalide";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (idParcours) {
    idParcours.addEventListener("keyup", function () {
      let error = document.getElementById("error-idParcours");

      if (!/^\d+$/.test(idParcours.value)) {
        error.innerText = "ID Parcours invalide";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (position) {
    position.addEventListener("keyup", function () {
      let error = document.getElementById("error-position");

      if (position.value.trim() === "") {
        error.innerText = "Position obligatoire";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

  if (description) {
    description.addEventListener("keyup", function () {
      let error = document.getElementById("error-description");

      if (description.value.trim().length < 5) {
        error.innerText = "Description trop courte";
        error.style.color = "red";
      } else {
        error.innerText = "Valide ✔";
        error.style.color = "green";
      }
    });
  }

});

// =====================
// 🔍 RECHERCHE AJAX STAND
// =====================
function searchStandAjax() {
    let searchVal = document.getElementById('searchStandInput').value.trim();
    if (searchVal === "") {
        alert("Veuillez entrer une valeur à rechercher.");
        return;
    }
    
    let formData = new FormData();
    formData.append('searchVal', searchVal);
    
    fetch('searchStand.php', {
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
