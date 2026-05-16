<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'fpdf.php';
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../BackOffice/partials/session.php';

if (!isConnected() || !isAdmin()) {
    ob_end_clean();
    header('Location: ../../FrontOffice/login.php');
    exit;
}

$pCtrl = new ParcoursController();
$marathonCtrl = new MarathonController();
$marathonId = isset($_GET['marathon_id']) ? (int)$_GET['marathon_id'] : 0;
$filterDiff = trim($_GET['difficulte'] ?? '');

// Un marathon_id est obligatoire : on exporte toujours les parcours d'un seul marathon
if ($marathonId <= 0) {
    ob_end_clean();
    header('Location: ../BackOffice/dashboard.php?tab=marathons');
    exit;
}

$marathon = $marathonCtrl->showMarathon($marathonId);
if (!$marathon) {
    ob_end_clean();
    header('Location: ../BackOffice/dashboard.php?tab=marathons');
    exit;
}

$tous = $pCtrl->afficherParcours();
$parcours = array_values(array_filter($tous, fn($p) => (int)$p['id_marathon'] === $marathonId));
if ($filterDiff !== '') {
    $parcours = array_values(array_filter($parcours, fn($p) => $p['difficulte'] === $filterDiff));
}
$titreDoc = utf8_decode('Parcours - ' . ($marathon['nom_marathon'] ?? '#' . $marathonId));

if (ob_get_length()) ob_clean();

class ParcoursPDF extends FPDF {
    var $docTitle = '';
    function Header() {
        // Dark header bar
        $this->SetFillColor(16, 42, 67);
        $this->Rect(0, 0, 298, 22, 'F');
        // Title
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(0, 5);
        $this->Cell(298, 12,  $this->docTitle, 0, 0, 'C');
        // Date
        $this->SetFont('Arial', '', 8);
        $this->SetXY(0, 14);
        $this->Cell(290, 8, 'Export du ' . date('d/m/Y'), 0, 0, 'R');
        $this->Ln(16);
    }
    function Footer() {
        $this->SetY(-12);
        $this->SetFillColor(16, 42, 67);
        $this->Rect(0, 285, 298, 12, 'F');
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(160, 190, 210);
        $this->SetXY(0, 286);
        $this->Cell(298, 8, utf8_decode('BarchaThon — Gestion des parcours   |   Page ') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new ParcoursPDF('L', 'mm', 'A4');
$pdf->docTitle = $titreDoc;
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

// ─── Table header ─────────────────────────────────────────────────────────────
$colW = [12, 55, 48, 48, 30, 30, 50];
$headers = ['ID', 'Nom du Parcours', utf8_decode('Départ'), utf8_decode('Arrivée'), 'Dist. (km)', utf8_decode('Difficulté'), 'Marathon'];

$pdf->SetFillColor(16, 42, 67);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetDrawColor(255, 255, 255);
$pdf->SetLineWidth(0.3);
foreach ($headers as $i => $h) {
    $pdf->Cell($colW[$i], 11, $h, 1, 0, 'C', true);
}
$pdf->Ln();

// ─── Table rows ───────────────────────────────────────────────────────────────
$pdf->SetDrawColor(210, 220, 230);
$pdf->SetLineWidth(0.2);
$rowH = 10;
$even = false;

$diffColors = [
    'facile'    => [209,250,229, 6,95,70,  'Facile'],
    'moyen'     => [254,249,195, 146,64,14,'Moyen'],
    'difficile' => [254,226,226, 153,27,27,'Difficile'],
];

foreach ($parcours as $p) {
    if ($pdf->GetY() + $rowH > 195) {
        $pdf->AddPage();
        $pdf->SetFillColor(16,42,67); $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial','B',9); $pdf->SetDrawColor(255,255,255);
        foreach ($headers as $i => $h) { $pdf->Cell($colW[$i],11,$h,1,0,'C',true); }
        $pdf->Ln(); $pdf->SetDrawColor(210,220,230);
    }

    $diff = $p['difficulte'] ?? 'moyen';
    $dc   = $diffColors[$diff] ?? $diffColors['moyen'];

    $pdf->SetFillColor($even ? 245 : 255, $even ? 248 : 255, $even ? 252 : 255);
    $pdf->SetTextColor(30, 50, 70);
    $pdf->SetFont('Arial', '', 8.5);
    $even = !$even;

    $pdf->Cell($colW[0], $rowH, $p['id_parcours'], 1, 0, 'C', true);
    $pdf->Cell($colW[1], $rowH, utf8_decode($p['nom_parcours']), 1, 0, 'L', true);
    $pdf->Cell($colW[2], $rowH, utf8_decode($p['point_depart']), 1, 0, 'L', true);
    $pdf->Cell($colW[3], $rowH, utf8_decode($p['point_arrivee']), 1, 0, 'L', true);

    // Distance with teal color
    $pdf->SetTextColor(15, 118, 110);
    $pdf->SetFont('Arial','B',8.5);
    $pdf->Cell($colW[4], $rowH, number_format((float)$p['distance'],2), 1, 0, 'C', true);
    $pdf->SetFont('Arial','',8.5);

    // Difficulty badge with color
    $pdf->SetFillColor($dc[0],$dc[1],$dc[2]);
    $pdf->SetTextColor($dc[3],$dc[4],$dc[5]);
    $pdf->Cell($colW[5], $rowH, utf8_decode($dc[6]), 1, 0, 'C', true);

    $pdf->SetFillColor($even ? 250 : 255, $even ? 250 : 255, 255);
    $pdf->SetTextColor(30,50,70);
    $pdf->Cell($colW[6], $rowH, utf8_decode($p['nom_marathon'] ?? '-'), 1, 1, 'L', true);
}


$pdf->Output('D', 'BarchaThon_Parcours.pdf');
exit;
