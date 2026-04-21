# Barchathon - Plateforme de gestion de marathons

Application web full-stack PHP/MySQL pour la gestion d'evenements marathon, avec un espace utilisateur (front office) et un tableau de bord administrateur (back office).

## Structure du projet

```
Barchathon/
├── config/
│   └── db.php                     # Connexion PDO a MySQL
├── controller/
│   ├── create_user.php            # Inscription avec photo de profil
│   ├── update_user.php            # Mise a jour du profil utilisateur
│   ├── delete_account.php         # Suppression du propre compte (front office)
│   ├── delete_user.php            # Suppression par l'admin (back office)
│   ├── read_users.php             # Liste paginee des utilisateurs (back office)
│   ├── change_password.php        # Changement de mot de passe
│   └── logout.php                 # Deconnexion
├── uploads/                       # Photos de profil uploadees
├── view/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css          # Feuille de style unifiee
│   │   ├── js/
│   │   │   └── app.js             # Animations, modals, effets
│   │   └── images/
│   │       └── logo_barchathon.jpg
│   ├── BackOffice/
│   │   ├── dashboard.php          # Tableau de bord admin
│   │   └── backoffice_User.php    # Gestion des utilisateurs
│   └── frontOffice/
│       ├── login.php              # Connexion
│       ├── register.php           # Inscription
│       ├── profile.php            # Profil utilisateur
│       └── modifyUser.php         # Modification du profil
└── user.sql                       # Schema et donnees initiales
```

## Installation

### 1. Base de donnees

Importer le fichier SQL dans MySQL :

```bash
mysql -u root -p < user.sql
```

Cela cree la base `barchathon` et la table `user` avec trois comptes demo.

### 2. Configuration

Modifier `Barchathon/config/db.php` si necessaire (host, username, password).

### 3. Serveur PHP

Lancer un serveur de developpement depuis le dossier `Barchathon/` :

```bash
cd Barchathon
php -S localhost:8000
```

Acceder a l'application : `http://localhost:8000/view/frontOffice/login.php`

### 4. Comptes demo

Les mots de passe des comptes demo correspondent au nom d'utilisateur. Pour les utiliser, recreez-les via le formulaire d'inscription ou inserez manuellement des hash bcrypt.

| Nom d'utilisateur | Role         |
|-------------------|--------------|
| admin             | admin        |
| organisateur      | organisateur |
| participant       | participant  |

## Fonctionnalites

### Front Office (utilisateur)
- Inscription avec upload de photo de profil
- Connexion / deconnexion avec sessions PHP
- Consultation du profil avec informations personnelles
- Modification du profil et de la photo
- Changement de mot de passe
- Suppression du compte avec confirmation modale

### Back Office (administrateur)
- Tableau de bord avec statistiques dynamiques (nombre d'utilisateurs, repartition par role, taux de completion)
- Liste des utilisateurs avec pagination (5 par page)
- Recherche par nom, email ou pays
- Filtres par role et par pays
- Suppression d'utilisateurs avec confirmation modale

### UI / UX
- Design unifie avec feuille de style partagee (CSS custom properties)
- Animations au scroll (fade-in, slide-up) via IntersectionObserver
- Effets hover sur les cartes, boutons et elements interactifs
- Modals de confirmation pour les actions destructives
- Feedback visuel automatique (succes/erreur) via parametres URL
- Responsive design (mobile, tablette, desktop)

### Securite
- Mots de passe hashes avec `password_hash()` (bcrypt)
- Requetes preparees PDO contre les injections SQL
- `htmlspecialchars()` sur toutes les sorties pour prevenir le XSS
- Validation MIME et taille pour les uploads d'images
- Verification de session et de role pour les pages protegees

## Screenshots

_Section reservee pour les captures d'ecran de l'application._

| Page | Capture |
|------|---------|
| Login | ![Login](screenshots/login.png) |
| Inscription | ![Register](screenshots/register.png) |
| Profil | ![Profile](screenshots/profile.png) |
| Dashboard admin | ![Dashboard](screenshots/dashboard.png) |
| Gestion utilisateurs | ![Users](screenshots/users.png) |
