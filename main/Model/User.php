<?php
class User {
    private $id_user;
    private $nom_complet;
    private $nom_user;
    private $mot_de_passe;
    private $email;
    private $role;
    private $age;
    private $poids;
    private $taille;
    private $tel;
    private $pays;
    private $ville;
    private $occupation;
    private $profile_picture;

    public function __construct($id_user, $nom_complet, $nom_user, $mot_de_passe, $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $profile_picture) {
        $this->id_user = $id_user;
        $this->nom_complet = $nom_complet;
        $this->nom_user = $nom_user;
        $this->mot_de_passe = $mot_de_passe;
        $this->email = $email;
        $this->role = $role;
        $this->age = $age;
        $this->poids = $poids;
        $this->taille = $taille;
        $this->tel = $tel;
        $this->pays = $pays;
        $this->ville = $ville;
        $this->occupation = $occupation;
        $this->profile_picture = $profile_picture;
    }

    public function getIdUser() { return $this->id_user; }
    public function getNomComplet() { return $this->nom_complet; }
    public function getNomUser() { return $this->nom_user; }
    public function getMotDePasse() { return $this->mot_de_passe; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getAge() { return $this->age; }
    public function getPoids() { return $this->poids; }
    public function getTaille() { return $this->taille; }
    public function getTel() { return $this->tel; }
    public function getPays() { return $this->pays; }
    public function getVille() { return $this->ville; }
    public function getOccupation() { return $this->occupation; }
    public function getProfilePicture() { return $this->profile_picture; }

    public function setNomComplet($v) { $this->nom_complet = $v; }
    public function setNomUser($v) { $this->nom_user = $v; }
    public function setMotDePasse($v) { $this->mot_de_passe = $v; }
    public function setEmail($v) { $this->email = $v; }
    public function setRole($v) { $this->role = $v; }
    public function setAge($v) { $this->age = $v; }
    public function setPoids($v) { $this->poids = $v; }
    public function setTaille($v) { $this->taille = $v; }
    public function setTel($v) { $this->tel = $v; }
    public function setPays($v) { $this->pays = $v; }
    public function setVille($v) { $this->ville = $v; }
    public function setOccupation($v) { $this->occupation = $v; }
    public function setProfilePicture($v) { $this->profile_picture = $v; }
}
