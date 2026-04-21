<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'fpdf.php';
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../FrontOffice/partials/session.php';

if (!isConnected() || !isAdmin()) {
    ob_end_clean();
    header('Location: ../../FrontOffice/login.php');
    exit;
}

$ctrl = new MarathonController();

// Filtrage selon les params GET transmis depuis le dashboard
$searchM = trim($_GET['search'] ?? '');
$filterRegion = trim($_GET['region'] ?? '');
if ($searchM !== '') {
    $marathons = $ctrl->rechercherMarathon($searchM);
} elseif ($filterRegion !== '') {
    $marathons = $ctrl->filtrerMarathon($filterRegion);
} else {
    $marathons = $ctrl->afficherMarathon();
}

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

    // Image lookup robuste : on cherche le fichier dans tous les dossiers possibles
    $imgField = $m['image_marathon'] ?? '';
    $imgPath  = '';
    if (!empty($imgField)) {
        $filename = basename($imgField);
        // Chercher recursivement dans tout le projet
        $base = realpath(__DIR__ . '/../../..');
        $candidates = [
            __DIR__ . '/../images/uploads/' . $filename,
            __DIR__ . '/images/uploads/' . $filename,
            $base . '/View/FrontOffice/images/uploads/' . $filename,
            $base . '/View/FrontOffice/marathon/images/uploads/' . $filename,
            $base . '/View/FrontOffice/parcours/images/uploads/' . $filename,
        ];
        // Si le champ contient deja un chemin complet type "images/uploads/xxx"
        $candidates[] = __DIR__ . '/../' . $imgField;
        $candidates[] = $base . '/View/FrontOffice/' . $imgField;
        foreach ($candidates as $c) {
            if (file_exists($c) && !is_dir($c)) { $imgPath = $c; break; }
        }
        // DEBUG : decommenter pour voir dans les logs Apache/PHP
        // error_log("MARATHON IMG | field=[" . $imgField . "] found=[" . $imgPath . "]");
    }
    $imgX = $x + $colW[0] + 1;
    $imgY = $y + 1;
    $imgW = $colW[1] - 2;
    $imgH = $rowH - 2;
    if ($imgPath !== '') {
        try { $pdf->Image($imgPath, $imgX, $imgY, $imgW, $imgH); } catch(Exception $e){
            $pdf->SetFillColor(220, 230, 240);
            $pdf->Rect($imgX, $imgY, $imgW, $imgH, 'F');
            $pdf->SetFont('Arial', 'I', 6);
            $pdf->SetTextColor(120, 140, 160);
            $pdf->SetXY($imgX, $imgY + $imgH/2 - 2);
            $pdf->Cell($imgW, 4, 'No image', 0, 0, 'C');
        }
    } else {
        $pdf->SetFillColor(235, 240, 245);
        $pdf->Rect($imgX, $imgY, $imgW, $imgH, 'F');
        $pdf->SetFont('Arial', 'I', 6.5);
        $pdf->SetTextColor(150, 160, 175);
        $pdf->SetXY($imgX, $imgY + $imgH/2 - 2);
        $pdf->Cell($imgW, 4, utf8_decode('Pas d\'image'), 0, 0, 'C');
    }
    // Remettre le curseur apres la colonne image pour continuer les cellules
    $pdf->SetXY($x + $colW[0] + $colW[1], $y);
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(16, 42, 67);
    $pdf->SetFillColor($even ? 240 : 255, $even ? 248 : 255, $even ? 255 : 255);

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