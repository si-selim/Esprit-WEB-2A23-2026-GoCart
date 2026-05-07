<?php
require_once __DIR__ . '/../config.php';

class Produit {
    private ?int $ID_produit;
    private ?int $ID_stand;
    private ?string $nom_produit;
    private ?string $type;
    private ?float $prix_produit;
    private ?int $qte_stock;
    private ?int $en_out_stock;
    private ?string $image;

    public function __construct(
        ?int $ID_produit = null,
        ?int $ID_stand = null,
        ?string $nom_produit = null,
        ?string $type = null,
        ?float $prix_produit = null,
        ?int $qte_stock = null,
        ?int $en_out_stock = null,
        ?string $image = null
    ) {
        $this->ID_produit = $ID_produit;
        $this->ID_stand = $ID_stand;
        $this->nom_produit = $nom_produit;
        $this->type = $type;
        $this->prix_produit = $prix_produit;
        $this->qte_stock = $qte_stock;
        $this->en_out_stock = $en_out_stock;
        $this->image = $image;
    }

    // =====================
    // SAVE (INSERT / UPDATE)
    // =====================
    public function save(): bool {
        $pdo = config::getConnexion();
        try {
            if ($this->ID_produit === null) {
                // INSERT
                $stmt = $pdo->prepare("
                    INSERT INTO produit (ID_stand, nom_produit, type, prix_produit, qte_stock, en_out_stock, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $this->ID_stand,
                    $this->nom_produit,
                    $this->type,
                    $this->prix_produit,
                    $this->qte_stock,
                    $this->en_out_stock,
                    $this->image
                ]);
                $this->ID_produit = (int)$pdo->lastInsertId();
            } else {
                // UPDATE
                $stmt = $pdo->prepare("
                    UPDATE produit 
                    SET ID_stand=?, nom_produit=?, type=?, prix_produit=?, qte_stock=?, en_out_stock=?
                    WHERE ID_produit=?
                ");
                $stmt->execute([
                    $this->ID_stand,
                    $this->nom_produit,
                    $this->type,
                    $this->prix_produit,
                    $this->qte_stock,
                    $this->en_out_stock,
                    $this->ID_produit
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log('Produit save error: ' . $e->getMessage());
            return false;
        }
    }

    // =====================
    // DELETE
    // =====================
    public function delete(): bool {
        if ($this->ID_produit !== null) {
            try {
                $pdo = config::getConnexion();
                $stmt = $pdo->prepare("DELETE FROM produit WHERE ID_produit=?");
                $stmt->execute([$this->ID_produit]);
                return true;
            } catch (PDOException $e) {
                error_log('Produit delete error: ' . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    // =====================
    // FINDERS
    // =====================
    public static function findById(int $id): ?Produit {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM produit WHERE ID_produit=?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? self::fromArray($data) : null;
    }

    public static function findAll(?string $sort = null, ?string $order = 'ASC'): array {
        $pdo = config::getConnexion();
        $query = "SELECT * FROM produit";
        
        $allowedSorts = ['ID_produit', 'ID_stand', 'nom_produit', 'type', 'prix_produit', 'qte_stock', 'en_out_stock', 'image'];
        if ($sort && in_array($sort, $allowedSorts)) {
            $order = ($order === 'DESC') ? 'DESC' : 'ASC';
            $query .= " ORDER BY $sort $order";
        }

        $stmt = $pdo->query($query);
        $produits = [];
        while ($data = $stmt->fetch()) {
            $produits[] = self::fromArray($data);
        }
        return $produits;
    }

    public static function findByStandId(int $idStand): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM produit WHERE ID_stand = ?");
        $stmt->execute([$idStand]);
        $produits = [];
        while ($data = $stmt->fetch()) {
            $produits[] = self::fromArray($data);
        }
        return $produits;
    }

    public static function search(string $search): array {
        $pdo = config::getConnexion();
        if (is_numeric($search)) {
            $stmt = $pdo->prepare("SELECT * FROM produit WHERE ID_produit = ? OR LOWER(nom_produit) LIKE LOWER(?) OR LOWER(type) LIKE LOWER(?)");
            $stmt->execute([$search, '%' . $search . '%', '%' . $search . '%']);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM produit WHERE LOWER(nom_produit) LIKE LOWER(?) OR LOWER(type) LIKE LOWER(?)");
            $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
        }
        $produits = [];
        while ($data = $stmt->fetch()) {
            $produits[] = self::fromArray($data);
        }
        return $produits;
    }

    private static function fromArray(array $data): Produit {
        return new Produit(
            (int)$data['ID_produit'],
            (int)$data['ID_stand'],
            $data['nom_produit'],
            $data['type'],
            (float)$data['prix_produit'],
            (int)$data['qte_stock'],
            (int)$data['en_out_stock'],
            $data['image'] ?? null
        );
    }

    // Getters
    public function getIDProduit(): ?int { return $this->ID_produit; }
    public function getIDStand(): ?int { return $this->ID_stand; }
    public function getNomProduit(): ?string { return $this->nom_produit; }
    public function getType(): ?string { return $this->type; }
    public function getPrixProduit(): ?float { return $this->prix_produit; }
    public function getQteStock(): ?int { return $this->qte_stock; }
    public function getEnOutStock(): ?int { 
        if ($this->en_out_stock === null) return null;
        return (int)$this->en_out_stock; 
    }
    public function getImage(): ?string { 
        return $this->image ?? ''; // Retourner une chaîne vide si null
    }

    // Setters
    public function setNomProduit(?string $v) { $this->nom_produit = $v; }
    public function setIDStand(?int $v) { $this->ID_stand = $v; }
    public function setType(?string $v) { $this->type = $v; }
    public function setPrixProduit(?float $v) { $this->prix_produit = $v; }
    public function setQteStock(?int $v) { $this->qte_stock = $v; }
    public function setEnOutStock(?int $v) { $this->en_out_stock = $v; }
    public function setImage(?string $v) { $this->image = $v; }
}