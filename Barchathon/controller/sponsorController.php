
<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../model/sponsor.php');

class sponsorController {
    public function deleteSponsor($id) {
        $sql = "DELETE FROM sponsor WHERE idSponsor = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addSponsor(Sponsor $sponsor) {
        $sql = "INSERT INTO sponsor (nom, type, adresse, contact, email, pageWeb, idUser) VALUES (:nom, :type, :adresse, :contact, :email, :pageWeb, :idUser)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $sponsor->getNom(),
                'type' => $sponsor->getType(),
                'adresse' => $sponsor->getAdresse(),
                'contact' => $sponsor->getContact(),
                'email' => $sponsor->getEmail(),
                'pageWeb' => $sponsor->getPageWeb(),
                'idUser' =>  $sponsor->getIdUser()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateSponsor(Sponsor $sponsor, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE sponsor SET 
                    nom = :nom,
                    type = :type,
                    adresse = :adresse,
                    contact = :contact,
                    email = :email,
                    pageWeb = :pageWeb,
                    idUser = :idUser
                WHERE idSponsor = :id'
            );
            $query->execute([
                'id' => $id,
                'nom' => $sponsor->getNom(),
                'type' => $sponsor->getType(),
                'adresse' => $sponsor->getAdresse(),
                'contact' => $sponsor->getContact(),
                'email' => $sponsor->getEmail(),
                'pageWeb' => $sponsor->getPageWeb(),
                'idUser' => $sponsor->getIdUser()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    public function showSponsor($id) {
        $sql = "SELECT * FROM sponsor WHERE idSponsor = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $query->execute();
            $sponsor = $query->fetch();
            return $sponsor;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getSponsorDistributionByType(): array {
        $sql = "SELECT type, COUNT(*) AS total FROM sponsor GROUP BY type";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            $results = [];
            while ($row = $query->fetch()) {
                $results[] = [
                    'label' => $row['type'] ?? 'Inconnu',
                    'count' => (int) $row['total']
                ];
            }
            return $results;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getSponsorsBySponsoringCount(int $limit = 10): array {
        $sql = "SELECT s.nom AS label, COUNT(sp.idSponsoring) AS total
                FROM sponsor s
                LEFT JOIN sponsoring sp ON s.idSponsor = sp.idSponsor
                GROUP BY s.idSponsor
                ORDER BY total DESC
                LIMIT :limit";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);
            $query->execute();
            $results = [];
            while ($row = $query->fetch()) {
                $results[] = [
                    'label' => $row['label'] ?? 'Inconnu',
                    'count' => (int) $row['total']
                ];
            }
            return $results;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function afficherSponsor($showActions = true, $deleteOnly = false, $viewOnly = false, $chooseButton = false, $extraParams = '', $formParams = '') {
        $sql = "SELECT * FROM sponsor";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            while ($row = $query->fetch()) {
                echo "<tr>";
                if ($chooseButton) {
                    echo "<td>{$row['idSponsor']}</td>";
                    echo "<td>{$row['nom']}</td>";
                    echo "<td>{$row['type']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['contact']}</td>";
                    echo "<td><a href='addSponsoring.php?idSponsor={$row['idSponsor']}{$extraParams}{$formParams}' class='btn btn-primary'>Choisir</a></td>";
                } else {
                    if ($deleteOnly) {
                        echo "<td>{$row['idUser']}</td>";
                        echo "<td>{$row['idSponsor']}</td>";
                        echo "<td>{$row['nom']}</td>";
                        echo "<td>{$row['type']}</td>";
                        echo "<td >{$row['adresse']}</td>";
                        echo "<td>{$row['contact']}</td>";
                        echo "<td>{$row['email']}</td>";
                        $pageWeb = $row['pageWeb'];
                        $href = (strpos($pageWeb, 'http') === 0 ? $pageWeb : 'https://' . $pageWeb);
                        echo "<td><a href='$href' target='_blank' rel='noopener noreferrer'>{$row['nom']}</a></td>";
                    } else {
                        echo "<td>{$row['idSponsor']}</td>";
                        echo "<td>{$row['nom']}</td>";
                        echo "<td>{$row['type']}</td>";
                        echo "<td >{$row['adresse']}</td>";
                        echo "<td>{$row['contact']}</td>";
                        echo "<td>{$row['email']}</td>";
                        $pageWeb = $row['pageWeb'];
                        $href = (strpos($pageWeb, 'http') === 0 ? $pageWeb : 'https://' . $pageWeb);
                        echo "<td><a href='$href' target='_blank' rel='noopener noreferrer'>{$row['nom']}</a></td>";
                    }
                    if ($showActions || $deleteOnly || $viewOnly) {
                        echo "<td>";
                        if ($showActions) {
                            echo "<button class='btn btn-secondary view-sponsoring-btn' data-sponsor-id='{$row['idSponsor']}'>Voir sponsoring</button>";
                            echo "<a href='#fournitures' class='btn btn-secondary'>Voir fourniture</a>";
                            echo "<div class='row-actions'>";
                            echo '<a href="deleteSponsor.php?id=' . $row['idSponsor'] . '" class="icon-btn icon-delete delete-sponsor-btn" data-sponsor-name="' . htmlspecialchars($row['nom'], ENT_QUOTES) . '" title="Supprimer"></a>';
                            echo "<a href='modifySponsor.php?id={$row['idSponsor']}' class='icon-btn icon-edit' title='Modifier'></a>";
                            echo "</div>";
                        } elseif ($deleteOnly) {
                            echo "<div class='row-actions'>";
                            echo '<a href="deleteSponsor.php?id=' . $row['idSponsor'] . '" class="icon-btn icon-delete delete-sponsor-btn" data-sponsor-name="' . htmlspecialchars($row['nom'], ENT_QUOTES) . '" title="Supprimer"></a>';
                            echo "</div>";
                        } elseif ($viewOnly) {
                            echo "<button class='btn btn-secondary view-sponsoring-btn' data-sponsor-id='{$row['idSponsor']}'>Voir sponsoring</button>";
                            echo "<a href='#fournitures' class='btn btn-secondary'>Voir fourniture</a>";
                        }
                        echo "</td>";
                    }
                }
                echo "</tr>";
            }
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}
?>