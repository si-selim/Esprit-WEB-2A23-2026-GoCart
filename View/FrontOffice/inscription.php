<!DOCTYPE html>
<?php
require_once "../../Controller/InscriptionController.php";

$controller = new InscriptionController();
$liste = $controller->getAll();
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Marathon</title>
    <link rel="stylesheet" href="inscription.css">
    <style>
    main.content-grid {
        flex-direction: column !important;
        align-items: center !important;
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
                    <h1>Inscription - Marathon Carthage</h1>
                    <p>Remplissez le formulaire ci-dessous pour valider votre inscription.</p>
                </div>
            </div>

            <?php if(isset($_GET['success'])) { ?>
                <?php if($_GET['success'] == 'add') { ?>
                    <p style="color:green;">Inscription ajoutée avec succès</p>
                <?php } elseif($_GET['success'] == 'update') { ?>
                    <p style="color:blue;">Inscription modifiée avec succès</p>
                <?php } ?>
            <?php } ?>

            <form method="post" action="../../Controller/process_inscription.php">

                <input type="hidden" id="id_inscription" name="id_inscription">

                <div class="form-grid">

                    <div class="field-group">
                        <label>Nombre de personnes</label>
                        <input id="nb_personnes" type="number" name="nb_personnes" placeholder="1">
                        <small id="error-nb_personnes"></small>
                    </div>

                    <div class="field-group">
                        <label>Circuit</label>
                        <select id="circuit" name="circuit">
                            <option value="">Choisir un circuit</option>
                            <option value="1">10 km</option>
                            <option value="2">21 km</option>
                            <option value="3">42 km</option>
                        </select>
                        <small id="error-circuit"></small>
                    </div>

                    <div class="field-group">
                        <label>Mode de paiement</label>
                        <select id="mode_paiement" name="mode_paiement">
                            <option value="">Choisir un mode</option>
                            <option value="cash">Espèces</option>
                            <option value="card">Carte bancaire</option>
                            <option value="transfer">Virement</option>
                        </select>
                        <small id="error-mode_paiement"></small>
                    </div>

                    <div class="field-group">
                        <label>Date de paiement</label>
                        <input type="date" id="date_paiement" name="date_paiement"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>

                </div>

                <div class="add-button-row">
                    <button class="btn btn-primary" type="submit" name="action" value="add">
                        Ajouter inscription
                    </button>
                </div>

                <div class="field-group">
                    <label>Prix total</label>
                    <input id="prix_total" type="text" readonly placeholder="0 TND">
                </div>

                <div class="action-buttons">
                    <button class="btn btn-outlined" type="submit" name="action" value="update">
                        Modifier
                    </button>
                    <a href="export_all_inscriptions.php" class="btn btn-primary">
                        Export PDF
                    </a>
                </div>

            </form>

        </section>

        <div class="recent-card">

            <div class="recent-header">
                <h2>Inscriptions récentes</h2>
            </div>

            <div class="search-row">
                <input type="number" id="search_id" placeholder="Chercher par ID">
                <select id="filter_statut">
                    <option value="">Tous les statuts</option>
                    <option value="paid">Payé</option>
                    <option value="unpaid">Non payé</option>
                </select>
                <select id="filter_circuit">
                    <option value="">Tous les circuits</option>
                    <option value="1">10 km</option>
                    <option value="2">21 km</option>
                    <option value="3">42 km</option>
                </select>
                <select id="filter_nb">
                    <option value="">Nb personnes</option>
                    <option value="1">1</option>
                    <option value="2-4">2 – 4</option>
                    <option value="5+">5+</option>
                </select>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Date inscription</th>
                        <th>Mode paiement</th>
                        <th>Circuit</th>
                        <th>Nb personnes</th>
                        <th>Date paiement</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody id="table-body">

                    <?php if(empty($liste)) { ?>
                        <tr>
                            <td colspan="7" style="text-align:center;color:red;">
                                Aucune inscription trouvée
                            </td>
                        </tr>
                    <?php } else { ?>

                        <?php foreach($liste as $row) { ?>
                            <tr data-id="<?php echo $row['id_inscription']; ?>"
                                data-statut="<?php echo $row['statut_paiement']; ?>"
                                data-circuit="<?php echo $row['id_parcours']; ?>"
                                data-nb="<?php echo $row['nb_personnes']; ?>">

                                <td><?php echo $row['date_inscription']; ?></td>
                                <td><?php echo $row['mode_de_paiement']; ?></td>
                                <td><?php echo $row['id_parcours']; ?></td>
                                <td><?php echo $row['nb_personnes']; ?></td>
                                <td><?php echo date("Y-m-d", strtotime($row['date_paiement'])); ?></td>

                                <td>
                                    <?php if($row['statut_paiement'] == "paid") { ?>
                                        <span style="color:green;font-weight:bold;">Payé</span>
                                    <?php } else { ?>
                                        <span style="color:red;font-weight:bold;">Non payé</span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <div class="table-actions">

                                        <button class="btn btn-secondary btn-small"
                                            onclick="fillForm(
                                                <?php echo $row['id_inscription']; ?>,
                                                <?php echo $row['nb_personnes']; ?>,
                                                '<?php echo $row['mode_de_paiement']; ?>',
                                                '<?php echo date("Y-m-d", strtotime($row['date_paiement'])); ?>',
                                                <?php echo $row['id_parcours']; ?>
                                            )">
                                            Sélectionner
                                        </button>

                                        <a href="../FrontOffice/voirDossard.php?id_inscription=<?php echo $row['id_inscription']; ?>"
                                           class="btn btn-secondary btn-small">
                                            Voir
                                        </a>

                                        <a href="../../Controller/InscriptionController.php?delete=<?php echo $row['id_inscription']; ?>&redirect=front_inscription"
                                           class="btn btn-danger"
                                           onclick="return confirm('Supprimer ?')">
                                            Supprimer
                                        </a>

                                        
                                        <?php if($row['statut_paiement'] != "paid") { ?>
                                            <button class="btn btn-primary btn-small btn-pay-trigger"
                                                onclick="openPayModal(
                                                    <?php echo $row['id_inscription']; ?>,
                                                    <?php echo $row['nb_personnes']; ?>,
                                                    <?php echo $row['id_parcours']; ?>
                                                )">
                                                Payer
                                            </button>
                                        <?php } else { ?>
                                            <span style="color:green;font-weight:bold;">OK</span>
                                        <?php } ?>

                                    </div>
                                </td>

                            </tr>
                        <?php } ?>

                    <?php } ?>

                    </tbody>
                </table>
            </div>

        </div>

    </main>

</div>

<script src="inscription.js?v=<?php echo time(); ?>"></script>

<script src="inscription_ai.js?v=<?php echo time(); ?>"></script>
</body>
</html>