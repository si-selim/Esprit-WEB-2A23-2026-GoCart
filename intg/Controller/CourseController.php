<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Course.php';

class CourseController {
    public function addCourse(Course $course) {
        $sql = "INSERT INTO courses (id_user, id_parcours, distance_parcourue, temps_realise, vitesse_moyenne, nombre_pas) 
                VALUES (:id_user, :id_parcours, :distance_parcourue, :temps_realise, :vitesse_moyenne, :nombre_pas)";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id_user', $course->getIdUser());
            $req->bindValue(':id_parcours', $course->getIdParcours());
            $req->bindValue(':distance_parcourue', $course->getDistanceParcourue());
            $req->bindValue(':temps_realise', $course->getTempsRealise());
            $req->bindValue(':vitesse_moyenne', $course->getVitesseMoyenne());
            $req->bindValue(':nombre_pas', $course->getNombrePas());
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getCoursesByUser($id_user) {
        $sql = "SELECT * FROM courses WHERE id_user = :id_user ORDER BY date_course DESC";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id_user', $id_user);
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
