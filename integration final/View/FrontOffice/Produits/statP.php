<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infographie Stocks | BarchaThon</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg-color: #9cd1d1; --sleeve-bg: rgba(255, 255, 255, 0.4); --header-color: #3e7070; }
        * { box-sizing: border-box; transition: all 0.5s ease-in-out; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-color); margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .infographic-card { background: #9cd1d1; width: 95%; max-width: 800px; padding: 40px; text-align: center; }
        header h1 { font-size: 3rem; color: var(--header-color); margin: 0; letter-spacing: 5px; font-weight: 800; }
        .header-dots { display: flex; justify-content: center; gap: 10px; margin: 15px 0 60px 0; }
        .dot { width: 12px; height: 12px; border-radius: 2px; }
        .dot.blue { background: #3498db; }
        .dot.teal { background: #1abc9c; }
        .dot.pink { background: #e91e63; }
        .dot.orange { background: #f39c12; }
        .dot.purple { background: #9b59b2; }
        .chart-container { display: flex; justify-content: space-around; align-items: flex-end; height: 400px; padding-top: 50px; border-top: 2px solid rgba(0, 0, 0, 0.1); border-bottom: 2px solid rgba(0, 0, 0, 0.1); margin-bottom: 30px; }
        .column { width: 150px; display: flex; flex-direction: column; align-items: center; height: 100%; position: relative; }
        .value-label { font-size: 2.2rem; font-weight: 800; color: #555; margin-bottom: 10px; white-space: nowrap; }
        .sleeve { background: var(--sleeve-bg); width: 100%; height: 100%; border-radius: 5px 5px 0 0; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05); }
        .bar { width: 100%; height: 0%; position: relative; display: flex; flex-direction: column; justify-content: flex-end; padding: 15px; color: white; transition: height 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .bar-top-line { position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: rgba(255, 255, 255, 0.5); }
        .icon-container { margin-top: 20px; font-size: 3rem; color: white; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); }
        .label-name { margin-top: 10px; font-size: 1rem; color: var(--header-color); font-weight: 800; text-transform: uppercase; }
        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 50px;
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
        .color-dispo { background-color: #27ae60; }
        .color-rupture { background-color: #e74c3c; }
    </style>
</head>
<body>
    <div class="infographic-card">
        <header><h1>INFOGRAPHIC</h1><div class="header-dots"><div class="dot blue"></div><div class="dot teal"></div><div class="dot pink"></div><div class="dot orange"></div><div class="dot purple"></div></div></header>
        <div class="chart-container">
            <div class="column"><div class="value-label" id="val-dispo">0</div><div class="sleeve"><div class="bar color-dispo" id="bar-dispo"><div class="bar-top-line"></div></div></div><div class="icon-container"><i class="fas fa-box-open"></i></div><div class="label-name">En Stock</div></div>
            <div class="column"><div class="value-label" id="val-rupture" style="margin-left: -50px">0</div><div class="sleeve"><div class="bar color-rupture" id="bar-rupture"><div class="bar-top-line"></div></div></div><div class="icon-container"><i class="fas fa-exclamation-triangle"></i></div><div class="label-name">Rupture</div></div>
        </div>

    </div>
    <script>
        function fetchStats() {
            fetch('getStatsProduits.php')
                .then(r => r.text())
                .then(text => {
                    const [dispo, rupture] = text.split(':');
                    const d = parseInt(dispo);
                    const r = parseInt(rupture);
                    const total = d + r;
                    const max = Math.max(d, r, 1);
                    const pD = total > 0 ? Math.round((d/total)*100) : 0;
                    const pR = total > 0 ? Math.round((r/total)*100) : 0;
                    document.getElementById('bar-dispo').style.height = (d/max)*100 + '%';
                    document.getElementById('val-dispo').innerText = pD + '%';
                    document.getElementById('val-dispo').style.fontSize = '2.2rem';
                    document.getElementById('bar-rupture').style.height = (r/max)*100 + '%';
                    document.getElementById('val-rupture').innerText = pR + '%';
                    document.getElementById('val-rupture').style.fontSize = '2.2rem';
                });
        }
        fetchStats();
        setInterval(fetchStats, 10000);
    </script>
</body>
</html>
