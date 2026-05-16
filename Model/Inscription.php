<?php

class Inscription {

    private ?int $id_inscription;
    private ?int $nb_personnes;
    private ?string $mode_paiement;
    private ?string $date_paiement;
    private ?int $id_parcours;
    private ?int $id_user;
    private ?string $statut_paiement;

    public function __construct(
        ?int $id_inscription = null,
        ?int $nb_personnes = null,
        ?string $mode_paiement = null,
        ?string $date_paiement = null,
        ?int $id_parcours = null,
        ?int $id_user = null,
        ?string $statut_paiement = "unpaid"
    ) {
        $this->id_inscription = $id_inscription;
        $this->nb_personnes = $nb_personnes;
        $this->mode_paiement = $mode_paiement;
        $this->date_paiement = $date_paiement;
        $this->id_parcours = $id_parcours;
        $this->id_user = $id_user;
        $this->statut_paiement = $statut_paiement;
    }

   

    public function getIdInscription(): ?int {
        return $this->id_inscription;
    }

    public function getNbPersonnes(): ?int {
        return $this->nb_personnes;
    }

    public function getModePaiement(): ?string {
        return $this->mode_paiement;
    }

    public function getDatePaiement(): ?string {
        return $this->date_paiement;
    }

    public function getIdParcours(): ?int {
        return $this->id_parcours;
    }

    public function getIdUser(): ?int {
        return $this->id_user;
    }

    public function getStatutPaiement(): ?string {
        return $this->statut_paiement;
    }

    

    public function setIdInscription($id) {
        $this->id_inscription = $id;
    }

    public function setNbPersonnes($nb) {
        $this->nb_personnes = $nb;
    }

    public function setModePaiement($mode) {
        $this->mode_paiement = $mode;
    }

    public function setDatePaiement($date) {
        $this->date_paiement = $date;
    }

    public function setIdParcours($id) {
        $this->id_parcours = $id;
    }

    public function setIdUser($id) {
        $this->id_user = $id;
    }

    public function setStatutPaiement($statut) {
        $this->statut_paiement = $statut;
    }
}
?>