<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controller/Mailer.php';

try {
    $db = config::getConnexion();
    $now = new DateTime();
    $tomorrowDate = (clone $now)->modify('+1 day')->format('Y-m-d');

    // ==========================================
    // 1. Send 24h emails
    // ==========================================
    $stmt24h = $db->prepare("
        SELECT i.id_inscription, u.email, u.nom_complet, p.id_parcours, p.nom_parcours, p.difficulte, p.distance, p.point_depart, p.point_arrivee, p.heure_depart, m.nom_marathon, m.region_marathon, m.date_marathon
        FROM inscription i
        JOIN user u ON i.id_user = u.id_user
        JOIN parcours p ON i.id_parcours = p.id_parcours
        JOIN marathon m ON p.id_marathon = m.id_marathon
        WHERE i.email_24h_sent = 0
    ");
    $stmt24h->execute();
    $inscriptions24h = $stmt24h->fetchAll();

    $emails24hSent = 0;
    foreach ($inscriptions24h as $row) {
        if ($row['date_marathon'] === $tomorrowDate) {
            $subject = "Votre marathon commence demain – préparez-vous !";
            $parcoursUrl = "http://localhost/integration/intg/View/FrontOffice/detailParcours.php?id=" . $row['id_parcours'] . "#courseModeSection";
            
            $body = "
            <p>Bonjour 👋,</p>
            <p>Nous vous rappelons que votre participation au marathon est prévue pour demain. Voici toutes les informations importantes :</p>
            <p>📌 <strong>Détails du marathon :</strong></p>
            <ul>
                <li><strong>Nom :</strong> {$row['nom_marathon']}</li>
                <li><strong>Région :</strong> {$row['region_marathon']}</li>
                <li><strong>Date :</strong> {$row['date_marathon']}</li>
                <li><strong>Météo prévue :</strong> Idéale pour courir ☀️</li>
            </ul>
            <p>🏃 <strong>Votre parcours choisi :</strong></p>
            <ul>
                <li><strong>Difficulté :</strong> {$row['difficulte']}</li>
                <li><strong>Distance :</strong> {$row['distance']} km</li>
                <li><strong>Départ :</strong> {$row['point_depart']}</li>
                <li><strong>Arrivée :</strong> {$row['point_arrivee']}</li>
                <li><strong>Heure de départ :</strong> {$row['heure_depart']}</li>
            </ul>
            <p>📍 <strong>Lieu et suivi du parcours :</strong><br>
            👉 <a href='{$parcoursUrl}'>Cliquez ici pour voir les détails et commencer la course</a></p>
            <p>🎯 Nous vous conseillons de bien vous hydrater et de vous préparer à l’avance afin de profiter pleinement de votre course.</p>
            <p>Bonne préparation et à demain 💪🏃‍♂️</p>";

            if (Mailer::send($row['email'], $subject, $body)) {
                $update = $db->prepare("UPDATE inscription SET email_24h_sent = 1 WHERE id_inscription = ?");
                $update->execute([$row['id_inscription']]);
                echo "24h email sent to {$row['email']} for inscription {$row['id_inscription']}\n";
                $emails24hSent++;
            } else {
                echo "Failed to send 24h email to {$row['email']}\n";
            }
        }
    }

    // ==========================================
    // 2. Send 1h emails
    // ==========================================
    $stmt1h = $db->prepare("
        SELECT i.id_inscription, u.email, u.nom_complet, p.id_parcours, p.nom_parcours, p.difficulte, p.distance, p.point_depart, p.point_arrivee, p.heure_depart, m.date_marathon
        FROM inscription i
        JOIN user u ON i.id_user = u.id_user
        JOIN parcours p ON i.id_parcours = p.id_parcours
        JOIN marathon m ON p.id_marathon = m.id_marathon
        WHERE i.email_1h_sent = 0
    ");
    $stmt1h->execute();
    $inscriptions1h = $stmt1h->fetchAll();

    $emails1hSent = 0;
    foreach ($inscriptions1h as $row) {
        if (empty($row['heure_depart']) || empty($row['date_marathon'])) continue;
        
        $startDateTimeStr = $row['date_marathon'] . ' ' . $row['heure_depart'];
        $startDateTime = new DateTime($startDateTimeStr);
        
        // diff in seconds
        $diffSeconds = $startDateTime->getTimestamp() - $now->getTimestamp();
        
        // If the start time is within the next 1 hour (3600 seconds) and hasn't started yet
        // and we give a window of say 60 minutes to trigger the email
        if ($diffSeconds > 0 && $diffSeconds <= 3600) {
            $subject = "Départ imminent – votre marathon commence dans 1 heure ⏰";
            $parcoursUrl = "http://localhost/integration/intg/View/FrontOffice/detailParcours.php?id=" . $row['id_parcours'] . "#courseModeSection";
            
            $body = "
            <p>Bonjour 👋,</p>
            <p>C’est le dernier rappel ! Votre marathon commence dans 1 heure.</p>
            <p>📌 <strong>Rappel de votre parcours :</strong></p>
            <ul>
                <li><strong>Difficulté :</strong> {$row['difficulte']}</li>
                <li><strong>Distance :</strong> {$row['distance']} km</li>
                <li><strong>Départ :</strong> {$row['point_depart']}</li>
                <li><strong>Arrivée :</strong> {$row['point_arrivee']}</li>
                <li><strong>Heure de départ :</strong> {$row['heure_depart']}</li>
            </ul>
            <p>📍 <strong>Lieu et suivi du parcours :</strong><br>
            👉 <a href='{$parcoursUrl}'>Cliquez ici pour accéder à votre espace de course et commencer</a></p>
            <p>⚠️ <strong>Important :</strong></p>
            <ul>
                <li>Arrivez sur place au moins 20 à 30 minutes à l’avance</li>
                <li>Vérifiez votre équipement (chaussures, eau, tenue)</li>
                <li>Échauffez-vous correctement avant le départ</li>
            </ul>
            <p>🔥 Bonne course et donnez le meilleur de vous-même !</p>";

            if (Mailer::send($row['email'], $subject, $body)) {
                $update = $db->prepare("UPDATE inscription SET email_1h_sent = 1 WHERE id_inscription = ?");
                $update->execute([$row['id_inscription']]);
                echo "1h email sent to {$row['email']} for inscription {$row['id_inscription']}\n";
                $emails1hSent++;
            } else {
                echo "Failed to send 1h email to {$row['email']}\n";
            }
        }
    }

    echo "Cron job completed successfully. 24h emails sent: $emails24hSent. 1h emails sent: $emails1hSent.\n";

} catch (Exception $e) {
    echo "Error processing cron emails: " . $e->getMessage() . "\n";
}
