# BarchaThon — Plateforme de gestion de marathons

Application web full-stack **PHP / MySQL** pour la gestion d'evenements marathon, avec un espace utilisateur (front office) et un tableau de bord administrateur (back office).

---

## Structure du projet

```
Barchathon/
├── config.php                        # Connexion PDO (classe config)
├── config_mail.php                   # Config SMTP (charge .local.php si present)
├── config_mail.local.php             # Identifiants SMTP reels — NON commite
├── config_google.php                 # Config OAuth Google (charge .local.php si present)
├── config_google.local.php           # Client ID / Secret Google — NON commite
├── user.sql                          # Schema BDD + donnees demo
│
├── Controller/
│   ├── UserController.php            # CRUD utilisateurs, Google OAuth, reset password
│   ├── MarathonController.php        # CRUD marathons
│   ├── ParcoursController.php        # CRUD parcours
│   ├── CommandeController.php        # Gestion des commandes
│   ├── LigneCommandeController.php   # Lignes de commande
│   └── Mailer.php                    # Envoi email SMTP (STARTTLS, raw stream)
│
├── Model/
│   └── User.php                      # Entite utilisateur
│
└── View/
    ├── assets/
    │   ├── css/style.css             # Feuille de style unifiee
    │   └── js/
    │       ├── app.js                # Animations, modals, effets
    │       ├── theme.js              # Bascule dark / light mode
    │       └── voice-nav.js          # Navigation vocale (Ctrl+G)
    │
    ├── BackOffice/
    │   └── dashboard.php             # Tableau de bord admin
    │
    └── FrontOffice/
        ├── partials/
        │   ├── topbar.php            # Barre de navigation avec dark mode
        │   ├── footer.php
        │   └── session.php
        ├── login.php                 # Connexion classique
        ├── register.php              # Inscription avec verification email
        ├── forgot_password.php       # Demande de reinitialisation de mot de passe
        ├── reset_password.php        # Formulaire nouveau mot de passe (token)
        ├── google_login.php          # Initie le flux OAuth Google
        ├── google_callback.php       # Callback OAuth — cree / lie le compte
        ├── face_login.php            # Connexion par reconnaissance faciale
        ├── face_enroll.php           # Enregistrement du visage
        ├── verify_email.php          # Verification du token email
        ├── profile.php               # Profil utilisateur
        ├── modifyUser.php            # Modification du profil
        ├── accueil.php               # Page d'accueil
        ├── listMarathons.php         # Liste des marathons
        ├── detailMarathon.php        # Detail d'un marathon
        ├── listParcours.php          # Liste des parcours
        ├── marathon/                 # CRUD marathons + export PDF
        └── parcours/                 # CRUD parcours + export PDF
```

---

## Installation

### 1. Base de donnees

```bash
mysql -u root -p < user.sql
```

Cela cree la base `barchathon`, la table `user` (avec toutes les colonnes) et insere trois comptes demo.

> Si la base existe deja, les `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` ajoutent uniquement les colonnes manquantes sans toucher aux donnees.

### 2. Configuration SMTP (emails)

Copier le fichier template et remplir vos identifiants :

```bash
cp config_mail.php config_mail.local.php
```

Editer `config_mail.local.php` :

```php
define('MAIL_HOST',      'smtp.example.com');
define('MAIL_PORT',      587);
define('MAIL_USERNAME',  'vous@example.com');
define('MAIL_PASSWORD',  'votre_mot_de_passe');
define('MAIL_FROM',      'vous@example.com');
define('MAIL_FROM_NAME', 'BarchaThon');
```

### 3. Configuration Google OAuth (connexion via Google)

1. Creer un projet sur [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Ajouter `http://localhost/Barchathon/View/FrontOffice/google_callback.php` comme URI de redirection autorisee
3. Copier Client ID et Client Secret dans `config_google.local.php` :

```php
$GOOGLE_CLIENT_ID     = 'VOTRE_CLIENT_ID.apps.googleusercontent.com';
$GOOGLE_CLIENT_SECRET = 'VOTRE_CLIENT_SECRET';
```

### 4. Lancer sous XAMPP

Placer le dossier dans `C:\xampp\htdocs\` et acceder a :

```
http://localhost/Barchathon/View/FrontOffice/login.php
```

### 5. Comptes demo

| Nom d'utilisateur | Role         | Email                      |
|-------------------|--------------|----------------------------|
| admin             | admin        | admin@barchathon.tn        |
| organisateur      | organisateur | orga@barchathon.tn         |
| participant       | participant  | participant@barchathon.tn  |

---

## Fonctionnalites

### Authentification
- Inscription avec upload de photo de profil et verification par email
- Connexion classique (nom d'utilisateur + mot de passe)
- Connexion via Google (OAuth 2.0) — cree ou lie le compte automatiquement
- Connexion par reconnaissance faciale (face-api.js)
- Reinitialisation de mot de passe par email (token a usage unique, expire apres 1h)
- Deconnexion

### Front Office (utilisateur)
- Page d'accueil et navigation principale
- Consultation et modification du profil (photo, informations personnelles)
- Changement de mot de passe
- Liste et detail des marathons et parcours
- Gestion des commandes

### Back Office (administrateur)
- Tableau de bord avec statistiques dynamiques et salutation animee
- Liste des utilisateurs avec pagination, recherche, filtres par role / pays, tri
- Actions rapides : modifier, bloquer / debloquer, supprimer
- Export CSV / PDF des utilisateurs

### Navigation vocale (Ctrl+G)
- Activation / desactivation par raccourci clavier `Ctrl+G`
- Commandes en francais pour naviguer entre les pages
- Commandes admin : recherche, filtres, CRUD, confirmation de modals
- Synthese vocale (fr-FR) pour le retour sonore
- Compatible Chrome et Edge (necessite un micro)

### UI / UX
- Dark mode / light mode persistant (localStorage)
- Salutation animee avec heure en temps reel sur le dashboard
- Design responsive (mobile, tablette, desktop)
- Animations fade-in et transitions CSS
- Modals de confirmation pour les actions destructives

### Securite
- Mots de passe hashes avec `password_hash()` (bcrypt)
- Requetes preparees PDO contre les injections SQL
- `htmlspecialchars()` sur toutes les sorties (XSS)
- Verification CSRF pour le flux OAuth Google (`state` token)
- Tokens de verification email et de reset a usage unique
- Validation MIME et taille pour les uploads d'images
- Verification de session et de role pour les pages protegees

---

## Variables d'environnement / fichiers secrets

| Fichier                  | Contenu                        | Commite ? |
|--------------------------|--------------------------------|-----------|
| `config_mail.local.php`  | Identifiants SMTP              | Non       |
| `config_google.local.php`| Client ID / Secret Google      | Non       |
| `View/FrontOffice/images/uploads/` | Photos de profil      | Non       |
