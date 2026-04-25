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
        $sql = "INSERT INTO `user` (nom_complet, nom_user, mot_de_passe, email, role, age, poids, taille, tel, pays, ville, occupation, profile_picture, sexe)
                VALUES (:nom_complet, :nom_user, :mot_de_passe, :email, :role, :age, :poids, :taille, :tel, :pays, :ville, :occupation, :profile_picture, :sexe)";
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
                'profile_picture' => $u->getProfilePicture(),
                'sexe' => $u->getSexe()
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
            occupation=:occupation,
            sexe=:sexe
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
                'sexe' => $u->getSexe()
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
            profile_picture=:profile_picture,
            sexe=:sexe
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
                'profile_picture' => $u->getProfilePicture(),
                'sexe' => $u->getSexe()
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
                $fullPath = __DIR__ . '/../View/FrontOffice/images/uploads/' . $pic;
                if (file_exists($fullPath)) unlink($fullPath);
            }

            $req = $db->prepare("DELETE FROM `user` WHERE id_user = :id");
            $req->bindValue(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function ajouterUserAvecVerification(User $u, $token) {
        $sql = "INSERT INTO `user` (nom_complet, nom_user, mot_de_passe, email, role, age, poids, taille, tel, pays, ville, occupation, profile_picture, sexe, verified, verification_token)
                VALUES (:nom_complet, :nom_user, :mot_de_passe, :email, :role, :age, :poids, :taille, :tel, :pays, :ville, :occupation, :profile_picture, :sexe, 0, :token)";
        $db = config::getConnexion();
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
            'profile_picture' => $u->getProfilePicture(),
            'sexe' => $u->getSexe(),
            'token' => $token
        ]);
    }

    public function findByVerificationToken($token) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM `user` WHERE verification_token = :t LIMIT 1");
        $stmt->execute(['t' => $token]);
        return $stmt->fetch();
    }

    public function saveFaceDescriptor($id, $json) {
        $db = config::getConnexion();
        $stmt = $db->prepare("UPDATE `user` SET face_descriptor = :d WHERE id_user = :id");
        $stmt->execute(['d' => $json, 'id' => $id]);
    }

    public function getFaceDescriptor($identifier) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT id_user, nom_user, nom_complet, role, email, profile_picture, status, verified, face_descriptor FROM `user` WHERE nom_user = :u OR email = :e LIMIT 1");
        $stmt->execute(['u' => $identifier, 'e' => $identifier]);
        return $stmt->fetch();
    }

    public function markVerified($id) {
        $db = config::getConnexion();
        $stmt = $db->prepare("UPDATE `user` SET verified = 1, verification_token = NULL WHERE id_user = :id");
        $stmt->execute(['id' => $id]);
    }

    public function bloquerUser($id) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("UPDATE `user` SET status = 'banned' WHERE id_user = :id");
            $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function debloquerUser($id) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("UPDATE `user` SET status = 'active' WHERE id_user = :id");
            $stmt->execute(['id' => $id]);
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
        $uploadDir = __DIR__ . '/../View/FrontOffice/images/uploads';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            return $filename;
        }

        return false;
    }

    public function deleteOldPicture($filename) {
        if (!$filename) return;
        $uploadDir = __DIR__ . '/../View/FrontOffice/images/uploads';
        $fullPath = $uploadDir . '/' . $filename;
        if (file_exists($fullPath)) unlink($fullPath);
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

    public function findByEmail($email) {
        $db = config::getConnexion();
        $q = $db->prepare("SELECT * FROM `user` WHERE email = :e LIMIT 1");
        $q->execute(['e' => $email]);
        return $q->fetch();
    }

    public function findByGoogleId($gid) {
        $db = config::getConnexion();
        $q = $db->prepare("SELECT * FROM `user` WHERE google_id = :g LIMIT 1");
        $q->execute(['g' => $gid]);
        return $q->fetch();
    }

    public function linkGoogleId($userId, $googleId) {
        $db = config::getConnexion();
        $q = $db->prepare("UPDATE `user` SET google_id = :g WHERE id_user = :id");
        $q->execute(['g' => $googleId, 'id' => $userId]);
    }

    /**
     * Create a user whose only credential is a verified Google identity.
     * Generates a unique nom_user from the email prefix, stores a random
     * password hash (so classic login is effectively disabled until the
     * user sets one), and marks the account verified since Google has
     * already confirmed the email.
     */
    public function createGoogleUser($email, $fullName, $googleId, $picture = null) {
        $db = config::getConnexion();

        // Derive a unique nom_user from the email local part
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', strstr($email, '@', true));
        if ($base === '' || $base === false) $base = 'user';
        $base = substr($base, 0, 40);
        $nomUser = $base;
        $i = 1;
        $checkStmt = $db->prepare("SELECT 1 FROM `user` WHERE nom_user = :u LIMIT 1");
        while (true) {
            $checkStmt->execute(['u' => $nomUser]);
            if (!$checkStmt->fetch()) break;
            $i++;
            $nomUser = $base . $i;
        }

        $randomPwd = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);

        $sql = "INSERT INTO `user` (nom_complet, nom_user, mot_de_passe, email, role, profile_picture, verified, google_id)
                VALUES (:nc, :nu, :mp, :em, 'participant', :pp, 1, :gid)";
        $q = $db->prepare($sql);
        $q->execute([
            'nc'  => $fullName ?: $nomUser,
            'nu'  => $nomUser,
            'mp'  => $randomPwd,
            'em'  => $email,
            'pp'  => $picture,
            'gid' => $googleId,
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Create a password reset token for the given email, valid for 1 hour.
     * Returns the token string on success, or null if no user matches.
     */
    public function createPasswordResetToken($email) {
        $db = config::getConnexion();
        $user = $this->findByEmail($email);
        if (!$user) return null;

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1h
        $stmt = $db->prepare("UPDATE `user` SET reset_token = :t, reset_token_expires = :e WHERE id_user = :id");
        $stmt->execute(['t' => $token, 'e' => $expires, 'id' => $user['id_user']]);
        return $token;
    }

    /**
     * Look up an unexpired reset token. Returns the user row or false.
     */
    public function findByResetToken($token) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM `user` WHERE reset_token = :t AND reset_token_expires IS NOT NULL AND reset_token_expires > NOW() LIMIT 1");
        $stmt->execute(['t' => $token]);
        return $stmt->fetch();
    }

    /**
     * Update password hash and clear the reset token in one go.
     */
    public function resetPassword($userId, $newHash) {
        $db = config::getConnexion();
        $stmt = $db->prepare("UPDATE `user` SET mot_de_passe = :h, reset_token = NULL, reset_token_expires = NULL WHERE id_user = :id");
        $stmt->execute(['h' => $newHash, 'id' => $userId]);
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
