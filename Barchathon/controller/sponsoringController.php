<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../model/sponsoring.php');

class sponsoringController {
    public function deleteSponsoring($id) {
        $sql = "DELETE FROM sponsoring WHERE idSponsoring = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addSponsoring(Sponsoring $sponsoring) {
        $sql = "INSERT INTO sponsoring (nomSponsoring, dateDebut, dateFin, montant, etat, idSponsor, idMarathon) VALUES (:nomSponsoring, :dateDebut, :dateFin, :montant, :etat, :idSponsor, :idMarathon)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nomSponsoring' => $sponsoring->getNomSponsoring(),
                'dateDebut' => $sponsoring->getDateDebut(),
                'dateFin' => $sponsoring->getDateFin(),
                'montant' => $sponsoring->getMontant(),
                'etat' => $sponsoring->getEtat(),
                'idSponsor' => $sponsoring->getIdSponsor(),
                'idMarathon' => $sponsoring->getIdMarathon()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateSponsoring(Sponsoring $sponsoring, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE sponsoring SET 
                    nomSponsoring = :nomSponsoring,
                    dateDebut = :dateDebut,
                    dateFin = :dateFin,
                    montant = :montant,
                    etat = :etat,
                    idSponsor = :idSponsor,
                    idMarathon = :idMarathon
                WHERE idSponsoring = :id'
            );
            $query->execute([
                'id' => $id,
                'nomSponsoring' => $sponsoring->getNomSponsoring(),
                'dateDebut' => $sponsoring->getDateDebut(),
                'dateFin' => $sponsoring->getDateFin(),
                'montant' => $sponsoring->getMontant(),
                'etat' => $sponsoring->getEtat(),
                'idSponsor' => $sponsoring->getIdSponsor(),
                'idMarathon' => $sponsoring->getIdMarathon()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showSponsoring($id) {
        $sql = "SELECT * FROM sponsoring WHERE idSponsoring = $id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute();
            $sponsoring = $query->fetch();
            return $sponsoring;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function showMarathon($id) {
        $sql = "SELECT * FROM marathon WHERE id_marathon = ?";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute([$id]);
            $marathon = $query->fetch(PDO::FETCH_ASSOC);
            return $marathon;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function afficherSponsoring($showActions = true, $deleteOnly = false, $viewOnly = false) {
        $sql = "SELECT * FROM sponsoring";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            while ($row = $query->fetch()) {
                echo "<tr>";
                echo "<td>{$row['idSponsoring']}</td>";
                echo "<td>{$row['nomSponsoring']}</td>";
                echo "<td>{$row['dateDebut']}</td>";
                echo "<td>{$row['dateFin']}</td>";
                echo "<td>{$row['montant']} €</td>";
                echo "<td>{$row['etat']}</td>";
                echo "<td>{$row['idSponsor']}</td>";
                echo "<td>{$row['idMarathon']}</td>";
                if ($showActions || $deleteOnly || $viewOnly) {
                    echo "<td>";
                    if ($showActions) {
                        echo "<div class='row-actions'>";
                        echo '<a href="deleteSponsoring.php?id=' . $row['idSponsoring'] . '" class="icon-btn icon-delete delete-sponsoring-btn" data-sponsoring-name="' . htmlspecialchars($row['nomSponsoring'], ENT_QUOTES) . '" title="Supprimer"></a>';
                        echo "<a href='modifySponsoring.php?id={$row['idSponsoring']}' class='icon-btn icon-edit' title='Modifier'></a>";
                        echo "</div>";
                    } elseif ($deleteOnly) {
                        echo "<div class='row-actions'>";
                        echo '<a href="deleteSponsoring.php?id=' . $row['idSponsoring'] . '" class="icon-btn icon-delete delete-sponsoring-btn" data-sponsoring-name="' . htmlspecialchars($row['nomSponsoring'], ENT_QUOTES) . '" title="Supprimer"></a>';
                        echo "</div>";
                    } elseif ($viewOnly) {
                        echo "<a href='#sponsoring' class='btn btn-secondary'>Voir sponsoring</a>";
                        echo "<a href='#fournitures' class='btn btn-secondary'>Voir fourniture</a>";
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function afficherSponsoringSponsor($idSponsor, $showActions = true) {
        $sql = "SELECT * FROM sponsoring WHERE idSponsor = :idSponsor";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':idSponsor', $idSponsor);
            $query->execute();
            
            while ($row = $query->fetch()) {
                echo "<tr>";
                echo "<td>{$row['idSponsoring']}</td>";
                echo "<td>{$row['nomSponsoring']}</td>";
                echo "<td>{$row['dateDebut']}</td>";
                echo "<td>{$row['dateFin']}</td>";
                echo "<td>{$row['montant']} €</td>";
                echo "<td>{$row['etat']}</td>";
                echo "<td>{$row['idSponsor']}</td>";
                echo "<td>{$row['idMarathon']}</td>";
                if ($showActions) {
                    echo "<td>";
                    echo "<div class='row-actions'>";
                    echo '<a href="deleteSponsoring.php?id=' . $row['idSponsoring'] . '" class="icon-btn icon-delete delete-sponsoring-btn" data-sponsoring-name="' . htmlspecialchars($row['nomSponsoring'], ENT_QUOTES) . '" title="Supprimer"></a>';
                    echo "<a href='modifySponsoring.php?id={$row['idSponsoring']}' class='icon-btn icon-edit' title='Modifier'></a>";
                    echo "</div>";
                    echo "</td>";
                }
                echo "</tr>";
            }
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function afficherMarathon($chooseButton = false, $extraParams = '', $formParams = '') {
        $sql = "SELECT * FROM marathon";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            while ($row = $query->fetch()) {
                echo "<tr>";
                echo "<td>{$row['id_marathon']}</td>";
                echo "<td>{$row['nom_marathon']}</td>";
                echo "<td>{$row['region_marathon']}</td>";
                echo "<td>{$row['date_marathon']}</td>";
                if ($chooseButton) {
                    echo "<td><a href='addSponsoring.php?idMarathon={$row['id_marathon']}{$extraParams}{$formParams}' class='btn btn-primary'>Choisir</a></td>";}
                }
                echo "</tr>";
            }
         catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}
?>