<?php
require_once "../../Controller/InscriptionController.php";
require_once "../../Controller/DossardController.php";

$id = $_GET['id_inscription'] ?? 0;

$inscriptionController = new InscriptionController();
$dossardController = new DossardController();


$inscription = $inscriptionController->getById($id);


$dossards = $dossardController->getByInscription($id);
?>

<h2>📋 Infos inscription</h2>

<p><b>ID :</b> <?= $id ?></p>
<p><b>Nombre personnes :</b> <?= $inscription['nb_personnes'] ?></p>
<p><b>Mode paiement :</b> <?= $inscription['mode_de_paiement'] ?></p>
<p><b>Date paiement :</b> <?= $inscription['date_paiement'] ?></p>
<p><b>Circuit :</b> <?= $inscription['id_parcours'] ?></p>

<hr>

<h2>🎽 Dossards</h2>

<table border="1">
<tr>
    <th>Nom</th>
    <th>Numéro</th>
    <th>Taille</th>
    <th>Couleur</th>
</tr>

<?php foreach($dossards as $d) { ?>
<tr>
    <td><?= $d['nom'] ?></td>
    <td><?= $d['numero'] ?></td>
    <td><?= $d['taille'] ?></td>
    <td><?= $d['couleur'] ?></td>
</tr>
<?php } ?>

</table>