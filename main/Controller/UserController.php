<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/User.php';

class UserController {

    public function afficherUsers() {
        $sql = "SELECT * FROM `user` ORDER BY id_user DESC";
        $db = config::getConnexion();

        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function ajouterUser(User $u) {
        $sql = "INSERT INTO `user` (nom_complet, nom_user, mot_de_passe, email, role, age, poids, taille, tel, pays, ville, occupation, profile_picture)
                VALUES (:nom_complet, :nom_user, :mot_de_passe, :email, :role, :age, :poids, :taille, :tel, :pays, :ville, :occupation, :profile_picture)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'nom_complet' => $u->getNomComplet(),
                'nom_user' => $u->getNomUser(),
                'mot_de_passe' => $u->getMotDePasse(),
                'email' => $u->getEmail(),
                'role' => $u->getRole(),
                'age' => $u->getAge(),
                'poids' => $u->getPoids(),
                'taille' => $u->getTaille(),
                'tel' => $u->getTel(),
                'pays' => $u->getPays(),
                'ville' => $u->getVille(),
                'occupation' => $u->getOccupation(),
                'profile_picture' => $u->getProfilePicture()
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function modifierUser(User $u, $id) {
        $sql = "UPDATE `user` SET
            nom_complet=:nom_complet,
            nom_user=:nom_user,
            email=:email,
            role=:role,
            age=:age,
            poids=:poids,
            taille=:taille,
            tel=:tel,
            pays=:pays,
            ville=:ville,
            occupation=:occupation
        WHERE id_user=:id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom_complet' => $u->getNomComplet(),
                'nom_user' => $u->getNomUser(),
                'email' => $u->getEmail(),
                'role' => $u->getRole(),
                'age' => $u->getAge(),
                'poids' => $u->getPoids(),
                'taille' => $u->getTaille(),
                'tel' => $u->getTel(),
                'pays' => $u->getPays(),
                'ville' => $u->getVille(),
                'occupation' => $u->getOccupation()
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function modifierUserAvecPhoto(User $u, $id) {
        $sql = "UPDATE `user` SET
            nom_complet=:nom_complet,
            nom_user=:nom_user,
            email=:email,
            role=:role,
            age=:age,
            poids=:poids,
            taille=:taille,
            tel=:tel,
            pays=:pays,
            ville=:ville,
            occupation=:occupation,
            profile_picture=:profile_picture
        WHERE id_user=:id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom_complet' => $u->getNomComplet(),
                'nom_user' => $u->getNomUser(),
                'email' => $u->getEmail(),
                'role' => $u->getRole(),
                'age' => $u->getAge(),
                'poids' => $u->getPoids(),
                'taille' => $u->getTaille(),
                'tel' => $u->getTel(),
                'pays' => $u->getPays(),
                'ville' => $u->getVille(),
                'occupation' => $u->getOccupation(),
                'profile_picture' => $u->getProfilePicture()
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function supprimerUser($id) {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare("SELECT profile_picture FROM `user` WHERE id_user = :id");
            $stmt->execute(['id' => $id]);
            $pic = $stmt->fetchColumn();

            if ($pic) {
                $picPath = realpath(__DIR__ . '/../uploads');
                if ($picPath) {
                    $fullPath = $picPath . '/' . $pic;
                    if (file_exists($fullPath)) unlink($fullPath);
                }
            }

            $req = $db->prepare("DELETE FROM `user` WHERE id_user = :id");
            $req->bindValue(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function showUser($id) {
        $sql = "SELECT * FROM `user` WHERE id_user = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function rechercherUsers($search = '', $role = '', $pays = '', $sort = '', $dir = 'DESC') {
        $db = config::getConnexion();
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(nom_complet LIKE ? OR nom_user LIKE ? OR email LIKE ? OR pays LIKE ?)";
            $s = "%$search%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if ($role !== '') {
            $where[] = "role = ?";
            $params[] = $role;
        }

        if ($pays !== '') {
            $where[] = "pays = ?";
            $params[] = $pays;
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $allowedSorts = ['nom_complet', 'age', 'poids', 'taille', 'ville'];
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $orderSQL = in_array($sort, $allowedSorts) ? "$sort $dir, id_user DESC" : "id_user DESC";

        try {
            $stmt = $db->prepare("SELECT * FROM `user` $whereSQL ORDER BY $orderSQL");
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function countUsers($search = '', $role = '', $pays = '') {
        $db = config::getConnexion();
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(nom_complet LIKE ? OR nom_user LIKE ? OR email LIKE ? OR pays LIKE ?)";
            $s = "%$search%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if ($role !== '') {
            $where[] = "role = ?";
            $params[] = $role;
        }

        if ($pays !== '') {
            $where[] = "pays = ?";
            $params[] = $pays;
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM `user` $whereSQL");
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getPays() {
        $sql = "SELECT DISTINCT pays FROM `user` WHERE pays IS NOT NULL AND pays != '' ORDER BY pays";
        $db = config::getConnexion();

        try {
            return $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function saveProfilePicture($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            return false;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }

        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $filename = uniqid('pp_', true) . '.' . $ext[$mime];
        $uploadDir = realpath(__DIR__ . '/../uploads');
        if (!$uploadDir) $uploadDir = __DIR__ . '/../uploads';

        if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            return $filename;
        }

        return null;
    }

    public function deleteOldPicture($filename) {
        if (!$filename) return;
        $uploadDir = realpath(__DIR__ . '/../uploads');
        if ($uploadDir) {
            $fullPath = $uploadDir . '/' . $filename;
            if (file_exists($fullPath)) unlink($fullPath);
        }
    }

    public function getPasswordHash($id) {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare("SELECT mot_de_passe FROM `user` WHERE id_user = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function changePassword($id, $newHash) {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare("UPDATE `user` SET mot_de_passe = :hash WHERE id_user = :id");
            $stmt->execute(['hash' => $newHash, 'id' => $id]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function findByUsername($username) {
        $sql = "SELECT * FROM `user` WHERE nom_user = :username";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['username' => $username]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function statsUsers() {
        $db = config::getConnexion();

        try {
            $total = (int)$db->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
            $participants = (int)$db->query("SELECT COUNT(*) FROM `user` WHERE role='participant'")->fetchColumn();
            $organisateurs = (int)$db->query("SELECT COUNT(*) FROM `user` WHERE role='organisateur'")->fetchColumn();
            $admins = (int)$db->query("SELECT COUNT(*) FROM `user` WHERE role='admin'")->fetchColumn();
            $withEmail = (int)$db->query("SELECT COUNT(*) FROM `user` WHERE email IS NOT NULL AND email != ''")->fetchColumn();
            $withPhone = (int)$db->query("SELECT COUNT(*) FROM `user` WHERE tel IS NOT NULL AND tel != ''")->fetchColumn();
            $withoutOccupation = (int)$db->query("SELECT COUNT(*) FROM `user` WHERE occupation IS NULL OR occupation = ''")->fetchColumn();
            $avgAge = $db->query("SELECT ROUND(AVG(age)) FROM `user` WHERE age IS NOT NULL")->fetchColumn() ?: 0;
            $topCountry = $db->query("SELECT pays FROM `user` WHERE pays IS NOT NULL AND pays != '' GROUP BY pays ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn() ?: '-';
            $topOccupation = $db->query("SELECT occupation FROM `user` WHERE occupation IS NOT NULL AND occupation != '' GROUP BY occupation ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn() ?: '-';

            $completionRate = $total > 0 ? round(($withEmail / $total) * 100) : 0;
            $pBar = $total > 0 ? round(($participants / $total) * 100) : 0;
            $oBar = $total > 0 ? round(($organisateurs / $total) * 100) : 0;
            $aBar = $total > 0 ? round(($admins / $total) * 100) : 0;

            return [
                'total' => $total,
                'participants' => $participants,
                'organisateurs' => $organisateurs,
                'admins' => $admins,
                'withEmail' => $withEmail,
                'withPhone' => $withPhone,
                'withoutOccupation' => $withoutOccupation,
                'completionRate' => $completionRate,
                'avgAge' => $avgAge,
                'topCountry' => $topCountry,
                'topOccupation' => $topOccupation,
                'pBar' => $pBar,
                'oBar' => $oBar,
                'aBar' => $aBar
            ];
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
