<?php
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";

$id = $_GET['id_inscription'] ?? 0;

$inscriptionController = new InscriptionController();
$dossardController = new DossardController();


$data = $inscriptionController->getById($id);
$nbFromInscription = $data['nb_personnes'] ?? 1;


$dossardsExistants = $dossardController->getByInscription($id);


$nbExistants = count($dossardsExistants);


$total = max($nbFromInscription, $nbExistants);


$nom_global = $dossardsExistants[0]['nom'] ?? "";


$nextNumero = $dossardController->getLastNumero();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dossards</title>
<link rel="stylesheet" href="inscription.css">
<link rel="stylesheet" href="dossard.css">
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
                    <h1>Dossards #<?php echo $id; ?></h1>
                    <p>Gestion des dossards liés à cette inscription</p>
                </div>
            </div>

            <form method="post" action="../../Controller/DossardController.php">

                <input type="hidden" name="id_inscription" value="<?php echo $id; ?>">

                <div class="form-grid">
                    <div class="field-group">
                        <label>Nom / Équipe</label>
                        <input type="text" name="nom_global" id="nom_global"
                               value="<?php echo $nom_global; ?>">
                        <small id="error-nom_global"></small>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Taille</th>
                                <th>Couleur</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php for ($i = 0; $i < $total; $i++) {

                            if (isset($dossardsExistants[$i])) {
                                $numero = $dossardsExistants[$i]['numero'];
                            } else {
                                $nextNumero++;
                                $numero = $nextNumero;
                            }
                        ?>
                        <tr>

                            <td>
                                <?php echo $numero; ?>
                                <input type="hidden" name="numero[]" value="<?php echo $numero; ?>">
                            </td>

                            <td>
                                <select name="taille[]" class="taille">
                                    <option value="">--Choisir--</option>
                                    <option value="S" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='S') echo "selected"; ?>>S</option>
                                    <option value="M" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='M') echo "selected"; ?>>M</option>
                                    <option value="L" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='L') echo "selected"; ?>>L</option>
                                    <option value="XL" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='XL') echo "selected"; ?>>XL</option>
                                </select>
                                <small class="error-taille"></small>
                            </td>

                            <td>
                                <input type="color" name="couleur[]" class="couleur"
                                       value="<?php echo $dossardsExistants[$i]['couleur'] ?? '#000000'; ?>">
                                <small class="error-couleur"></small>
                            </td>

                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="add-button-row">
                    <button type="submit" class="btn btn-primary">
                        Enregistrer
                    </button>
                </div>

            </form>

        </section>

    </main>

</div>

<script src="dossard.js"></script>
</body>
</html>