<?php
class Objectif {
    private ?int $id_objectif = null;
    private string $titre;
    private string $description;
    private string $type_objectif;
    private int $target_value;
    private string $recompense;
    private string $description_recompense;
    private int $etat;

    public function __construct(string $titre, string $description, string $type_objectif, int $target_value, string $recompense, string $description_recompense, int $etat = 1) {
        $this->titre = $titre;
        $this->description = $description;
        $this->type_objectif = $type_objectif;
        $this->target_value = $target_value;
        $this->recompense = $recompense;
        $this->description_recompense = $description_recompense;
        $this->etat = $etat;
    }

    public function getIdObjectif(): ?int { return $this->id_objectif; }
    public function setIdObjectif(?int $id): void { $this->id_objectif = $id; }
    
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): void { $this->titre = $titre; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getTypeObjectif(): string { return $this->type_objectif; }
    public function setTypeObjectif(string $type_objectif): void { $this->type_objectif = $type_objectif; }

    public function getTargetValue(): int { return $this->target_value; }
    public function setTargetValue(int $target_value): void { $this->target_value = $target_value; }

    public function getRecompense(): string { return $this->recompense; }
    public function setRecompense(string $recompense): void { $this->recompense = $recompense; }

    public function getDescriptionRecompense(): string { return $this->description_recompense; }
    public function setDescriptionRecompense(string $description_recompense): void { $this->description_recompense = $description_recompense; }

    public function getEtat(): int { return $this->etat; }
    public function setEtat(int $etat): void { $this->etat = $etat; }
}
?>
