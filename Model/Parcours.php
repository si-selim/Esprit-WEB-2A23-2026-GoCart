<?php
class Parcours {
    private $id_parcours;
    private $nom_parcours;
    private $point_depart;
    private $point_arrivee;
    private $distance;
    private $difficulte;
    private $id_marathon;
    private $heure_depart;

    public function __construct($id_parcours, $nom_parcours, $point_depart, $point_arrivee, $distance, $difficulte, $id_marathon, $heure_depart = null) {
        $this->id_parcours   = $id_parcours;
        $this->nom_parcours  = $nom_parcours;
        $this->point_depart  = $point_depart;
        $this->point_arrivee = $point_arrivee;
        $this->distance      = $distance;
        $this->difficulte    = $difficulte;
        $this->id_marathon   = $id_marathon;
        $this->heure_depart  = $heure_depart;
    }

    public function getIdParcours()   { return $this->id_parcours; }
    public function getNomParcours()  { return $this->nom_parcours; }
    public function getPointDepart()  { return $this->point_depart; }
    public function getPointArrivee() { return $this->point_arrivee; }
    public function getDistance()     { return $this->distance; }
    public function getDifficulte()   { return $this->difficulte; }
    public function getIdMarathon()   { return $this->id_marathon; }
    public function getHeureDepart()  { return $this->heure_depart; }

    public function setNomParcours($v)  { $this->nom_parcours = $v; }
    public function setPointDepart($v)  { $this->point_depart = $v; }
    public function setPointArrivee($v) { $this->point_arrivee = $v; }
    public function setDistance($v)     { $this->distance = $v; }
    public function setDifficulte($v)   { $this->difficulte = $v; }
    public function setIdMarathon($v)   { $this->id_marathon = $v; }
    public function setHeureDepart($v)  { $this->heure_depart = $v; }
}
