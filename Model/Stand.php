<?php
class Stand {
    private $id_stand;
    private $nom_stand;
    private $position;
    private $description;
    private $id_parcours;

    public function __construct($id_stand, $nom_stand, $position, $description, $id_parcours) {
        $this->id_stand = $id_stand;
        $this->nom_stand = $nom_stand;
        $this->position = $position;
        $this->description = $description;
        $this->id_parcours = $id_parcours;
    }

    public function getIdStand() { return $this->id_stand; }
    public function getNomStand() { return $this->nom_stand; }
    public function getPosition() { return $this->position; }
    public function getDescription() { return $this->description; }
    public function getIdParcours() { return $this->id_parcours; }

    public function setNomStand($v) { $this->nom_stand = $v; }
    public function setPosition($v) { $this->position = $v; }
    public function setDescription($v) { $this->description = $v; }
    public function setIdParcours($v) { $this->id_parcours = $v; }
}