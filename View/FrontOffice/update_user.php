<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isConnected()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: modifyUser.php');
    exit;
}

$ctrl = new UserController();
$id = getUserId();

$nom_complet = trim($_POST['nom_complet'] ?? '');
$nom_user = trim($_POST['nom_user'] ?? '');
$email = trim($_POST['email'] ?? '');
$age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
$poids = !empty($_POST['poids']) ? (float)$_POST['poids'] : null;
$taille = !empty($_POST['taille']) ? (int)$_POST['taille'] : null;
$tel = trim($_POST['tel'] ?? '') ?: null;
$pays = trim($_POST['pays'] ?? '') ?: null;
$ville = trim($_POST['ville'] ?? '') ?: null;
$occupation = trim($_POST['occupation'] ?? '') ?: null;

if ($nom_complet === '' || $nom_user === '' || $email === '') {
    header('Location: modifyUser.php?error=' . urlencode('Veuillez remplir tous les champs obligatoires.'));
    exit;
}

if (strlen($nom_complet) < 3) {
    header('Location: modifyUser.php?error=' . urlencode('Le nom complet doit contenir au moins 3 caracteres.'));
    exit;
}

if (strlen($nom_user) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $nom_user)) {
    header('Location: modifyUser.php?error=' . urlencode('Le nom d\'utilisateur doit contenir au moins 3 caracteres (lettres, chiffres, underscores).'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: modifyUser.php?error=' . urlencode('Veuillez entrer une adresse email valide.'));
    exit;
}

if ($tel !== null && !preg_match('/^\d{8}$/', $tel)) {
    header('Location: modifyUser.php?error=' . urlencode('Le numero de telephone doit contenir exactement 8 chiffres.'));
    exit;
}

if ($age !== null && ($age < 1 || $age > 120)) {
    header('Location: modifyUser.php?error=' . urlencode('L\'age doit etre entre 1 et 120.'));
    exit;
}

if ($poids !== null && ($poids < 1 || $poids > 500)) {
    header('Location: modifyUser.php?error=' . urlencode('Le poids doit etre entre 1 et 500 kg.'));
    exit;
}

if ($taille !== null && ($taille < 1 || $taille > 300)) {
    header('Location: modifyUser.php?error=' . urlencode('La taille doit etre entre 1 et 300 cm.'));
    exit;
}

$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $result = $ctrl->saveProfilePicture($_FILES['profile_picture']);
    if ($result === false) {
        header('Location: modifyUser.php?error=' . urlencode('Type de fichier non autorise ou photo trop volumineuse (max 2 Mo).'));
        exit;
    }
    $profile_picture = $result;
}

$currentUser = $ctrl->showUser($id);
$role = $currentUser['role'];

$userObj = new User(null, $nom_complet, $nom_user, '', $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $profile_picture);

try {
    if ($profile_picture) {
        $oldPic = $currentUser['profile_picture'];
        $ctrl->modifierUserAvecPhoto($userObj, $id);
        if ($oldPic) {
            $ctrl->deleteOldPicture($oldPic);
        }
        $_SESSION['user']['profile_picture'] = $profile_picture;
    } else {
        $ctrl->modifierUser($userObj, $id);
    }

    $_SESSION['user']['username'] = $nom_user;
    $_SESSION['user']['nom'] = $nom_complet;
    $_SESSION['user']['email'] = $email;
    header('Location: profile.php?success=' . urlencode('Profil mis a jour.'));
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        header('Location: modifyUser.php?error=' . urlencode('Ce nom d\'utilisateur existe deja.'));
    } else {
        header('Location: modifyUser.php?error=' . urlencode('Erreur lors de la mise a jour.'));
    }
}
exit;
