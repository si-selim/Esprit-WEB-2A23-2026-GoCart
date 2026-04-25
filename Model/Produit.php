<?php
class Produit {
    private $id_produit;
    private $id_stand;
    private $nom_produit;
    private $type;
    private $prix_produit;
    private $qte_stock;
    private $en_out_stock;

    public function __construct($id_produit, $id_stand, $nom_produit, $type, $prix_produit, $qte_stock, $en_out_stock) {
        $this->id_produit = $id_produit;
        $this->id_stand = $id_stand;
        $this->nom_produit = $nom_produit;
        $this->type = $type;
        $this->prix_produit = $prix_produit;
        $this->qte_stock = $qte_stock;
        $this->en_out_stock = $en_out_stock;
    }

    public function getIdProduit() { return $this->id_produit; }
    public function getIdStand() { return $this->id_stand; }
    public function getNomProduit() { return $this->nom_produit; }
    public function getType() { return $this->type; }
    public function getPrixProduit() { return $this->prix_produit; }
    public function getQteStock() { return $this->qte_stock; }
    public function getEnOutStock() { return $this->en_out_stock; }

    public function setIdStand($v) { $this->id_stand = $v; }
    public function setNomProduit($v) { $this->nom_produit = $v; }
    public function setType($v) { $this->type = $v; }
    public function setPrixProduit($v) { $this->prix_produit = $v; }
    public function setQteStock($v) { $this->qte_stock = $v; }
    public function setEnOutStock($v) { $this->en_out_stock = $v; }
}