<?php
class Commande {
    private ?int $idcommande;
    private ?int $idutilisateur;
    private ?int $idstand;
    private ?string $datecommande;
    private ?string $statut;
    private ?float $montanttotal;

    //Constructor
    public function __construct(
        ?int $idcommande,
        ?int $idutilisateur,
        ?int $idstand,
        ?string $datecommande,
        ?string $statut,
        ?float $montanttotal
    ) {
        $this->idcommande = $idcommande;
        $this->idutilisateur = $idutilisateur;
        $this->idstand = $idstand;
        $this->datecommande = $datecommande;
        $this->statut = $statut;
        $this->montanttotal = $montanttotal;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID Commande</th><th>ID Utilisateur</th><th>ID Stand</th><th>Date Commande</th><th>Statut</th><th>Montant Total</th></tr>";
        echo "<tr>";
        echo "<td>{$this->idcommande}</td>";
        echo "<td>{$this->idutilisateur}</td>";
        echo "<td>{$this->idstand}</td>";
        echo "<td>{$this->datecommande}</td>";
        echo "<td>{$this->statut}</td>";
        echo "<td>" . number_format($this->montanttotal, 2) . "</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters
    public function getIdcommande(): ?int {
        return $this->idcommande;
    }

    public function setIdcommande(?int $idcommande): void {
        $this->idcommande = $idcommande;
    }

    public function getIdutilisateur(): ?int {
        return $this->idutilisateur;
    }

    public function setIdutilisateur(?int $idutilisateur): void {
        $this->idutilisateur = $idutilisateur;
    }

    public function getIdstand(): ?int {
        return $this->idstand;
    }

    public function setIdstand(?int $idstand): void {
        $this->idstand = $idstand;
    }

    public function getDatecommande(): ?string {
        return $this->datecommande;
    }

    public function setDatecommande(?string $datecommande): void {
        $this->datecommande = $datecommande;
    }

    public function getStatut(): ?string {
        return $this->statut;
    }

    public function setStatut(?string $statut): void {
        $this->statut = $statut;
    }

    public function getMontanttotal(): ?float {
        return $this->montanttotal;
    }

    public function setMontanttotal(?float $montanttotal): void {
        $this->montanttotal = $montanttotal;
    }
}
?>
