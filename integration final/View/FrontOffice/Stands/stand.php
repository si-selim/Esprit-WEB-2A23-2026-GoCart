<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stands Marathon</title>
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
                <a href="accueil.php">Accueil</a>
                <a href="stand.php">Stands</a>
                <a href="../BackOffice/dashboard.php?tab=stands">Admin Stands</a>
                <a href="logout.php" class="btn-login">Déconnexion</a>
            </nav>
        </div>
    </header>

    <div class="stand">

        <h1>Stands </h1>

        <!-- CATEGORIE 1: tunis -->
        <h2 class="category-title">Marathon de Tunis Medina</h2>
        <div class="grid-stands">
            <a class="card" href="stand_marathon_de_tunis.php">Stand Marathon de Tunis Medina</a>
        </div>

        <!-- CATEGORIE 2: Sport -->
        <h2 class="category-title">Semi Marathon de Sousse Corniche</h2>
        <div class="grid-stands">
            <a class="card" href="Semi Marathon de Sousse Corniche.php">Stand Semi Marathon de Sousse Corniche</a>
        </div>

        <!-- CATEGORIE 3: Tech -->
        <h2 class="category-title">Trail de Zaghouan</h2>
        <div class="grid-stands">
            <a class="card" href="Stand Trail de Zaghouan.php">Stand Trail de Zaghouan</a>

        </div>

        <!-- CATEGORIE 4: Art -->
        <h2 class="category-title">Marathon International de Djerba</h2>
        <div class="grid-stands">
            <a class="card" href="Marathon International de Djerba.php"> stand Marathon International de Djerba</a>

        </div>

    </div>

</body>

</html>
