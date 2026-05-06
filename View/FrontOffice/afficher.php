<?php
require_once "../../Controller/InscriptionController.php";


$controller = new InscriptionController();
$liste = $controller->getAll();


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="inscription.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FrontOffice</title>

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

        .btn-danger {
            background:#e76f51;
        }

        .btn-secondary {
            background:var(--teal);
        }

    </style>
</head>

<body>

<div class="page-shell">

    <!-- ✅ NAVBAR IDENTIQUE -->
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
            <a href="afficher.php">Afficher</a>
            <a href="stats.php">Statistiques</a> <!-- ✅ ajouté -->
        </nav>

        <div class="user-badge">Organisateur</div>
    </header>

    <!-- ✅ CONTENU -->
    <main class="content-grid">

        <section class="card card-form">

            <div class="card-title">
                <h1>Gestion des Inscriptions</h1>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Mode paiement</th>
                            <th>Circuit</th>
                            <th>Nb personnes</th>
                            <th>Date paiement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach($liste as $row) { ?>
                        <tr>
                            <td><?php echo $row['mode_de_paiement']; ?></td>

                            <td>
                            <?php 
                            if ($row['id_parcours'] == 1) echo "10 km";
                            elseif ($row['id_parcours'] == 2) echo "21 km";
                            else echo "42 km";
                            ?>
                            </td>

                            <td><?php echo $row['nb_personnes']; ?></td>
                            <td><?php echo $row['date_paiement']; ?></td>

                            <td>
                                <a href="../FrontOffice/voirDossard.php?id_inscription=<?php echo $row['id_inscription']; ?>" 
                                class="btn btn-secondary">
                                Voir
                                </a>

                                <a href="../../Controller/InscriptionController.php?delete=<?php echo $row['id_inscription']; ?>&redirect=front_afficher" 
                                class="btn btn-danger"
                                onclick="return confirm('Supprimer ?')">
                                Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

        </section>

    </main>

</div>

</body>
</html>