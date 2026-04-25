<?php
class Marathon {
    private $id_marathon;
    private $nom_marathon;
    private $image_marathon;
    private $organisateur_marathon;
    private $region_marathon;
    private $date_marathon;
    private $nb_places_dispo;
    private $prix_marathon;

    public function __construct($id_marathon, $nom_marathon, $image_marathon, $organisateur_marathon, $region_marathon, $date_marathon, $nb_places_dispo, $prix_marathon) {
        $this->id_marathon = $id_marathon;
        $this->nom_marathon = $nom_marathon;
        $this->image_marathon = $image_marathon;
        $this->organisateur_marathon = $organisateur_marathon;
        $this->region_marathon = $region_marathon;
        $this->date_marathon = $date_marathon;
        $this->nb_places_dispo = $nb_places_dispo;
        $this->prix_marathon = $prix_marathon;
    }

    public function getIdMarathon() { return $this->id_marathon; }
    public function getNomMarathon() { return $this->nom_marathon; }
    public function getImageMarathon() { return $this->image_marathon; }
    public function getOrganisateurMarathon() { return $this->organisateur_marathon; }
    public function getRegionMarathon() { return $this->region_marathon; }
    public function getDateMarathon() { return $this->date_marathon; }
    public function getNbPlacesDispo() { return $this->nb_places_dispo; }
    public function getPrixMarathon() { return $this->prix_marathon; }

    public function setNomMarathon($v) { $this->nom_marathon = $v; }
    public function setImageMarathon($v) { $this->image_marathon = $v; }
    public function setOrganisateurMarathon($v) { $this->organisateur_marathon = $v; }
    public function setRegionMarathon($v) { $this->region_marathon = $v; }
    public function setDateMarathon($v) { $this->date_marathon = $v; }
    public function setNbPlacesDispo($v) { $this->nb_places_dispo = $v; }
    public function setPrixMarathon($v) { $this->prix_marathon = $v; }
}
