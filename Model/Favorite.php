<?php
class Favorite {
    private $id_favorite;
    private $id_user;
    private $id_produit;

    public function __construct($id_favorite = null, $id_user = null, $id_produit = null) {
        $this->id_favorite = $id_favorite;
        $this->id_user = $id_user;
        $this->id_produit = $id_produit;
    }

    public function getIdFavorite() { return $this->id_favorite; }
    public function getIdUser() { return $this->id_user; }
    public function getIdProduit() { return $this->id_produit; }

    public function setIdFavorite($id_favorite) { $this->id_favorite = $id_favorite; }
    public function setIdUser($id_user) { $this->id_user = $id_user; }
    public function setIdProduit($id_produit) { $this->id_produit = $id_produit; }
}
?>
