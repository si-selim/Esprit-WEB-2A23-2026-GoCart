<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: modifyUser.php');
    exit;
}

$id = $_SESSION['user_id'];
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
$deleteOld = false;

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['profile_picture']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        header('Location: modifyUser.php?error=' . urlencode('Type de fichier non autorise.'));
        exit;
    }

    if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
        header('Location: modifyUser.php?error=' . urlencode('La photo ne doit pas depasser 2 Mo.'));
        exit;
    }

    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $filename = uniqid('pp_', true) . '.' . $ext[$mime];
    $dest = __DIR__ . '/../uploads/' . $filename;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) {
        $profile_picture = $filename;
        $deleteOld = true;
    }
}

try {
    if ($profile_picture) {
        $old = $pdo->prepare("SELECT profile_picture FROM `user` WHERE id_user = ?");
        $old->execute([$id]);
        $oldPic = $old->fetchColumn();

        $stmt = $pdo->prepare("UPDATE `user` SET nom_complet=?, nom_user=?, email=?, age=?, poids=?, taille=?, tel=?, pays=?, ville=?, occupation=?, profile_picture=? WHERE id_user=?");
        $stmt->execute([$nom_complet, $nom_user, $email, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $profile_picture, $id]);

        if ($deleteOld && $oldPic && file_exists(__DIR__ . '/../uploads/' . $oldPic)) {
            unlink(__DIR__ . '/../uploads/' . $oldPic);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE `user` SET nom_complet=?, nom_user=?, email=?, age=?, poids=?, taille=?, tel=?, pays=?, ville=?, occupation=? WHERE id_user=?");
        $stmt->execute([$nom_complet, $nom_user, $email, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $id]);
    }

    $_SESSION['nom_user'] = $nom_user;
    header('Location: profile.php?success=' . urlencode('Profil mis a jour.'));
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        header('Location: modifyUser.php?error=' . urlencode('Ce nom d\'utilisateur existe deja.'));
    } else {
        header('Location: modifyUser.php?error=' . urlencode('Erreur lors de la mise a jour.'));
    }
}
exit;
