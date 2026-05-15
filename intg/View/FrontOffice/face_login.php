<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/UserController.php';

$ctrl = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['descriptor'])) {
    header('Content-Type: application/json');
    $username = trim($_POST['username']);
    $incoming = json_decode($_POST['descriptor'], true);

    if (!$username || !is_array($incoming) || count($incoming) !== 128) {
        echo json_encode(['ok' => false, 'error' => 'Requete invalide.']);
        exit;
    }

    $row = $ctrl->getFaceDescriptor($username);
    if (!$row) {
        echo json_encode(['ok' => false, 'error' => 'Utilisateur introuvable.']);
        exit;
    }
    if (empty($row['face_descriptor'])) {
        echo json_encode(['ok' => false, 'error' => 'Aucun visage enregistre pour ce compte.']);
        exit;
    }
    if (($row['status'] ?? 'active') === 'banned') {
        echo json_encode(['ok' => false, 'error' => 'Votre compte a ete bloque.']);
        exit;
    }

    $stored = json_decode($row['face_descriptor'], true);
    if (!is_array($stored) || count($stored) !== 128) {
        echo json_encode(['ok' => false, 'error' => 'Descripteur stocke invalide.']);
        exit;
    }

    $sum = 0.0;
    for ($i = 0; $i < 128; $i++) {
        $d = (float)$incoming[$i] - (float)$stored[$i];
        $sum += $d * $d;
    }
    $distance = sqrt($sum);
    $threshold = 0.5;

    if ($distance > $threshold) {
        echo json_encode(['ok' => false, 'error' => 'Visage non reconnu.', 'distance' => $distance]);
        exit;
    }

    $_SESSION['user'] = [
        'id'       => $row['id_user'],
        'id_user'  => $row['id_user'],
        'username' => $row['nom_user'],
        'nom'      => $row['nom_complet'],
        'role'     => $row['role'],
        'email'    => $row['email'],
        'profile_picture' => $row['profile_picture'],
    ];
    $redirect = ($row['role'] === 'admin') ? '../BackOffice/dashboard.php' : 'accueil.php';
    echo json_encode(['ok' => true, 'redirect' => $redirect, 'distance' => $distance]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Face ID — BarchaThon</title>
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');</script>
    <style>
        html[data-theme="dark"] body { background:#0f172a !important; color:#e2e8f0; }
        html[data-theme="dark"] .card { background:#1e293b !important; box-shadow:0 14px 40px rgba(0,0,0,.4) !important; }
        html[data-theme="dark"] h1 { color:#e2e8f0; }
        html[data-theme="dark"] .muted { color:#94a3b8; }
        html[data-theme="dark"] label { color:#e2e8f0; }
        html[data-theme="dark"] input[type=text] { background:#162032 !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.1) !important; }
        html[data-theme="dark"] .btn-secondary { background:rgba(255,255,255,0.06) !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.1) !important; }
    </style>
    <style>
        body { background:linear-gradient(180deg,#fefaf0,#f4fbfb); min-height:100vh; margin:0; font-family:"Segoe UI",sans-serif; color:#102a43; display:flex; align-items:center; justify-content:center; padding:20px; }
        .card { background:#fff; border-radius:24px; padding:36px; box-shadow:0 14px 40px rgba(16,42,67,.10); width:100%; max-width:500px; }
        h1 { font-size:1.6rem; margin:0 0 10px; }
        .muted { color:#627d98; font-size:.92rem; margin-bottom:18px; }
        label { display:block; font-weight:700; margin-bottom:6px; font-size:.9rem; }
        input[type=text] { width:100%; border:1.5px solid #cbd5e1; border-radius:12px; padding:12px 15px; font-size:.95rem; margin-bottom:14px; }
        input[type=text]:focus { outline:none; border-color:#0f766e; box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .video-box { position:relative; width:100%; aspect-ratio:4/3; background:#0f172a; border-radius:16px; overflow:hidden; margin-bottom:14px; }
        video, canvas { position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; }
        .msg { padding:10px 14px; border-radius:10px; margin-bottom:14px; font-size:.9rem; }
        .msg.info { background:#e0f2fe; color:#075985; }
        .msg.success { background:#ecfdf5; color:#065f46; }
        .msg.error { background:#fef2f2; color:#b42318; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; }
        .btn { padding:12px 20px; border-radius:12px; font-weight:700; border:none; cursor:pointer; font-size:.95rem; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .btn-primary { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; flex:1; }
        .btn-secondary { background:#f1f5f9; color:#102a43; border:1px solid #cbd5e1; }
        .btn[disabled] { opacity:.5; cursor:not-allowed; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Connexion par reconnaissance faciale</h1>
        <p class="muted">Entrez votre nom d'utilisateur, autorisez la camera et cliquez sur <strong>Scanner mon visage</strong>.</p>

        <label for="username">Nom d'utilisateur ou email</label>
        <input id="username" type="text" placeholder="Nom d'utilisateur ou email" autocomplete="username">

        <div id="status" class="msg info">Chargement des modeles...</div>

        <div class="video-box">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="overlay"></canvas>
        </div>

        <div class="actions">
            <button id="scan" class="btn btn-primary" disabled>Scanner mon visage</button>
            <a class="btn btn-secondary" href="login.php">Connexion par mot de passe</a>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script defer>
        const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const statusEl = document.getElementById('status');
        const scanBtn = document.getElementById('scan');
        const usernameInput = document.getElementById('username');

        function setStatus(type, text) {
            statusEl.className = 'msg ' + type;
            statusEl.textContent = text;
        }

        let FACE_OPTS;

        async function init() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                FACE_OPTS = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.3 });
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    overlay.width = video.videoWidth;
                    overlay.height = video.videoHeight;
                    setStatus('info', 'Camera prete. Entrez votre nom d\'utilisateur puis scannez.');
                    scanBtn.disabled = false;
                    drawLoop();
                };
            } catch (err) {
                setStatus('error', 'Erreur : ' + (err.message || err));
            }
        }

        async function drawLoop() {
            if (video.paused || video.ended) return;
            const detection = await faceapi.detectSingleFace(video, FACE_OPTS);
            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);
            if (detection) {
                const box = detection.box;
                ctx.strokeStyle = '#14b8a6';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
            }
            requestAnimationFrame(drawLoop);
        }

        async function detectWithRetry(attempts) {
            for (var i = 0; i < attempts; i++) {
                var result = await faceapi
                    .detectSingleFace(video, FACE_OPTS)
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                if (result) return result;
                await new Promise(function(r){ setTimeout(r, 400); });
            }
            return null;
        }

        scanBtn.addEventListener('click', async () => {
            const username = usernameInput.value.trim();
            if (!username) {
                setStatus('error', 'Veuillez entrer votre nom d\'utilisateur ou email.');
                return;
            }
            scanBtn.disabled = true;
            setStatus('info', 'Scan en cours...');
            try {
                const result = await detectWithRetry(5);
                if (!result) {
                    setStatus('error', 'Aucun visage detecte. Assurez-vous d\'etre bien eclaire et face a la camera.');
                    scanBtn.disabled = false;
                    return;
                }
                const descriptor = Array.from(result.descriptor);
                const body = new URLSearchParams();
                body.append('username', username);
                body.append('descriptor', JSON.stringify(descriptor));
                const res = await fetch('face_login.php', { method: 'POST', body });
                const data = await res.json();
                if (data.ok) {
                    setStatus('success', 'Connexion reussie ! Redirection...');
                    setTimeout(() => { window.location.href = data.redirect; }, 800);
                } else {
                    setStatus('error', data.error || 'Echec de la connexion.');
                    scanBtn.disabled = false;
                }
            } catch (err) {
                setStatus('error', 'Erreur : ' + (err.message || err));
                scanBtn.disabled = false;
            }
        });

        window.addEventListener('load', init);
    </script>
</body>
</html>
