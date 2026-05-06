<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/MARATHONS/lib/fpdf186/fpdf.php";
require_once "../../Controller/DossardController.php";

$id       = intval($_GET['id_inscription'] ?? 0);
$ctrl     = new DossardController();
$dossards = $ctrl->getByInscription($id);

// ── Palette ────────────────────────────────────────────────────────────────
define('C_NAVY_R',  11);  define('C_NAVY_G',  32);  define('C_NAVY_B',  50);
define('C_TEAL_R',  15);  define('C_TEAL_G', 118);  define('C_TEAL_B', 110);
define('C_MINT_R', 204);  define('C_MINT_G', 251);  define('C_MINT_B', 241);
define('C_GREY_R', 100);  define('C_GREY_G', 116);  define('C_GREY_B', 139);
define('C_LINE_R', 226);  define('C_LINE_G', 232);  define('C_LINE_B', 240);

// ── Chemin du logo (à adapter si besoin) ──────────────────────────────────
define('LOGO_PATH', $_SERVER['DOCUMENT_ROOT'] . '/MARATHONS/assets/logo.jpg');

class BarchaPDF extends FPDF
{
    public int $inscriptionId = 0;

    function Header()
    {
        $w = $this->GetPageWidth();

        // Navy banner
        $this->SetFillColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);
        $this->Rect(0, 0, $w, 32, 'F');

        // Teal accent stripe gauche
        $this->SetFillColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
        $this->Rect(0, 0, 3, 32, 'F');

        // ── Logo BarchaThon ───────────────────────────────────────────────
        // Le logo a un fond clair → on le met dans un cadre teal arrondi
        // OU on l'affiche directement si le PNG a un fond transparent
        $logoPath = LOGO_PATH;
        $logoH    = 26; // hauteur souhaitée en mm
        $xLogo    = 6;
        $yLogo    = 3;

        if (file_exists($logoPath)) {
            // Cadre teal arrondi derrière le logo (pour le détacher du fond navy)
            $this->SetFillColor(255, 255, 255);
            $this->RoundedRect($xLogo, $yLogo, $logoH * 1.6, $logoH, 4, 'F');
            // Logo par-dessus
            $this->Image($logoPath, $xLogo + 1, $yLogo + 1, $logoH * 1.6 - 2, $logoH - 2);
            $afterLogo = $xLogo + $logoH * 1.6 + 4;
        } else {
            // Fallback badge BT
            $this->SetFillColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
            $this->RoundedRect(6, 6, 20, 20, 4, 'F');
            $this->SetFont('Arial', 'B', 12);
            $this->SetTextColor(255, 255, 255);
            $this->SetXY(6, 11);
            $this->Cell(20, 10, 'BT', 0, 0, 'C');

            // "BarchaThon" texte
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(255, 255, 255);
            $this->SetXY(30, 6);
            $this->Cell(70, 9, 'BarchaThon', 0, 0, 'L');

            $afterLogo = 30;
        }

        // Sub-label
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(167, 243, 208);
        $this->SetXY($afterLogo, 20);
        $this->Cell(60, 6, 'Front Office  -  Export Dossards', 0, 0, 'L');

        // ── Droite : inscription id + date ────────────────────────────────
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY($w - 78, 6);
        $this->Cell(73, 9, 'Inscription #' . $this->inscriptionId, 0, 0, 'R');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(167, 243, 208);
        $this->SetXY($w - 78, 16);
        $this->Cell(73, 6, date('d/m/Y  H:i'), 0, 0, 'R');

        $this->Ln(36);
    }

    function Footer()
    {
        $this->SetY(-14);
        $this->SetDrawColor(C_LINE_R, C_LINE_G, C_LINE_B);
        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(C_GREY_R, C_GREY_G, C_GREY_B);
        $this->Cell(0, 5,
            'Genere automatiquement par BarchaThon Front Office  |  Inscription #' . $this->inscriptionId,
            0, 0, 'C');
        $this->SetXY($this->GetPageWidth() - 25, $this->GetY() - 5);
        $this->Cell(15, 5, 'Page ' . $this->PageNo(), 0, 0, 'R');
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k; $hp = $this->h;
        $op = ($style==='F') ? 'f' : (($style==='FD'||$style==='DF') ? 'B' : 'S');
        $arc = 4/3*(sqrt(2)-1);
        $this->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k));
        $this->_out(sprintf('%.2F %.2F l', ($x+$w-$r)*$k, ($hp-$y)*$k));
        $this->_Arc($x+$w-$r+$r*$arc,$y,$x+$w,$y+$r-$r*$arc,$x+$w,$y+$r);
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-($y+$h-$r))*$k));
        $this->_Arc($x+$w,$y+$h-$r+$r*$arc,$x+$w-$r+$r*$arc,$y+$h,$x+$w-$r,$y+$h);
        $this->_out(sprintf('%.2F %.2F l', ($x+$r)*$k, ($hp-($y+$h))*$k));
        $this->_Arc($x+$r-$r*$arc,$y+$h,$x,$y+$h-$r+$r*$arc,$x,$y+$h-$r);
        $this->_out(sprintf('%.2F %.2F l', $x*$k, ($hp-($y+$r))*$k));
        $this->_Arc($x,$y+$r-$r*$arc,$x+$r-$r*$arc,$y,$x+$r,$y);
        $this->_out($op);
    }

    function _Arc($x1,$y1,$x2,$y2,$x3,$y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1*$this->k,($h-$y1)*$this->k,
            $x2*$this->k,($h-$y2)*$this->k,
            $x3*$this->k,($h-$y3)*$this->k));
    }

    function StatsCard($nb, $withQr, $withoutQr)
    {
        $w   = $this->GetPageWidth() - 20;
        $col = $w / 3;
        $x0  = 10;
        $y0  = $this->GetY();
        $h   = 18;

        foreach ([[$nb,'Dossards'],[$withQr,'Avec QR'],[$withoutQr,'Sans QR']] as $i => [$val,$label]) {
            $x = $x0 + $i * $col;
            $this->SetFillColor(C_MINT_R, C_MINT_G, C_MINT_B);
            $this->SetDrawColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
            $this->SetLineWidth(0.4);
            $this->RoundedRect($x+1, $y0, $col-2, $h, 3, 'FD');
            $this->SetFont('Arial', 'B', 14);
            $this->SetTextColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);
            $this->SetXY($x+1, $y0+2);
            $this->Cell($col-2, 8, $val, 0, 0, 'C');
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(C_GREY_R, C_GREY_G, C_GREY_B);
            $this->SetXY($x+1, $y0+10);
            $this->Cell($col-2, 6, $label, 0, 0, 'C');
        }
        $this->SetY($y0 + $h + 5);
    }

    function SectionTitle($text)
    {
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);
        $this->Cell(0, 8, $text, 0, 1, 'L');
        $this->SetDrawColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
        $this->SetLineWidth(1);
        $this->Line(10, $this->GetY(), 60, $this->GetY());
        $this->SetLineWidth(0.3);
        $this->Ln(3);
    }
}

// ── Build ──────────────────────────────────────────────────────────────────
$pdf = new BarchaPDF();
$pdf->inscriptionId = $id;
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

$pdf->SectionTitle('Liste des Dossards');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(C_GREY_R, C_GREY_G, C_GREY_B);
$pdf->Cell(0, 5, "Recapitulatif complet des dossards associes a l'inscription #$id", 0, 1, 'L');
$pdf->Ln(2);

$nb     = count($dossards);
$withQr = count(array_filter($dossards, fn($d) => !empty($d['qr_code'])));
$pdf->StatsCard($nb, $withQr, $nb - $withQr);

$pdf->SetDrawColor(C_LINE_R, C_LINE_G, C_LINE_B);
$pdf->SetLineWidth(0.3);
$pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth()-10, $pdf->GetY());
$pdf->Ln(4);

// Table header
$cols = [
    ['#',10,'C'], ['Nom',44,'L'], ['Numero',22,'C'],
    ['Taille',20,'C'], ['Couleur',32,'C'], ['QR Code',38,'C'],
];
$pdf->SetFillColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetLineWidth(0);
foreach ($cols as [$label,$w]) $pdf->Cell($w,10,$label,0,0,'C',true);
$pdf->Ln();

// Rows
$rowH = 22; $fill = false;
foreach ($dossards as $i => $d) {
    $yRow = $pdf->GetY();

    // Teal left stripe
    $pdf->SetFillColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
    $pdf->Rect(10, $yRow, 1.5, $rowH, 'F');

    if ($fill) $pdf->SetFillColor(C_MINT_R, C_MINT_G, C_MINT_B);
    else        $pdf->SetFillColor(255,255,255);

    $pdf->SetDrawColor(C_LINE_R, C_LINE_G, C_LINE_B);
    $pdf->SetLineWidth(0.2);
    $pdf->SetTextColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);

    $pdf->SetXY(11.5, $yRow);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(8.5, $rowH, $i+1, 'B', 0,'C',true);

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(44, $rowH, iconv('UTF-8','windows-1252//TRANSLIT',$d['nom']??'—'), 'B',0,'L',true);

    $pdf->SetFont('Arial','',9);
    $pdf->Cell(22, $rowH, $d['numero']??($i+1), 'B',0,'C',true);
    $pdf->Cell(20, $rowH, $d['taille']??'—',   'B',0,'C',true);

    $xColor = $pdf->GetX();
    $pdf->Cell(32, $rowH,'','B',0,'C',true);
    $couleur = $d['couleur'] ?? '';
    if ($couleur) {
        $hex = ltrim($couleur,'#');
        if (strlen($hex)===6) {
            $pdf->SetFillColor(hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2)));
            $pdf->RoundedRect($xColor+4, $yRow+($rowH/2)-4, 8, 8, 2,'F');
        }
        $pdf->SetFont('Arial','',7);
        $pdf->SetTextColor(C_GREY_R,C_GREY_G,C_GREY_B);
        $pdf->SetXY($xColor+13, $yRow+($rowH/2)-3);
        $pdf->Cell(18,6,$couleur,0,0,'L');
        if ($fill) $pdf->SetFillColor(C_MINT_R,C_MINT_G,C_MINT_B);
        else        $pdf->SetFillColor(255,255,255);
        $pdf->SetTextColor(C_NAVY_R,C_NAVY_G,C_NAVY_B);
    }

    $pdf->SetXY($xColor+32, $yRow);
    $qrPath = $_SERVER['DOCUMENT_ROOT'].'/MARATHONS/qr/'.($d['qr_code']??'');
    if (!empty($d['qr_code']) && file_exists($qrPath)) {
        $pdf->Cell(38,$rowH,'','B',0,'C',true);
        $pdf->Image($qrPath, $xColor+32+10, $yRow+2, 18, 18);
    } else {
        $pdf->SetFont('Arial','I',8);
        $pdf->SetTextColor(200,50,50);
        $pdf->Cell(38,$rowH,'Pas de QR','B',0,'C',true);
        $pdf->SetTextColor(C_NAVY_R,C_NAVY_G,C_NAVY_B);
    }

    $pdf->Ln();
    $fill = !$fill;
}

$tableW = array_sum(array_column($cols,1));
$pdf->SetDrawColor(C_LINE_R,C_LINE_G,C_LINE_B);
$pdf->SetLineWidth(0.5);
$pdf->Line(10,$pdf->GetY(),10+$tableW,$pdf->GetY());

$pdf->Output('I',"dossards_inscription_{$id}.pdf");