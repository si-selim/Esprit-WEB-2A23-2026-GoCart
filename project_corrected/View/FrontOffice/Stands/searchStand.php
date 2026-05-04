<?php
require_once __DIR__ . '/../../Controller/standcontroller.php';

$message = "";
$standTrouve = null;
$recommendations = [];
$messageClass = "";

// Récupérer la valeur du champ ou les coordonnées GPS
$searchVal = isset($_REQUEST['searchVal']) ? trim($_REQUEST['searchVal']) : "";
$userLat = isset($_REQUEST['lat']) ? (float)$_REQUEST['lat'] : null;
$userLon = isset($_REQUEST['lon']) ? (float)$_REQUEST['lon'] : null;

if (!empty($searchVal) || ($userLat !== null && $userLon !== null)) {
    $controller = new StandController();
    
    if ($userLat !== null && $userLon !== null) {
        // Recommandation basée sur la position RÉELLE de l'utilisateur
        $recommendations = $controller->getRecommendationsByCoords($userLat, $userLon);
        $message = "📍 Recommandations autour de votre position actuelle";
        $messageClass = "success-msg";
        $standTrouve = ['nom_stand' => 'Votre Position', 'ID_stand' => 'Moi', 'position' => "$userLat, $userLon", 'ID_parcours' => 'GPS'];
    } else {
        // Recherche classique par nom/ID
        $standTrouve = $controller->searchStand($searchVal);
        if ($standTrouve) {
            $message = "✅ Stand trouvé !";
            $messageClass = "success-msg";
            $recommendations = $controller->getIntelligentRecommendations($standTrouve['ID_stand']);
        } else {
            $message = "❌ Stand introuvable.";
            $messageClass = "error-msg";
        }
    }
} else {
    $message = "Veuillez entrer une recherche ou activer votre géolocalisation.";
    $messageClass = "error-msg";
}
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
    
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        font-family: 'Outfit', sans-serif;
        animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-content {
        background: #ffffff;
        border-radius: 20px;
        padding: 30px;
        width: 90%;
        max-width: 680px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }
    
    .modal-content::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 6px;
        background: linear-gradient(90deg, #10b981, #3b82f6);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .modal-header h3 {
        margin: 0;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.5rem;
        font-weight: 800;
    }

    .modal-header h3 .icon {
        background: #ecfdf5;
        color: #10b981;
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .close-btn {
        background: #f1f5f9;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #64748b;
        font-size: 1.2rem;
        transition: all 0.2s;
    }

    .close-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: rotate(90deg);
    }

    /* Main Result Card */
    .main-result-card {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 30px;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 20px;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    .main-result-card::after {
        content: '✔ Résultat Principal';
        position: absolute;
        top: 12px; right: 12px;
        background: #10b981;
        color: white;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 20px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .stand-badge {
        background: #1e293b;
        color: #f8fafc;
        width: 60px; height: 60px;
        border-radius: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        box-shadow: 0 4px 12px rgba(30, 41, 59, 0.15);
    }
    
    .stand-badge span {
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .stand-details h4 {
        margin: 0 0 5px 0;
        font-size: 1.2rem;
        color: #0f172a;
    }
    
    .stand-details p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Recommendations Section */
    .recommendations-wrapper {
        animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
    }

    .recommendations-title {
        font-size: 1rem;
        font-weight: 800;
        color: #334155;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .recommendations-title span {
        color: #3b82f6;
    }

    .recom-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .recom-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .recom-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -10px rgba(59, 130, 246, 0.25);
        border-color: #bfdbfe;
    }
    
    .recom-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: #3b82f6;
        transform: scaleY(0);
        transition: transform 0.3s ease;
        transform-origin: bottom;
    }
    
    .recom-card:hover::before {
        transform: scaleY(1);
    }

    .recom-icon {
        background: #eff6ff;
        color: #3b82f6;
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .recom-info h5 {
        margin: 0 0 4px 0;
        font-size: 0.95rem;
        color: #1e293b;
        font-weight: 600;
    }

    .recom-info p {
        margin: 0;
        font-size: 0.8rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .error-msg {
        background: #fef2f2;
        color: #ef4444;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 1.05rem;
        border: 1px dashed #fca5a5;
    }

    .badge-parcours {
        background: #e0f2fe;
        color: #0369a1;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
    }
</style>

<div class="modal-overlay">
    <div class="modal-content">
        <?php if ($standTrouve): ?>
            <div class="modal-header">
                <h3><div class="icon">🔍</div> Résultat de recherche</h3>
                <button type="button" class="close-btn" onclick="document.getElementById('searchModalContainer').innerHTML = '';">&times;</button>
            </div>
            
            <!-- Main Result -->
            <div class="main-result-card">
                <div class="stand-badge">
                    <span>ID</span>
                    #<?= htmlspecialchars($standTrouve['ID_stand']) ?>
                </div>
                <div class="stand-details">
                    <h4><?= htmlspecialchars($standTrouve['nom_stand']) ?></h4>
                    <p>
                        📍 <?= htmlspecialchars($standTrouve['position']) ?> 
                        <span class="badge-parcours">Parcours <?= htmlspecialchars($standTrouve['ID_parcours']) ?></span>
                    </p>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="recommendations-wrapper">
                <h4 class="recommendations-title"><span>🔁</span> Recommandations à proximité</h4>
                
                <?php if (!empty($recommendations)): ?>
                    <div class="recom-grid">
                        <?php foreach ($recommendations as $rec): ?>
                            <div class="recom-card" title="<?= htmlspecialchars($rec['description']) ?>">
                                <div class="recom-icon">🏪</div>
                                <div class="recom-info">
                                    <h5><?= htmlspecialchars($rec['nom_stand']) ?></h5>
                                    <p>
                                        📍 Dist: <strong><?= $rec['distance_km'] ?> km</strong> 
                                        <span style="font-size: 0.7rem; color: #94a3b8; margin-left: 5px;">(<?= htmlspecialchars($rec['position']) ?>)</span>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #64748b; font-size: 0.9rem; font-style: italic;">Aucun autre stand à proximité trouvé.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="modal-header">
                <h3>🔍 Résultat de recherche</h3>
                <button type="button" class="close-btn" onclick="document.getElementById('searchModalContainer').innerHTML = '';">&times;</button>
            </div>
            <div class="error-msg"><?= $message ?></div>
        <?php endif; ?>
    </div>
</div>

