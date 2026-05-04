<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stands marathon</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <img src="logo.png" alt="BarchaThon Logo">
                <span>BarchaThon</span>
            </div>
            <nav class="nav">
                <a href="../BackOffice/tab_stand.php">Accueil</a>
                <a href="stand.php">Catalogue</a>
                <a href="#">S’inscrire</a>
                <a href="#" class="btn-login">Se connecter</a>
            </nav>
        </div>
    </header>
    <div class="stand">

        <div class="top">
            <h1>Détails stand</h1>
            <div>
                <a href="stand.php">Retour details</a>
                <a href="#">Catalogue</a>
            </div>
        </div>

        <!-- CATEGORIE 1 -->
        <h2 class="category-title">Stand-Semi Marathon de Sousse Corniche</h2>
        <div class="grid-stands">
            <div class="card">
                <div class="card-head">
                    <h3>🏪 Stand Nutrition</h3>
                    <div class="stand-position"><span class="pin-icon">📍</span> Position : Nutrition</div>
                </div>
                <div class="card-body">
                    <p class="stand-desc">Collations sportives et nutrition rapide.</p>
                    <div class="card-buttons">
                        <a class="btn-catalogue" href="produitC2.php">🛒 Voir catalogue produits</a>
                        <a class="btn-manipuler" href="crud-stand.php">⚙️ Manipuler Stand</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const idParcours = 3; // Semi Marathon de Sousse Corniche
            fetch(`getStandsByParcours.php?id_parcours=${idParcours}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        const grid = document.querySelector('.grid-stands');
                        data.data.forEach(stand => {
                            const card = document.createElement('div');
                            card.className = 'card';
                            const nom = stand.nom_stand || 'Stand';
                            const position = stand.position || 'Non spécifiée';
                            const desc = stand.description || '';

                            card.innerHTML = `
                        <div class="card-head">
                            <h3>🏪 ${nom}</h3>
                            <div class="stand-position"><span class="pin-icon">📍</span> Position : ${position}</div>
                        </div>
                        <div class="card-body">
                            <p class="stand-desc">${desc}</p>
                            <div class="card-buttons">
                                <a class="btn-catalogue" href="produits.php?id_stand=${stand.ID_stand}">🛒 Voir catalogue produits</a>
                                <a class="btn-manipuler" href="crud-stand.php">⚙️ Manipuler Stand</a>
                            </div>
                        </div>
                    `;
                            grid.appendChild(card);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors de la récupération des stands:', error));
        });
    </script>
</body>

</html>
