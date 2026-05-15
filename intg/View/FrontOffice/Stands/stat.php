<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infographie Statistiques | BarchaThon</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js CDN (Loaded in head to avoid 'not defined' errors) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --bg-color: #9cd1d1;
            --card-bg: rgba(255, 255, 255, 0.2);
            --header-color: #3e7070;
            --accent-blue: #3498db;
            --accent-teal: #1abc9c;
            --accent-pink: #e91e63;
            --accent-yellow: #f1c40f;
            --accent-purple: #9b59b2;
            --accent-orange: #e67e22;
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 80%);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--header-color);
        }

        .infographic-container {
            width: 95%;
            max-width: 1200px;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        header h1 {
            font-size: 3.5rem;
            color: var(--header-color);
            margin: 0;
            letter-spacing: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .header-dots {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 15px 0 50px 0;
        }

        .dot { width: 14px; height: 14px; border-radius: 4px; }
        .dot.blue { background: var(--accent-blue); }
        .dot.teal { background: var(--accent-teal); }
        .dot.pink { background: var(--accent-pink); }
        .dot.orange { background: var(--accent-orange); }
        .dot.purple { background: var(--accent-purple); }

        /* Leader Spotlight */
        .spotlight-card {
            background: linear-gradient(135deg, #ffd700 0%, #f39c12 100%);
            padding: 25px 50px;
            border-radius: 25px;
            box-shadow: 0 15px 45px rgba(243, 156, 18, 0.4);
            display: inline-block;
            margin-bottom: 50px;
            border: 5px solid white;
            position: relative;
            transform: scale(1);
            animation: pulse 2s infinite ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .spotlight-card h2 {
            color: white;
            font-size: 2.2rem;
            margin: 5px 0;
            font-weight: 800;
        }

        .spotlight-badge {
            background: rgba(255,255,255,0.25);
            padding: 6px 20px;
            border-radius: 50px;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        /* Stats Grid Layout */
        .stats-layout {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 30px;
            align-items: stretch;
            margin-bottom: 40px;
        }

        @media (max-width: 1000px) {
            .stats-layout { grid-template-columns: 1fr; }
        }

        /* Circular Distribution Chart Box */
        .distribution-box {
            background: #ffffff;
            border-radius: 40px;
            padding: 35px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .distribution-box h3 {
            margin: 0 0 25px 0;
            font-size: 1.1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--header-color);
        }

        .chart-wrapper {
            width: 100%;
            height: 350px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-center-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
            z-index: 10;
        }

        .chart-center-icon i {
            font-size: 3rem;
            color: var(--header-color);
            margin-bottom: 5px;
            opacity: 0.8;
        }

        .chart-center-icon span {
            display: block;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }

        /* Column Chart Styling */
        .columns-box {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 40px;
            padding: 30px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            min-height: 450px;
            gap: 15px;
        }

        .column {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            position: relative;
        }

        .value-label {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 12px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .sleeve {
            background: rgba(255, 255, 255, 0.3);
            width: 50px;
            height: 300px;
            border-radius: 50px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .bar {
            width: 100%;
            height: 0%;
            border-radius: 50px;
            transition: height 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 -5px 15px rgba(0,0,0,0.2);
        }

        .icon-cap {
            margin-top: 15px;
            background: white;
            color: var(--header-color);
            width: 50px; height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .stand-name {
            margin-top: 12px;
            font-size: 0.75rem;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            max-width: 80px;
            line-height: 1.2;
        }

        /* Error Message Overlay */
        #error-overlay {
            display: none;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);
            border-left: 8px solid #e74c3c;
            color: #e74c3c;
            font-weight: 800;
            z-index: 100;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { transform: translate(-50%, 50px); opacity: 0; }
            to { transform: translate(-50%, 0); opacity: 1; }
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            background: white;
            color: var(--header-color);
            text-decoration: none;
            padding: 18px 45px;
            border-radius: 50px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .btn-nav:hover {
            transform: translateY(-5px);
            background: var(--header-color);
            color: white;
        }

        /* Colors */
        .c-1 { background: #3498db; } .c-2 { background: #1abc9c; } 
        .c-3 { background: #e91e63; } .c-4 { background: #f1c40f; }
        .c-5 { background: #9b59b2; } .c-6 { background: #e67e22; }
        .c-7 { background: #16a085; } .c-8 { background: #2c3e50; }

    </style>
</head>
<body>

    <div class="infographic-container">
        <header>
            <h1>INFOGRAPHIC</h1>
            <div class="header-dots">
                <div class="dot blue"></div>
                <div class="dot teal"></div>
                <div class="dot pink"></div>
                <div class="dot orange"></div>
                <div class="dot purple"></div>
            </div>
        </header>

        <!-- Leader Card -->
        <div id="leader-spotlight" style="display:none;">
            <div class="spotlight-card">
                <div style="font-size: 3rem; margin-bottom: 10px;">🏆</div>
                <div class="spotlight-badge">Stand le plus actif</div>
                <h2 id="top-stand-name">Chargement...</h2>
                <div style="color: rgba(255,255,255,0.9); font-weight: 600;">
                    <i class="fas fa-box-open"></i> <span id="top-stand-count">0</span> Produits enregistrés
                </div>
            </div>
        </div>

        <!-- Main Stats Grid -->
        <div class="stats-layout">
            <!-- Circular Stock Chart -->
            <div class="distribution-box">
                <h3>Répartition du Stock</h3>
                <div class="chart-wrapper">
                    <div class="chart-center-icon">
                        <i class="fas fa-chart-pie"></i>
                        <span>Statistiques</span>
                    </div>
                    <canvas id="distributionChart"></canvas>
                </div>
                <div id="chart-legend" style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%;"></div>
            </div>

            <!-- Bar Chart Columns -->
            <div class="columns-box" id="columnsContainer">
                <div style="color:white; font-weight:800; opacity:0.6;">Préparation de l'infographie...</div>
            </div>
        </div>


    </div>

    <!-- Error Alert -->
    <div id="error-overlay"></div>

    <script>
        const chartColors = ['#3498db', '#1abc9c', '#e91e63', '#f1c40f', '#9b59b2', '#e67e22', '#16a085', '#2c3e50'];
        const barIcons = ['fa-at', 'fa-gear', 'fa-dollar-sign', 'fa-comments', 'fa-envelope', 'fa-star', 'fa-bolt', 'fa-flag'];
        let distributionChartInstance = null;

        function updateUI() {
            const errorOverlay = document.getElementById('error-overlay');
            
            fetch('getStatsStands.php')
                .then(response => response.text())
                .then(text => {
                    if (text.startsWith('SUCCESS')) {
                        errorOverlay.style.display = 'none';
                        const dataItems = text.split('|')[1].split(';').filter(x => x).map(item => {
                            const [name, count] = item.split(':');
                            return { name: name, total: parseInt(count) };
                        });
                        
                        processData(dataItems);
                    } else {
                        throw new Error(text.split('|')[1] || "Serveur non disponible");
                    }
                })
                .catch(err => {
                    errorOverlay.style.display = 'block';
                    errorOverlay.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Erreur : ${err.message}`;
                });
        }

        function processData(data) {
            // Sort to find the leader
            const sortedData = [...data].sort((a, b) => b.total - a.total);
            const leader = sortedData[0];

            if (leader && leader.total > 0) {
                document.getElementById('leader-spotlight').style.display = 'block';
                document.getElementById('top-stand-name').innerText = leader.name;
                document.getElementById('top-stand-count').innerText = leader.total;
            }

            renderDistribution(data);
            renderBars(data, leader ? leader.total : 10);
        }

        function renderDistribution(data) {
            const ctx = document.getElementById('distributionChart').getContext('2d');
            
            // Limit to 8 for aesthetic reasons
            const chartData = data.slice(0, 8);
            
            if (distributionChartInstance) {
                distributionChartInstance.destroy();
            }

            distributionChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.map(d => d.name),
                    datasets: [{
                        data: chartData.map(d => d.total),
                        backgroundColor: chartColors,
                        borderWidth: 6,
                        borderColor: '#ffffff',
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });

            // Create custom legend
            const legendDiv = document.getElementById('chart-legend');
            legendDiv.innerHTML = '';
            chartData.forEach((d, i) => {
                const item = document.createElement('div');
                item.style.display = 'flex';
                item.style.alignItems = 'center';
                item.style.gap = '8px';
                item.style.fontSize = '0.75rem';
                item.style.fontWeight = '700';
                item.innerHTML = `<div style="width:10px; height:10px; border-radius:3px; background:${chartColors[i % chartColors.length]}"></div> ${d.name}`;
                legendDiv.appendChild(item);
            });
        }

        function renderBars(data, maxVal) {
            const container = document.getElementById('columnsContainer');
            container.innerHTML = '';
            
            // Limit to top 7 for layout
            const barData = data.slice(0, 7);
            
            barData.forEach((item, index) => {
                const heightPercent = maxVal > 0 ? (item.total / maxVal) * 100 : 0;
                const colorClass = `c-${(index % 8) + 1}`;
                
                const col = document.createElement('div');
                col.className = 'column';
                col.innerHTML = `
                    <div class="value-label">${item.total}</div>
                    <div class="sleeve">
                        <div class="bar ${colorClass}" style="height:${heightPercent}%"></div>
                    </div>
                    <div class="icon-cap">
                        <i class="fas ${barIcons[index % barIcons.length]}"></i>
                    </div>
                    <div class="stand-name">${item.name}</div>
                `;
                container.appendChild(col);
            });
        }

        // Initialize and poll
        window.addEventListener('load', () => {
            updateUI();
            setInterval(updateUI, 10000);
        });
    </script>
</body>
</html>
