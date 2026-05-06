document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    if (!form) return;

    const nom = document.getElementById("nom_global");

    
    function validateNom() {
        if (!nom) return true;

        let error = document.getElementById("error-nom_global");

        if (nom.value.trim() === "") {
            if (error) {
                error.style.color = "red";
                error.innerText = "Nom obligatoire";
            }
            return false;
        } else {
            if (error) {
                error.style.color = "green";
                error.innerText = "OK";
            }
            return true;
        }
    }

    
    function validateCouleur() {
    let valid = true;

    document.querySelectorAll(".couleur").forEach(c => {

        let error = c.parentElement.querySelector(".error-couleur");
        let value = c.value.trim();

        
        if (value === "") {
            error.style.color = "red";
            error.innerText = "Couleur obligatoire";
            valid = false;
            return;
        }

        
        const regex = /^#[0-9A-Fa-f]{6}$/;

        if (!regex.test(value)) {
            error.style.color = "red";
            error.innerText = "Format invalide (ex: #FF0000)";
            valid = false;
        } else {
            error.style.color = "green";
            error.innerText = "OK";
        }
    });

    return valid;
}

    
    function validateTaille() {
        let valid = true;

        document.querySelectorAll(".taille").forEach(t => {

            let error = t.parentElement.querySelector(".error-taille");

            if (t.value === "") {
                if (error) {
                    error.style.color = "red";
                    error.innerText = "Taille obligatoire";
                }
                valid = false;
            } else {
                if (error) {
                    error.style.color = "green";
                    error.innerText = "OK";
                }
            }
        });

        return valid;
    }

    
    if (nom) nom.addEventListener("input", validateNom);

    document.querySelectorAll(".couleur").forEach(c => {
        c.addEventListener("input", validateCouleur);
    });

    document.querySelectorAll(".taille").forEach(t => {
        t.addEventListener("change", validateTaille);
    });

    
    form.addEventListener("submit", function (e) {

        let okNom = validateNom();
        let okCouleur = validateCouleur();
        let okTaille = validateTaille();

        if (!okNom || !okCouleur || !okTaille) {
            e.preventDefault();
            alert(" Vérifie tous les champs !");
        } else {
            alert(" Dossards enregistrés !");
        }
    });

});