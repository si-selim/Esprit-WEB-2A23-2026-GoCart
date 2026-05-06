<?php

class Dossard {

    private ?int $id_dossard;
    private ?string $nom;
    private ?int $numero;
    private ?string $taille;
    private ?string $couleur;
    private ?int $id_inscription;
    private $circuit;
private $modePaiement;

    public function __construct(
        ?int $id_dossard = null,
        ?string $nom = null,
        ?int $numero = null,
        ?string $taille = null,
        ?string $couleur = null,
        ?int $id_inscription = null
    ) {
        $this->id_dossard = $id_dossard;
        $this->nom = $nom;
        $this->numero = $numero;
        $this->taille = $taille;
        $this->couleur = $couleur;
        $this->id_inscription = $id_inscription;
    }

    
    public function getNom() { return $this->nom; }
    public function getNumero() { return $this->numero; }
    public function getTaille() { return $this->taille; }
    public function getCouleur() { return $this->couleur; }
    public function getIdInscription() { return $this->id_inscription; }
    public function getCircuit() { return $this->circuit; }
public function getModePaiement() { return $this->modePaiement; }

    
    public function setNom($nom) { $this->nom = $nom; }
    public function setNumero($numero) { $this->numero = $numero; }
    public function setTaille($taille) { $this->taille = $taille; }
    public function setCouleur($couleur) { $this->couleur = $couleur; }
    public function setIdInscription($id) { $this->id_inscription = $id; }
}
?>