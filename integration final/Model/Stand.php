<?php
require_once __DIR__ . '/../config.php';

class Stand {

    private ?int $idStand;
    private ?int $idParcours;
    private ?string $nomStand;
    private ?string $position;
    private ?string $description;

    // =====================
    // Constructor
    // =====================
    public function __construct(
        ?int $idStand = null,
        ?int $idParcours = null,
        ?string $nomStand = null,
        ?string $position = null,
        ?string $description = null
    ) {
        $this->idStand = $idStand;
        $this->idParcours = $idParcours;
        $this->nomStand = $nomStand;
        $this->position = $position;
        $this->description = $description;
    }

    // =====================
    // SAVE (INSERT / UPDATE)
    // =====================
    public function save(): bool {
        $pdo = config::getConnexion();

        try {
            if ($this->idStand === null) {

                // INSERT
                $stmt = $pdo->prepare("
                    INSERT INTO stand (ID_parcours, nom_stand, position, description)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute([
                    $this->idParcours,
                    $this->nomStand,
                    $this->position,
                    $this->description
                ]);

                $this->idStand = (int) $pdo->lastInsertId();

            } else {

                // UPDATE
                $stmt = $pdo->prepare("
                    UPDATE stand 
                    SET ID_parcours=?, nom_stand=?, position=?, description=?
                    WHERE ID_stand=?
                ");

                $stmt->execute([
                    $this->idParcours,
                    $this->nomStand,
                    $this->position,
                    $this->description,
                    $this->idStand
                ]);
            }

            return true;

        } catch (PDOException $e) {
            error_log('Stand save error: ' . $e->getMessage());
            return false;
        }
    }

    // =====================
    // DELETE
    // =====================
    public function delete(): bool {
        if ($this->idStand !== null) {
            try {
                $pdo = config::getConnexion();
                $stmt = $pdo->prepare("DELETE FROM stand WHERE ID_stand=?");
                $stmt->execute([$this->idStand]);
                return true;
            } catch (PDOException $e) {
                error_log('Stand delete error: ' . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    // =====================
    // FIND BY ID
    // =====================
    public static function findById(int $idStand): ?Stand {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM stand WHERE ID_stand=?");
            $stmt->execute([$idStand]);
            $data = $stmt->fetch();

            if ($data) {
                return new Stand(
                    (int) $data['ID_stand'],
                    isset($data['ID_parcours']) ? (int) $data['ID_parcours'] : null,
                    $data['nom_stand'] ?? null,
                    $data['position'] ?? null,
                    $data['description'] ?? null
                );
            }
        } catch (PDOException $e) {
            error_log('Stand findById error: ' . $e->getMessage());
        }

        return null;
    }

    // =====================
    // FIND BY NAME (LIKE)
    // =====================
    public static function findByName(string $name): ?Stand {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM stand WHERE nom_stand LIKE ? LIMIT 1");
            $stmt->execute(['%' . $name . '%']);
            $data = $stmt->fetch();

            if ($data) {
                return new Stand(
                    (int) $data['ID_stand'],
                    isset($data['ID_parcours']) ? (int) $data['ID_parcours'] : null,
                    $data['nom_stand'] ?? null,
                    $data['position'] ?? null,
                    $data['description'] ?? null
                );
            }
        } catch (PDOException $e) {
            error_log('Stand findByName error: ' . $e->getMessage());
        }

        return null;
    }

    // =====================
    // SEARCH BY ID OR NAME
    // =====================
    public static function findBySearch(string $search): ?Stand {
        // Si c'est un nombre, chercher par ID d'abord
        if (ctype_digit($search)) {
            $stand = self::findById((int) $search);
            if ($stand !== null) {
                return $stand;
            }
        }
        // Sinon chercher par nom
        return self::findByName($search);
    }

    // =====================
    // FIND ALL
    // =====================
    public static function findAll(): array {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->query("SELECT * FROM stand");

            $stands = [];

            while ($data = $stmt->fetch()) {
                $stands[] = new Stand(
                    (int) $data['ID_stand'],
                    isset($data['ID_parcours']) ? (int) $data['ID_parcours'] : null,
                    $data['nom_stand'] ?? null,
                    $data['position'] ?? null,
                    $data['description'] ?? null
                );
            }

            return $stands;

        } catch (PDOException $e) {
            error_log('Stand findAll error: ' . $e->getMessage());
            return [];
        }
    }

    // =====================
    // GETTERS
    // =====================
    public function getIdStand(): ?int {
        return $this->idStand;
    }

    public function getIdParcours(): ?int {
        return $this->idParcours;
    }

    public function getNomStand(): ?string {
        return $this->nomStand;
    }

    public function getPosition(): ?string {
        return $this->position;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    // =====================
    // SETTERS
    // =====================
    public function setIdParcours(?int $idParcours): void {
        $this->idParcours = $idParcours;
    }

    public function setNomStand(?string $nomStand): void {
        $this->nomStand = $nomStand;
    }

    public function setPosition(?string $position): void {
        $this->position = $position;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }
}