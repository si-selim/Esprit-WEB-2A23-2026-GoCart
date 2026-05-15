<?php
class Course {
    private ?int $id;
    private int $id_user;
    private int $id_parcours;
    private float $distance_parcourue;
    private float $temps_realise;
    private float $vitesse_moyenne;
    private int $nombre_pas;
    private ?string $date_course;

    public function __construct(
        ?int $id,
        int $id_user,
        int $id_parcours,
        float $distance_parcourue,
        float $temps_realise,
        float $vitesse_moyenne,
        int $nombre_pas,
        ?string $date_course = null
    ) {
        $this->id = $id;
        $this->id_user = $id_user;
        $this->id_parcours = $id_parcours;
        $this->distance_parcourue = $distance_parcourue;
        $this->temps_realise = $temps_realise;
        $this->vitesse_moyenne = $vitesse_moyenne;
        $this->nombre_pas = $nombre_pas;
        $this->date_course = $date_course;
    }

    public function getId(): ?int { return $this->id; }
    public function getIdUser(): int { return $this->id_user; }
    public function getIdParcours(): int { return $this->id_parcours; }
    public function getDistanceParcourue(): float { return $this->distance_parcourue; }
    public function getTempsRealise(): float { return $this->temps_realise; }
    public function getVitesseMoyenne(): float { return $this->vitesse_moyenne; }
    public function getNombrePas(): int { return $this->nombre_pas; }
    public function getDateCourse(): ?string { return $this->date_course; }
}
