<?php
class Sponsor {
    private ?int $idSponsor;
    private ?string $nom;
    private ?string $type;
    private ?string $adresse;
    private ?string $contact;
    private ?string $email;
    private ?string $pageWeb;
    private ?int $idUser;

    // Constructor
    public function __construct(?int $idSponsor, ?string $nom, ?string $type, ?string $adresse, ?string $contact, ?string $email, ?string $pageWeb, ?int $idUser) {
        $this->idSponsor = $idSponsor;
        $this->nom = $nom;
        $this->type = $type;
        $this->adresse = $adresse;
        $this->contact = $contact;
        $this->email = $email;
        $this->pageWeb = $pageWeb;
        $this->idUser = $idUser;
    }

    // Getters and Setters
    public function getIdSponsor(): ?int {
        return $this->idSponsor;
    }

    public function setIdSponsor(?int $idSponsor): void {
        $this->idSponsor = $idSponsor;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function setNom(?string $nom): void {
        $this->nom = $nom;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(?string $type): void {
        $this->type = $type;
    }

    public function getAdresse(): ?string {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): void {
        $this->adresse = $adresse;
    }

    public function getContact(): ?string {
        return $this->contact;
    }

    public function setContact(?string $contact): void {
        $this->contact = $contact;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getPageWeb(): ?string {
        return $this->pageWeb;
    }

    public function setPageWeb(?string $pageWeb): void {
        $this->pageWeb = $pageWeb;
    }

    public function getIdUser(): ?int {
        return $this->idUser;
    }

    public function setIdUser(?int $idUser): void {
        $this->idUser = $idUser;
    }

    
}
?>