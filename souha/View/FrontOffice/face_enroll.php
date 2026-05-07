<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/UserController.php';

if (!isConnected()) { header('Location: login.php'); exit; }

$ctrl = new UserController();
$userId = getUserId();
$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descriptor'])) {
    $descJson = $_POST['descriptor'];
    $arr = json_decode($descJson, true);
    if (is_array($arr) && count($arr) === 128) {
        $ctrl->saveFaceDescriptor($userId, $descJson);
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Descripteur invalide.']);
    }
    exit;
}

$u = $ctrl->showUser($userId);
$hasFace = !empty($u['face_descriptor']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurer Face ID — BarchaThon</title>
    <script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'light');</script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        html[data-theme="dark"] body { background:#0f172a !important; color:#e2e8f0; }
        html[data-theme="dark"] .wrap { background:#1e293b !important; border:1px solid rgba(255,255,255,0.08) !important; }
        html[data-theme="dark"] h1 { color:#e2e8f0; }
        html[data-theme="dark"] .muted { color:#94a3b8; }
        html[data-theme="dark"] .btn-secondary { background:rgba(255,255,255,0.06) !important; color:#e2e8f0 !important; border-color:rgba(255,255,255,0.1) !important; }
    </style>
    <style>
        body { background:linear-gradient(180deg,#fefaf0,#f4fbfb); min-height:100vh; margin:0; font-family:"Segoe UI",sans-serif; color:#102a43; padding:20px; }
        .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:24px; padding:36px; box-shadow:0 14px 40px rgba(16,42,67,.10); }
        h1 { font-size:1.6rem; margin:0 0 10px; }
        .muted { color:#627d98; font-size:.92rem; margin-bottom:20px; }
        .video-box { position:relative; width:100%; aspect-ratio:4/3; background:#0f172a; border-radius:16px; overflow:hidden; margin-bottom:16px; }
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
    <div class="wrap">
        <h1>Configurer la connexion Face ID</h1>
        <p class="muted">Autorisez l'acces a votre camera, positionnez votre visage dans le cadre et cliquez sur <strong>Enregistrer mon visage</strong>. Cela permettra de vous connecter sans mot de passe.</p>

        <?php if ($hasFace): ?>
            <div class="msg success">Un visage est deja enregistre pour votre compte. Vous pouvez le remplacer en recapturant.</div>
        <?php endif; ?>

        <div id="status" class="msg info">Chargement des modeles de reconnaissance faciale...</div>

        <div class="video-box">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="overlay"></canvas>
        </div>

        <div class="actions">
            <button id="capture" class="btn btn-primary" disabled>Enregistrer mon visage</button>
            <a class="btn btn-secondary" href="profile.php">Retour au profil</a>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script defer>
        const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const statusEl = document.getElementById('status');
        const captureBtn = document.getElementById('capture');

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
                setStatus('info', 'Modeles charges. Demande d\'acces a la camera...');
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    overlay.width = video.videoWidth;
                    overlay.height = video.videoHeight;
                    setStatus('info', 'Camera prete. Positionnez votre visage et cliquez sur Enregistrer.');
                    captureBtn.disabled = false;
                    drawLoop();
                };
            } catch (err) {
                setStatus('error', 'Erreur : ' + (err.message || err));
            }
        }

        async function drawLoop() {
            if (video.paused || video.ended) return;
            const detection = await faceapi
                .detectSingleFace(video, FACE_OPTS)
                .withFaceLandmarks();
            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);
            if (detection) {
                const box = detection.detection.box;
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

        captureBtn.addEventListener('click', async () => {
            captureBtn.disabled = true;
            setStatus('info', 'Capture en cours...');
            try {
                const result = await detectWithRetry(5);
                if (!result) {
                    setStatus('error', 'Aucun visage detecte. Assurez-vous d\'etre bien eclaire et face a la camera, puis reessayez.');
                    captureBtn.disabled = false;
                    return;
                }
                const descriptor = Array.from(result.descriptor);
                const body = new URLSearchParams();
                body.append('descriptor', JSON.stringify(descriptor));
                const res = await fetch('face_enroll.php', { method: 'POST', body });
                const data = await res.json();
                if (data.ok) {
                    setStatus('success', 'Visage enregistre avec succes ! Vous pouvez desormais vous connecter par reconnaissance faciale.');
                } else {
                    setStatus('error', data.error || 'Erreur lors de l\'enregistrement.');
                    captureBtn.disabled = false;
                }
            } catch (err) {
                setStatus('error', 'Erreur : ' + (err.message || err));
                captureBtn.disabled = false;
            }
        });

        window.addEventListener('load', init);
    </script>
</body>
</html>
