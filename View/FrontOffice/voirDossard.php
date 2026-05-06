<<?php
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";

$id = $_GET['id_inscription'] ?? 0;

$dossardController = new DossardController();
$inscriptionController = new InscriptionController();


$data = $inscriptionController->getById($id);

$nb = 0;
if ($data) {
    $nb = is_array($data) ? ($data['nb_personnes'] ?? 0) : 0;
}

$liste = $dossardController->getByInscription($id);


$total = max($nb, count($liste));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="inscription.css">
<meta charset="UTF-8">
<title>Voir Dossards</title>

<style>
body {
    margin:0;
    font-family:Segoe UI;
    background:#f4fbfb;
}

.page-shell {
    width:90%;
    margin:auto;
}

.topbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px;
    background:white;
    border-radius:10px;
    margin:20px 0;
}

.nav-links a {
    margin:0 10px;
    text-decoration:none;
    color:#0b2032;
    font-weight:bold;
}

.card {
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

.table-wrapper table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#0b2032;
    color:white;
    padding:12px;
}

td {
    padding:12px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

.btn {
    padding:8px 12px;
    background:#0f766e;
    color:white;
    border-radius:8px;
    text-decoration:none;
}

.btn-warning {
    background:orange;
}
</style>
</head>

<body>

<div class="page-shell">

    
    <header class="topbar">
        <div class="brand">
            <span class="brand-mark">BT</span>
            <div>
                <strong>BarchaThon</strong>
                <small>Front Office</small>
            </div>
        </div>

        <nav class="nav-links">
            <a href="inscription.php">Inscription</a>
            <a href="dossard.php">Dossard</a>
            <a href="stats.php">Statistiques</a>
        </nav>

        <div class="user-badge">Participant Demo</div>
    </header>

    
    <main class="content-grid">

        <section class="card card-form">

            <div class="card-title">
                <div>
                    <h1>Dossards de l'inscription #<?php echo $id; ?></h1>
                    <p>Liste des dossards associés</p>
                </div>
            </div>

            <?php if($nb == 0) { ?>
                <p style="color:red;">Aucune inscription trouvée</p>
            <?php } else { ?>

            <?php if(count($liste) == 0) { ?>
                <p style="color:red;">Aucun dossard trouvé</p>
            <?php } ?>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Nom</th>
                            <th>Numéro</th>
                            <th>Taille</th>
                            <th>Couleur</th>
                            <th>QR Code</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php for($i = 0; $i < $total; $i++) { ?>
                    <tr>

                        <td>
                        <?php if(isset($liste[$i])) { ?>
                            ✔
                        <?php } else { ?>
                            <a href="dossard.php?id_inscription=<?php echo $id; ?>" class="btn btn-secondary btn-small">
                                Compléter
                            </a>
                        <?php } ?>
                        </td>

                        <td><?php echo $liste[$i]['nom'] ?? "—"; ?></td>

                        <td><?php echo $liste[$i]['numero'] ?? ($i + 1); ?></td>

                        <td><?php echo $liste[$i]['taille'] ?? ""; ?></td>

                        <td>
                            <?php if(isset($liste[$i]['couleur'])) { ?>
                                <div style="
                                    width:20px;
                                    height:20px;
                                    margin:auto;
                                    border-radius:5px;
                                    background:<?php echo $liste[$i]['couleur']; ?>">
                                </div>
                                <small><?php echo $liste[$i]['couleur']; ?></small>
                            <?php } ?>
                        </td>

                        <td>
                            <?php if(isset($liste[$i]['qr_code'])) { ?>
                                <img src="../../qr/<?php echo $liste[$i]['qr_code']; ?>" width="80">
                            <?php } else { ?>
                                Pas de QR
                            <?php } ?>
                        </td>

                    </tr>
                    <?php } ?>

                    </tbody>
                </table>

            </div>

            <?php } ?>

            <br>

            <div class="action-buttons">
                <a href="export_pdf.php?id_inscription=<?php echo $id; ?>" 
                   class="btn btn-primary">
                    Exporter PDF
                </a>

                <a href="inscription.php" class="btn btn-outlined">
                    Retour
                </a>
            </div>

        </section>

    </main>

</div>

</body>
</html>