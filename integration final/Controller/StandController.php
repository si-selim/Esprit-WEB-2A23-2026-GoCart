<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Stand.php';

class StandController {

    // =====================
    // LIST (READ ALL)
    // =====================
    public function listStands($sort = null, $order = 'ASC') {
        $sql = "SELECT * FROM stand";
        
        $allowedSorts = ['ID_parcours', 'ID_stand', 'nom_stand', 'position', 'description'];
        if ($sort && in_array($sort, $allowedSorts)) {
            $order = ($order === 'DESC') ? 'DESC' : 'ASC';
            $sql .= " ORDER BY $sort $order";
        }

        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // =====================
    // GET BY PARCOURS
    // =====================
    public function getStandsByParcours($idParcours) {
        $sql = "SELECT * FROM stand WHERE ID_parcours = :ID_parcours";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':ID_parcours', (int) $idParcours, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('getStandsByParcours error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Alias for backward compatibility with existing project
     */
    public function afficherStandsParParcours($id_parcours) {
        return $this->getStandsByParcours($id_parcours);
    }

    // =====================
    // ADD
    // =====================
    public function addStand($stand) {
        $sql = "INSERT INTO stand (ID_parcours, nom_stand, position, description) 
                VALUES (:ID_parcours, :nom_stand, :position, :description)";
        $db = config::getConnexion();

        $query = $db->prepare($sql);

        try {
            $query->execute([
                'ID_parcours'  => $stand->getIdParcours(),
                'nom_stand'    => $stand->getNomStand(),
                'position'     => $stand->getPosition(),
                'description'  => $stand->getDescription()
            ]);
            return true;
        } catch (Exception $e) {
            error_log('addStand error: ' . $e->getMessage());
            return false;
        }
    }

    // =====================
    // DELETE
    // =====================
    public function deleteStand($idStand) {
        $sql = "DELETE FROM stand WHERE ID_stand = :ID_stand";
        $db = config::getConnexion();

        $req = $db->prepare($sql);
        $req->bindValue(':ID_stand', (int) $idStand, PDO::PARAM_INT);

        try {
            $req->execute();
            return true;
        } catch (Exception $e) {
            error_log('deleteStand error: ' . $e->getMessage());
            return false;
        }
    }

    // =====================
    // GET BY ID (prepared statement)
    // =====================
    public function getStand($idStand) {
        $sql = "SELECT * FROM stand WHERE ID_stand = :ID_stand";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':ID_stand', (int) $idStand, PDO::PARAM_INT);
            $query->execute();

            return $query->fetch();

        } catch (Exception $e) {
            error_log('getStand error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias for backward compatibility
     */
    public function showStand($id) {
        return $this->getStand($id);
    }

    // =====================
    // SEARCH BY ID OR NAME (Plural for AJAX)
    // =====================
    public function searchStands($search) {
        $db = config::getConnexion();
        $sql = "SELECT * FROM stand WHERE nom_stand LIKE :s OR position LIKE :s OR description LIKE :s";
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':s', '%' . $search . '%');
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('searchStands error: ' . $e->getMessage());
            return [];
        }
    }

    // =====================
    // SEARCH BY ID OR NAME
    // =====================
    public function searchStand($search) {
        $db = config::getConnexion();

        try {
            if (ctype_digit(trim($search))) {
                $query = $db->prepare("SELECT * FROM stand WHERE ID_stand = :id");
                $query->bindValue(':id', (int) $search, PDO::PARAM_INT);
                $query->execute();
                $result = $query->fetch();
                if ($result) return $result;
            }

            $query = $db->prepare("SELECT * FROM stand WHERE nom_stand LIKE :nom LIMIT 1");
            $query->bindValue(':nom', '%' . $search . '%');
            $query->execute();
            return $query->fetch();

        } catch (Exception $e) {
            error_log('searchStand error: ' . $e->getMessage());
            return null;
        }
    }

    // =====================
    // UPDATE
    // =====================
    public function updateStand($stand, $idStand) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE stand SET 
                    ID_parcours = :ID_parcours,
                    nom_stand = :nom_stand,
                    position = :position,
                    description = :description
                WHERE ID_stand = :ID_stand'
            );
            $query->execute([
                'ID_stand'     => (int) $idStand,
                'ID_parcours'  => $stand->getIdParcours(),
                'nom_stand'    => $stand->getNomStand(),
                'position'     => $stand->getPosition(),
                'description'  => $stand->getDescription()
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('updateStand error: ' . $e->getMessage());
            return false;
        }
    }

    // =====================
    // INTELLIGENT RECOMMENDATION (Geocoding + Haversine)
    // =====================
    
    private function geocode($address) {
        if (empty($address)) return [36.8065, 10.1815];
        
        $addrClean = strtolower(trim($address));
        
        // 1. Cache local étendu pour les lieux fréquents du projet (Zaghouan et Tunis)
        $localCache = [
            'ariana'      => [36.8625, 10.1956],
            'menzah'      => [36.8465, 10.1706],
            'manzah'      => [36.8465, 10.1706],
            'nasser'      => [36.8580, 10.1600],
            'medina'      => [36.7992, 10.1706],
            'zaghouan'    => [36.4022, 10.1425],
            'lac'         => [36.8333, 10.2333],
            'marsa'       => [36.8778, 10.3222],
            'oued ellil'  => [36.8427, 10.0388],
            'oued el lil' => [36.8427, 10.0388],
            'oued lil'    => [36.8427, 10.0388],
            'manouba'     => [36.8078, 10.0864],
            'sidi thabet' => [36.9100, 10.0400],
            'sid thabet'  => [36.9100, 10.0400],
            'raoued'      => [36.9139, 10.1825],
            'tunis'       => [36.8065, 10.1815],
            'mourouj'     => [36.7261, 10.2111],
            'el mourouj'  => [36.7261, 10.2111],
            'ben arous'   => [36.7531, 10.2222],
            'bardo'       => [36.8092, 10.1411]
        ];

        foreach ($localCache as $key => $coords) {
            if (strpos($addrClean, $key) !== false) return $coords;
        }

        // 2. Fallback vers API Nominatim si non trouvé en cache
        try {
            $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($address . ", Tunisie") . "&format=json&limit=1";
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: BarchaThon-App/1.0\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data) && isset($data[0]['lat'])) {
                    return [(float)$data[0]['lat'], (float)$data[0]['lon']];
                }
            }
        } catch (Exception $e) {
            error_log("Geocoding error for $address: " . $e->getMessage());
        }

        // 3. Dernier recours : Centre de Tunis
        return [36.8065, 10.1815];
    }

    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1-$a));
    }

    public function getIntelligentRecommendations($searchTerm) {
        $allStands = $this->listStands();
        $targetStand = null;

        // 1. Identifier le stand recherché via son id_stand ou nom_stand.
        foreach ($allStands as $s) {
            if ($s['ID_stand'] == $searchTerm || strcasecmp($s['nom_stand'], $searchTerm) === 0) {
                $targetStand = $s;
                break;
            }
        }

        if (!$targetStand) return [];

        // 2. Convertir sa position
        list($lat1, $lon1) = $this->geocode($targetStand['position']);
        
        $results = [];
        // 3. Convertir aussi les positions des autres stands.
        foreach ($allStands as $stand) {
            if ($stand['ID_stand'] == $targetStand['ID_stand']) continue; // Ignorer le stand lui-même
            
            list($lat2, $lon2) = $this->geocode($stand['position']);
            // 5. Calculer la distance réelle
            $dist = $this->haversine($lat1, $lon1, $lat2, $lon2);
            
            $results[] = [
                'nom_stand' => $stand['nom_stand'],
                'position' => $stand['position'],
                'distance_km' => round($dist, 2),
                'description' => $stand['description'],
                'ID_parcours' => $stand['ID_parcours']
            ];
        }

        // 4. Filtrer en priorité les stands avec le même ID_parcours.
        // 6. Trier les stands par distance croissante.
        usort($results, function($a, $b) use ($targetStand) {
            $aSame = ($a['ID_parcours'] == $targetStand['ID_parcours']) ? 1 : 0;
            $bSame = ($b['ID_parcours'] == $targetStand['ID_parcours']) ? 1 : 0;
            
            if ($aSame !== $bSame) {
                return $bSame - $aSame; // Priorité au même parcours (1 avant 0)
            }
            return $a['distance_km'] <=> $b['distance_km']; // Ensuite par distance
        });

        // 7. Retourner les 5 stands les plus proches.
        $top5 = array_slice($results, 0, 5);

        // Nettoyer ID_parcours pour correspondre exactement au format de sortie demandé
        $finalOutput = [];
        foreach ($top5 as $r) {
            $finalOutput[] = [
                'nom_stand' => $r['nom_stand'],
                'position' => $r['position'],
                'distance_km' => $r['distance_km'],
                'description' => $r['description']
            ];
        }

        return $finalOutput;
    }

    public function getRecommendationsByCoords($lat1, $lon1) {
        $allStands = $this->listStands();
        $results = [];
        foreach ($allStands as $stand) {
            list($lat2, $lon2) = $this->geocode($stand['position']);
            $dist = $this->haversine($lat1, $lon1, $lat2, $lon2);
            $stand['distance_km'] = round($dist, 2);
            $results[] = $stand;
        }
        usort($results, function($a, $b) { return $a['distance_km'] <=> $b['distance_km']; });
        return $results;
    }

    public function getStandsWithCoords() {
        $allStands = $this->listStands();
        foreach ($allStands as &$stand) {
            list($lat, $lon) = $this->geocode($stand['position']);
            $stand['lat'] = $lat;
            $stand['lon'] = $lon;
        }
        return $allStands;
    }
}