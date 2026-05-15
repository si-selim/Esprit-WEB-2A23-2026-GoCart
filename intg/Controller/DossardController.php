<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../Model/Dossard.php";
require_once __DIR__ . "/../lib/phpqrcode/qrlib.php";


function isValidHex($color) {
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
}

class DossardController {

    private function generateQRFile($id, $dataQR) {
        $dir = __DIR__ . "/../qr/";
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $fileName = "qr_" . $id . ".png";
        $filePath = $dir . $fileName;

        // Try GD-based phpqrcode first
        if (function_exists('imagecreate')) {
            try {
                \QRcode::png($dataQR, $filePath, QR_ECLEVEL_L, 12);
                if (file_exists($filePath) && filesize($filePath) > 0) return $fileName;
            } catch (\Throwable $e) { /* fall through to remote */ }
        }

        // Fallback: external QR API (no GD required)
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='
             . urlencode($dataQR);
        $png = @file_get_contents($url);
        if ($png === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $png = curl_exec($ch);
            curl_close($ch);
        }
        if ($png) {
            file_put_contents($filePath, $png);
            return $fileName;
        }

        return null;
    }

    public function add(Dossard $dossard) {

        $db = Config::getConnexion();


        $sql = "INSERT INTO dossard (nom, numero, taille, couleur, id_inscription)
                VALUES (:nom, :numero, :taille, :couleur, :id_inscription)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'nom' => $dossard->getNom(),
            'numero' => $dossard->getNumero(),
            'taille' => $dossard->getTaille(),
            'couleur' => $dossard->getCouleur(),
            'id_inscription' => $dossard->getIdInscription()
        ]);


        $id = $db->lastInsertId();

        $dataQR =
"===== DOSSARD =====\n".
"ID Dossard: ".$id."\n".
"ID Inscription: ".$dossard->getIdInscription()."\n".
"Nom: ".$dossard->getNom()."\n".
"Numero: ".$dossard->getNumero()."\n".
"Taille: ".$dossard->getTaille()."\n".
"Couleur: ".$dossard->getCouleur()."\n".
"====================";

        $fileName = $this->generateQRFile($id, $dataQR);

        if ($fileName) {
            $stmt = $db->prepare("UPDATE dossard SET qr_code=? WHERE id_dossard=?");
            $stmt->execute([$fileName, $id]);
        }
    }

    public function delete($id) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM dossard WHERE id_dossard=?");
        $stmt->execute([$id]);
    }

    public function deleteByInscription($id_inscription) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM dossard WHERE id_inscription=?");
        $stmt->execute([$id_inscription]);
    }

    public function getByInscription($id_inscription) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM dossard WHERE id_inscription=? ORDER BY numero ASC");
        $stmt->execute([$id_inscription]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $db = Config::getConnexion();
        return $db->query("SELECT * FROM dossard")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastNumero() {
        $db = Config::getConnexion();
        $row = $db->query("SELECT MAX(numero) as max_num FROM dossard")
                  ->fetch(PDO::FETCH_ASSOC);

        return $row['max_num'] ?? 0;
    }

    public function updateSizeAndColor($id_dossard, $taille, $couleur) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE dossard SET taille = ?, couleur = ? WHERE id_dossard = ?");
        $stmt->execute([$taille, $couleur, $id_dossard]);
    }

    public function regenerateQR(array $row) {
        $id = $row['id_dossard'];

        $dataQR =
"===== DOSSARD =====\n".
"ID Dossard: ".$id."\n".
"ID Inscription: ".$row['id_inscription']."\n".
"Nom: ".$row['nom']."\n".
"Numero: ".$row['numero']."\n".
"Taille: ".$row['taille']."\n".
"Couleur: ".$row['couleur']."\n".
"====================";

        $fileName = $this->generateQRFile($id, $dataQR);
        if (!$fileName) return null;

        $db   = Config::getConnexion();
        $stmt = $db->prepare("UPDATE dossard SET qr_code=? WHERE id_dossard=?");
        $stmt->execute([$fileName, $id]);

        return $fileName;
    }
}




