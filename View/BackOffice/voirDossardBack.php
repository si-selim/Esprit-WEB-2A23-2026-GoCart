<?php
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";

$id = $_GET['id_inscription'] ?? 0;

$dossardController = new DossardController();
$inscriptionController = new InscriptionController();

$data = $inscriptionController->getById($id);

$nb = $data['nb_personnes'] ?? 0;
$liste = $dossardController->getByInscription($id);

$total = max($nb, count($liste));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dossards - BackOffice</title>

<style>
:root {
    --teal:#0f766e;
    --dark:#0b2032;
    --bg:#f4fbfb;
    --card:#ffffff;
}

body {
    margin:0;
    font-family:Segoe UI;
    background:var(--bg);
}

.layout {
    display:grid;
    grid-template-columns:260px 1fr;
    min-height:100vh;
}

/* SIDEBAR */
.sidebar {
    background:linear-gradient(180deg, #0b2032, #12314a);
    color:white;
    padding:20px;
}

.brand {
    margin-bottom:30px;
}

.brand-badge {
    width:50px;
    height:50px;
    border-radius:15px;
    background:var(--teal);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
}

.side-link {
    display:block;
    color:white;
    padding:12px;
    margin:8px 0;
    text-decoration:none;
    border-radius:10px;
    background:rgba(255,255,255,0.05);
}

.side-link:hover {
    background:var(--teal);
}

/* CONTENT */
.content {
    padding:25px;
}

.card {
    background:var(--card);
    padding:20px;
    border-radius:15px;
    margin-bottom:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

table {
    width:100%;
    border-collapse:collapse;
}

th, td {
    padding:10px;
    border-bottom:1px solid #ddd;
    text-align:center;
}

th {
    background:var(--dark);
    color:white;
}

.btn {
    padding:6px 10px;
    border-radius:8px;
    text-decoration:none;
    color:white;
}

.btn-secondary {
    background:var(--teal);
}

.btn-warning {
    background:orange;
}

.btn-back {
    background:#555;
}
</style>
</head>

<body>

<div class="layout">

    <!-- ✅ SIDEBAR IDENTIQUE -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-badge">BT</div>
            <strong>BarchaThon</strong><br>
            <small>Admin Dashboard</small>
        </div>

        <nav>
            <a class="side-link" href="afficher.php">Inscriptions</a>
        </nav>
    </aside>

    <!-- ✅ CONTENT -->
    <main class="content">

        <h1>Dossards - Inscription #<?php echo $id; ?></h1>

        <div class="card">

        <?php if($nb == 0) { ?>
            <p style="color:red;">Aucune inscription trouvée</p>
        <?php } else { ?>

        <?php if(count($liste) == 0) { ?>
            <p style="color:red;">Aucun dossard trouvé</p>
        <?php } ?>

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
                    <a href="dossard.php?id_inscription=<?php echo $id; ?>" class="btn btn-warning">
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

        <?php } ?>

        <br>

        <a href="../FrontOffice/export_pdf.php?id_inscription=<?php echo $id; ?>" class="btn btn-secondary">
    Exporter PDF
</a>

        <a href="afficher.php" class="btn btn-back">
            Retour
        </a>

        </div>

    </main>

</div>

</body>
</html>