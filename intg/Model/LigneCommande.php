<?php
class LigneCommande {
    private ?int $idligne;
    private ?int $idcommande;
    private ?int $idproduit;
    private ?int $quantite;
    private ?float $prixunitaire;

    //Constructor
    public function __construct(
        ?int $idligne,
        ?int $idcommande,
        ?int $idproduit,
        ?int $quantite,
        ?float $prixunitaire
    ) {
        $this->idligne = $idligne;
        $this->idcommande = $idcommande;
        $this->idproduit = $idproduit;
        $this->quantite = $quantite;
        $this->prixunitaire = $prixunitaire;
    }

    public function show() {
        echo "<tr>";
        echo "<td>{$this->idligne}</td>";
        echo "<td>{$this->idcommande}</td>";
        echo "<td>{$this->idproduit}</td>";
        echo "<td>{$this->quantite}</td>";
        echo "<td>" . number_format($this->prixunitaire, 2) . "</td>";
        echo "</tr>";
    }

    // Getters and Setters
    public function getIdligne(): ?int {
        return $this->idligne;
    }

    public function setIdligne(?int $idligne): void {
        $this->idligne = $idligne;
    }

    public function getIdcommande(): ?int {
        return $this->idcommande;
    }

    public function setIdcommande(?int $idcommande): void {
        $this->idcommande = $idcommande;
    }

    public function getIdproduit(): ?int {
        return $this->idproduit;
    }

    public function setIdproduit(?int $idproduit): void {
        $this->idproduit = $idproduit;
    }

    public function getQuantite(): ?int {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): void {
        $this->quantite = $quantite;
    }

    public function getPrixunitaire(): ?float {
        return $this->prixunitaire;
    }

    public function setPrixunitaire(?float $prixunitaire): void {
        $this->prixunitaire = $prixunitaire;
    }
}
?>
