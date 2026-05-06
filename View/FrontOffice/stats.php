<?php
require_once "../../Controller/InscriptionController.php";

$controller = new InscriptionController();

$stats = $controller->getStats();
$pay = $controller->getPaidStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    
    <link rel="stylesheet" href="inscription.css">
</head>

<body>

<div class="page-shell">

    
    <header class="topbar">
        <div class="brand">
            <span class="brand-mark">BT</span>
            <div>
                <strong>BarchaThon</strong>
                <small>Front Office</small>
            </div>
        </div>

        <nav class="nav-links">
            <a href="inscription.php">Inscription</a>
            <a href="dossard.php">Dossard</a>
            <a href="stats.php">Statistiques</a>
            
        </nav>

        <div class="user-badge">Organisateur</div>
    </header>

    
    <main class="content-grid">

        <section class="card card-form">

            <div class="card-title">
                <div>
                    <h1>📊 Dashboard Organisateur</h1>
                    <p>Analyse des inscriptions et des paiements</p>
                </div>
            </div>

            
            <div class="grid">

                <div class="card">
                    <h2>Total Inscriptions</h2>
                    <h1><?php echo $stats['totalInscriptions']; ?></h1>
                </div>

                <div class="card">
                    <h2>Total Participants</h2>
                    <h1><?php echo $stats['totalParticipants']; ?></h1>
                </div>

            </div>

            
            <div class="grid">

                <div class="card">
                    <h2>Répartition des parcours</h2>
                    <canvas id="chartParcours"></canvas>
                </div>

                <div class="card">
                    <h2>Modes de paiement</h2>
                    <canvas id="chartPaiement"></canvas>
                </div>

            </div>
            <div class="grid">

    <div class="card">
        <h2>Payé</h2>
        <h1 style="color:green;"><?php echo $pay['paid']; ?></h1>
    </div>

    <div class="card">
        <h2>Non payé</h2>
        <h1 style="color:red;"><?php echo $pay['unpaid']; ?></h1>
    </div>

</div>

        </section>

    </main>
</div>

<script>

const parcoursData = <?php echo json_encode($stats['parcours']); ?>;

const labelsParcours = parcoursData.map(p => {
    if(p.id_parcours == 1) return "10 km";
    if(p.id_parcours == 2) return "21 km";
    return "42 km";
});

const valuesParcours = parcoursData.map(p => p.total);

new Chart(document.getElementById('chartParcours'), {
    type: 'pie',
    data: {
        labels: labelsParcours,
        datasets: [{
            data: valuesParcours
        }]
    }
});


const paiementData = <?php echo json_encode($stats['paiement']); ?>;

const labelsPaiement = paiementData.map(p => p.mode_de_paiement);
const valuesPaiement = paiementData.map(p => p.total);

new Chart(document.getElementById('chartPaiement'), {
    type: 'bar',
    data: {
        labels: labelsPaiement,
        datasets: [{
            data: valuesPaiement
        }]
    }
});
</script>

</body>
</html>