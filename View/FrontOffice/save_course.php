<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/CourseController.php';

header('Content-Type: application/json');

$user = getCurrentUser();
$userId = $user['id_user'] ?? $user['id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_parcours'], $data['distance'], $data['temps'], $data['vitesse'], $data['pas'])) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}

$courseController = new CourseController();
$course = new Course(
    null,
    $userId,
    (int)$data['id_parcours'],
    (float)$data['distance'],
    (float)$data['temps'],
    (float)$data['vitesse'],
    (int)$data['pas']
);

$res = $courseController->addCourse($course);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Course enregistrée avec succès.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la course.']);
}
