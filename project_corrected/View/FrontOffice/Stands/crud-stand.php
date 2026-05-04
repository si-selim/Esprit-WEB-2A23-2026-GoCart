<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: listStandsFront.php'); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Stand | BarchaThon</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #f5f8f4, #eef8f8);
            color: #102a43;
            padding: 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #0f766e;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 20px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #0b5e52;
        }

        .form-page {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px 44px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.07);
        }

        .form-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 10px;
        }

        .form-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            flex-shrink: 0;
        }

        .form-header h1 {
            margin: 0;
            font-size: 1.65rem;
            color: #1a2332;
            font-weight: 800;
        }

        .form-subtitle {
            color: #5a6f80;
            font-size: 0.92rem;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            margin-bottom: 24px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-field.full-width {
            grid-column: span 2;
        }

        .form-field label {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1a2332;
        }

        .form-field input,
        .form-field select,
        .form-field textarea {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #d9e2ec;
            border-radius: 12px;
            font-size: 0.92rem;
            color: #334e68;
            background: #f8fafb;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .form-field input::placeholder,
        .form-field textarea::placeholder {
            color: #94a3b8;
        }

        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-field textarea {
            min-height: 100px;
            resize: vertical;
        }

        .error {
            color: #ef4444;
            font-size: 0.8rem;
            min-height: 16px;
        }

        /* --- Separator --- */
        .form-separator {
            border: none;
            border-top: 1px solid #e8ecf0;
            margin: 28px 0;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #6b7b8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }

        /* --- Search row --- */
        .search-row {
            display: flex;
            gap: 14px;
            align-items: flex-end;
            margin-bottom: 24px;
        }

        .search-field {
            flex: 1;
        }

        /* --- Sort row --- */
        .sort-row {
            display: flex;
            gap: 14px;
            align-items: flex-end;
            margin-bottom: 24px;
        }

        /* --- Buttons --- */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px 24px;
            background: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-submit:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            background: #1e2d3d;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-secondary:hover {
            background: #2a3f55;
            transform: translateY(-1px);
        }

        .btn-search {
            padding: 13px 22px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-search:hover {
            background: #1d4ed8;
        }

        .btn-sort {
            padding: 13px 22px;
            background: #0f766e;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-sort:hover {
            background: #0b5e52;
        }

        .action-row {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        @media (max-width: 700px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-field.full-width {
                grid-column: span 1;
            }

            .form-page {
                padding: 24px 20px;
            }

            .search-row,
            .sort-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>

<body>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>
    <div style="padding-top: 80px;">
        <?php 
        $parcoursId = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : (isset($_POST['parcours_id']) ? (int)$_POST['parcours_id'] : 0);
        $backUrl = $parcoursId > 0 ? "../detailParcours.php?id=" . $parcoursId : "listStandsFront.php"; 
        ?>
        <div style="max-width:860px;margin:0 auto;padding:16px 20px 0;">
            <a href="<?php echo $backUrl; ?>" class="back-link">← Retour à la liste</a>
        </div>
    <div class="form-page">
        <div class="form-header">
            <div class="form-header-icon">🏪</div>
            <h1>Ajouter un Stand</h1>
        </div>
        <p class="form-subtitle">Remplissez les informations du nouveau stand. Tous les champs sont obligatoires.</p>

        <form action="addStand.php" method="post" onsubmit="return validateStand(event)">

            <div class="form-grid">
                <div class="form-field">
                    <label>Nom Stand</label>
                    <input type="text" id="nomStand" name="nom_stand" placeholder="Ex: Stand Hydratation">
                    <span class="error" id="error-nomStand"></span>
                </div>

                <div class="form-field">
                    <label>Position</label>
                    <input type="text" id="position" name="position" placeholder="Ex: Km 5">
                    <span class="error" id="error-position"></span>
                </div>

                <input type="hidden" name="ID_parcours" value="<?php echo $parcoursId; ?>">

                <div class="form-field full-width">
                    <label>Description</label>
                    <textarea id="description" name="description" placeholder="Décrivez le stand..." rows="4"></textarea>
                    <span class="error" id="error-description"></span>
                </div>
            </div>

            <button type="submit" class="btn-submit">✅ Enregistrer le Stand</button>

            <div class="action-row" style="margin-top: 15px;">
                <a href="<?php echo $backUrl; ?>" class="btn-secondary" style="width: 100%; background: #0f766e; padding: 14px 24px;">📋 Voir la liste des stands (Front)</a>
            </div>

        </form>
    </div><!-- /form-page -->
    </div><!-- /padding-top wrapper -->
    
    <script src="controleStand.js"></script>
</body>

</html>
