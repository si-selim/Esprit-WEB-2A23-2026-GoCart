<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/Objectif.php');

class ObjectifController {
    public function listObjectifs() {
        $sql = "SELECT * FROM objectif";
        $db = config::getConnexion();
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error listObjectifs: ' . $e->getMessage());
            return [];
        }
    }

    public function listActiveObjectifs() {
        $sql = "SELECT * FROM objectif WHERE etat = 1";
        $db = config::getConnexion();
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error listActiveObjectifs: ' . $e->getMessage());
            return [];
        }
    }

    public function showObjectif($id) {
        $sql = "SELECT * FROM objectif WHERE id_objectif = :id";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error showObjectif: ' . $e->getMessage());
            return false;
        }
    }

    public function addObjectif(Objectif $obj) {
        $sql = "INSERT INTO objectif (titre, description, type_objectif, target_value, recompense, description_recompense, etat) 
                VALUES (:titre, :description, :type_objectif, :target_value, :recompense, :description_recompense, :etat)";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'titre' => $obj->getTitre(),
                'description' => $obj->getDescription(),
                'type_objectif' => $obj->getTypeObjectif(),
                'target_value' => $obj->getTargetValue(),
                'recompense' => $obj->getRecompense(),
                'description_recompense' => $obj->getDescriptionRecompense(),
                'etat' => $obj->getEtat()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            error_log('Error addObjectif: ' . $e->getMessage());
            return false;
        }
    }

    public function updateObjectif(Objectif $obj, $id) {
        $sql = "UPDATE objectif SET titre = :titre, description = :description, type_objectif = :type_objectif, 
                target_value = :target_value, recompense = :recompense, description_recompense = :description_recompense, etat = :etat 
                WHERE id_objectif = :id";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'titre' => $obj->getTitre(),
                'description' => $obj->getDescription(),
                'type_objectif' => $obj->getTypeObjectif(),
                'target_value' => $obj->getTargetValue(),
                'recompense' => $obj->getRecompense(),
                'description_recompense' => $obj->getDescriptionRecompense(),
                'etat' => $obj->getEtat()
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Error updateObjectif: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteObjectif($id) {
        $sql = "DELETE FROM objectif WHERE id_objectif = :id";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            error_log('Error deleteObjectif: ' . $e->getMessage());
            return false;
        }
    }

    public function toggleEtat($id, $etat) {
        $sql = "UPDATE objectif SET etat = :etat WHERE id_objectif = :id";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['etat' => $etat, 'id' => $id]);
            return true;
        } catch (Exception $e) {
            error_log('Error toggleEtat: ' . $e->getMessage());
            return false;
        }
    }

    // Logic for tracking user progress
    public function getUserProgress($userId, $type_objectif) {
        $db = config::getConnexion();
        if ($type_objectif === 'commandes') {
            $sql = "SELECT COUNT(*) as count FROM commande WHERE idutilisateur = :id AND statut IN ('confirmé', 'validée', 'validé')";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$res['count'];
        } elseif ($type_objectif === 'marathons') {
            $sql = "SELECT nbre_inscription FROM user WHERE id_user = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$res['nbre_inscription'];
        } elseif ($type_objectif === 'logins') {
            $sql = "SELECT consecutive_logins FROM user WHERE id_user = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$res['consecutive_logins'];
        }
        return 0;
    }

    public function getUserClaims($userId) {
        $sql = "SELECT id_objectif FROM user_objectif_claim WHERE id_user = :id";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $claims = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $claims[] = $row['id_objectif'];
            }
            return $claims;
        } catch (Exception $e) {
            error_log('Error getUserClaims: ' . $e->getMessage());
            return [];
        }
    }

    public function claimRecompense($userId, $objectifId) {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();
            
            // Insert claim
            $sql = "INSERT INTO user_objectif_claim (id_user, id_objectif, statut) VALUES (:id_user, :id_objectif, 'reclamé')";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id_user' => $userId, 'id_objectif' => $objectifId]);
            
            // Check if reward contains a percentage discount
            $objectif = $this->showObjectif($objectifId);
            if ($objectif && preg_match('/(\d+)\s*%/', $objectif['recompense'], $matches)) {
                $discount = (int)$matches[1];
                $sqlUser = "UPDATE user SET pending_discount = pending_discount + :discount WHERE id_user = :userId";
                $stmtUser = $db->prepare($sqlUser);
                $stmtUser->execute(['discount' => $discount, 'userId' => $userId]);
            }
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error claimRecompense: ' . $e->getMessage());
            return false;
        }
    }
}
?>
