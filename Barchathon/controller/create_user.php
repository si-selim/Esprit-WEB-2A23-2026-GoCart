<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$nom_complet = trim($_POST['nom_complet'] ?? '');
$nom_user = trim($_POST['nom_user'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';
$age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
$poids = !empty($_POST['poids']) ? (float)$_POST['poids'] : null;
$taille = !empty($_POST['taille']) ? (int)$_POST['taille'] : null;
$email = trim($_POST['email'] ?? '');
$pays = trim($_POST['pays'] ?? '') ?: null;
$ville = trim($_POST['ville'] ?? '') ?: null;
$tel = trim($_POST['tel'] ?? '') ?: null;
$occupation = trim($_POST['occupation'] ?? '') ?: null;

if ($nom_complet === '' || $nom_user === '' || $mot_de_passe === '' || $email === '') {
    header('Location: register.php?error=' . urlencode('Veuillez remplir tous les champs obligatoires.'));
    exit;
}

if (strlen($nom_complet) < 3) {
    header('Location: register.php?error=' . urlencode('Le nom complet doit contenir au moins 3 caracteres.'));
    exit;
}

if (strlen($nom_user) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $nom_user)) {
    header('Location: register.php?error=' . urlencode('Le nom d\'utilisateur doit contenir au moins 3 caracteres (lettres, chiffres, underscores).'));
    exit;
}

if (strlen($mot_de_passe) < 6) {
    header('Location: register.php?error=' . urlencode('Le mot de passe doit contenir au moins 6 caracteres.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=' . urlencode('Veuillez entrer une adresse email valide.'));
    exit;
}

if ($tel !== null && !preg_match('/^\d{8}$/', $tel)) {
    header('Location: register.php?error=' . urlencode('Le numero de telephone doit contenir exactement 8 chiffres.'));
    exit;
}

if ($age !== null && ($age < 1 || $age > 120)) {
    header('Location: register.php?error=' . urlencode('L\'age doit etre entre 1 et 120.'));
    exit;
}

if ($poids !== null && ($poids < 1 || $poids > 500)) {
    header('Location: register.php?error=' . urlencode('Le poids doit etre entre 1 et 500 kg.'));
    exit;
}

if ($taille !== null && ($taille < 1 || $taille > 300)) {
    header('Location: register.php?error=' . urlencode('La taille doit etre entre 1 et 300 cm.'));
    exit;
}

$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['profile_picture']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        header('Location: register.php?error=' . urlencode('Type de fichier non autorise. Formats acceptes : JPG, PNG, GIF, WEBP.'));
        exit;
    }

    if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
        header('Location: register.php?error=' . urlencode('La photo ne doit pas depasser 2 Mo.'));
        exit;
    }

    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $filename = uniqid('pp_', true) . '.' . $ext[$mime];
    $dest = __DIR__ . '/../uploads/' . $filename;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) {
        $profile_picture = $filename;
    }
}

$hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO `user` (nom_complet, nom_user, mot_de_passe, age, poids, taille, email, pays, ville, tel, occupation, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom_complet, $nom_user, $hash, $age, $poids, $taille, $email, $pays, $ville, $tel, $occupation, $profile_picture]);
    header('Location: login.php?success=' . urlencode('Compte cree avec succes.'));
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        header('Location: register.php?error=' . urlencode('Ce nom d\'utilisateur existe deja.'));
    } else {
        header('Location: register.php?error=' . urlencode('Erreur lors de la creation du compte.'));
    }
}
exit;
