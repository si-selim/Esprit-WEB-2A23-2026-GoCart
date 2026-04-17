<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'fpdf.php';
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../FrontOffice/partials/session.php';

if (!isConnected() || (!isAdmin() && !isOrganisateur())) {
    ob_end_clean();
    header('Location: ../../FrontOffice/login.php');
    exit;
}

$ctrl = new MarathonController();
$marathons = $ctrl->afficherMarathon();

if (ob_get_length()) ob_clean();

class MarathonPDF extends FPDF {
    function Header() {
    $this->SetFillColor(16, 42, 67);
    $this->Rect(0, 0, 298, 20, 'F'); // J'ai réduit la hauteur de 22 à 20
    
    // LA LIGNE VERTE A ÉTÉ SUPPRIMÉE ICI
    
    $this->SetFont('Arial', 'B', 14);
    $this->SetTextColor(255, 255, 255);
    $this->SetXY(0, 4);
    $this->Cell(298, 10, utf8_decode('Liste des Marathons'), 0, 0, 'C');
    
    $this->SetFont('Arial', '', 8);
    $this->SetXY(0, 12);
    $this->Cell(290, 8, 'Export du ' . date('d/m/Y'), 0, 0, 'R');
    $this->Ln(12); // Réduction de l'espace après le header
   }
}

$pdf = new MarathonPDF('L', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();


$bW = 67; $bH = 18; $startX = 5;
$y0 = $pdf->GetY();
foreach ($statBoxes as $i => $box) {
    $bx = $startX + $i * ($bW + 3);
    $pdf->SetFillColor($box['r'], $box['g'], $box['b']);
    $pdf->Rect($bx, $y0, $bW, $bH, 'F');
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('Arial','B',8);
    $pdf->SetXY($bx, $y0+2);
    $pdf->Cell($bW, 5, utf8_decode($box['label']), 0, 0, 'C');
    $pdf->SetFont('Arial','B',13);
    $pdf->SetXY($bx, $y0+8);
    $pdf->Cell($bW, 9, $box['value'], 0, 0, 'C');
}
$pdf->Ln($bH + 6);

// ─── Table header ─────────────────────────────────────────────────────────────
$colW = [12, 36, 52, 44, 35, 28, 30, 32];
$headers = ['ID', 'Image', 'Nom', 'Organisateur', utf8_decode('Région'), 'Date', 'Places', 'Prix (TND)'];

$pdf->SetFillColor(16, 42, 67);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8.5);
$pdf->SetDrawColor(255, 255, 255);
$pdf->SetLineWidth(0.3);
foreach ($headers as $i => $h) {
    $pdf->Cell($colW[$i], 11, $h, 1, 0, 'C', true);
}
$pdf->Ln();

// ─── Table rows ───────────────────────────────────────────────────────────────
$pdf->SetDrawColor(210, 220, 230);
$pdf->SetLineWidth(0.2);
$rowH = 20;
$even = false;

foreach ($marathons as $m) {
    if ($pdf->GetY() + $rowH > 195) {
        $pdf->AddPage();
        $pdf->SetFillColor(16, 42, 67); $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial','B',8.5); $pdf->SetDrawColor(255,255,255);
        foreach ($headers as $i => $h) { $pdf->Cell($colW[$i],11,$h,1,0,'C',true); }
        $pdf->Ln(); $pdf->SetDrawColor(210,220,230);
    }

    $x = $pdf->GetX(); $y = $pdf->GetY();
    $pdf->SetTextColor(16, 42, 67);
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetFillColor($even ? 240 : 255, $even ? 248 : 255, $even ? 255 : 255);
    $even = !$even;

    $pdf->Cell($colW[0], $rowH, $m['id_marathon'], 1, 0, 'C', true);
    $pdf->Cell($colW[1], $rowH, '', 1, 0, 'C', true);

    // Image lookup — image_marathon contains "images/uploads/filename.png"
    $imgField = $m['image_marathon'] ?? '';
    $imgPath  = '';
    if (!empty($imgField)) {
        $filename = basename($imgField);
        $candidates = [
            __DIR__ . '/../FrontOffice/images/uploads/' . $filename,
            __DIR__ . '/images/uploads/' . $filename,
        ];
        foreach ($candidates as $c) {
            if (file_exists($c) && !is_dir($c)) { $imgPath = $c; break; }
        }
    }
    if ($imgPath !== '') {
        $imgX = $x + $colW[0] + 1;
        $imgY = $y + 1;
        $imgW = $colW[1] - 2;
        $imgH = $rowH - 2;
        try { $pdf->Image($imgPath, $imgX, $imgY, $imgW, $imgH); } catch(Exception $e){}
    }

    $pdf->Cell($colW[2], $rowH, utf8_decode($m['nom_marathon']), 1, 0, 'L', true);
    $pdf->Cell($colW[3], $rowH, utf8_decode($m['organisateur_marathon']), 1, 0, 'L', true);
    $pdf->Cell($colW[4], $rowH, utf8_decode($m['region_marathon']), 1, 0, 'C', true);
    $pdf->Cell($colW[5], $rowH, date('d/m/Y', strtotime($m['date_marathon'])), 1, 0, 'C', true);

    $places_dispo = (int)$m['nb_places_dispo'];
    $pdf->SetTextColor($places_dispo > 0 ? 6 : 185, $places_dispo > 0 ? 95 : 28, $places_dispo > 0 ? 70 : 28);
    $pdf->Cell($colW[6], $rowH, $places_dispo > 0 ? $places_dispo . ' pl.' : 'Complet', 1, 0, 'C', true);

    $pdf->SetTextColor(15, 118, 110);
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Cell($colW[7], $rowH, number_format($m['prix_marathon'], 2) . ' TND', 1, 1, 'C', true);
    $pdf->SetTextColor(16, 42, 67);
    $pdf->SetFont('Arial', '', 8.5);
}

$pdf->Output('D', 'BarchaThon_Marathons.pdf');
exit;
