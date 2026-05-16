<?php
class Sponsoring {
    private ?int $idSponsoring;
    private ?string $nomSponsoring;
    private ?string $dateDebut;
    private ?string $dateFin;
    private ?float $montant;
    private ?string $etat;
    private ?int $idSponsor;
    private ?int $idMarathon;

    // Constructor
    public function __construct(?int $idSponsoring, ?string $nomSponsoring, ?string $dateDebut, ?string $dateFin, ?float $montant, ?string $etat, ?int $idSponsor, ?int $idMarathon) {
        $this->idSponsoring = $idSponsoring;
        $this->nomSponsoring = $nomSponsoring;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->montant = $montant;
        $this->etat = $etat;
        $this->idSponsor = $idSponsor;
        $this->idMarathon = $idMarathon;
    }

    // Getters and Setters
    public function getIdSponsoring(): ?int {
        return $this->idSponsoring;
    }

    public function setIdSponsoring(?int $idSponsoring): void {
        $this->idSponsoring = $idSponsoring;
    }

    public function getNomSponsoring(): ?string {
        return $this->nomSponsoring;
    }

    public function setNomSponsoring(?string $nomSponsoring): void {
        $this->nomSponsoring = $nomSponsoring;
    }

    public function getDateDebut(): ?string {
        return $this->dateDebut;
    }

    public function setDateDebut(?string $dateDebut): void {
        $this->dateDebut = $dateDebut;
    }

    public function getDateFin(): ?string {
        return $this->dateFin;
    }

    public function setDateFin(?string $dateFin): void {
        $this->dateFin = $dateFin;
    }

    public function getMontant(): ?float {
        return $this->montant;
    }

    public function setMontant(?float $montant): void {
        $this->montant = $montant;
    }

    public function getEtat(): ?string {
        return $this->etat;
    }

    public function setEtat(?string $etat): void {
        $this->etat = $etat;
    }

    public function getIdSponsor(): ?int {
        return $this->idSponsor;
    }

    public function setIdSponsor(?int $idSponsor): void {
        $this->idSponsor = $idSponsor;
    }

    public function getIdMarathon(): ?int {
        return $this->idMarathon;
    }

    public function setIdMarathon(?int $idMarathon): void {
        $this->idMarathon = $idMarathon;
    }
}
?>