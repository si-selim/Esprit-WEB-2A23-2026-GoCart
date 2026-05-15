<?php
require_once __DIR__ . '/../../lib/fpdf186/fpdf.php';
require_once "../../Controller/CommandeController.php";
require_once "../../Controller/LigneCommandeController.php";
require_once "../../Controller/ProduitController.php";

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    die("ID de commande manquant");
}

$commandeC = new CommandeController();
$ligneC = new LigneCommandeController();
$produitC = new ProduitController();

$commande = $commandeC->showCommande($id);
if (!$commande) {
    die("Commande introuvable");
}

$lignesQuery = $ligneC->getLignesCommande($id);
$lignes = $lignesQuery->fetchAll();

// Palette
define('C_NAVY_R',  11);  define('C_NAVY_G',  32);  define('C_NAVY_B',  50);
define('C_TEAL_R',  15);  define('C_TEAL_G', 118);  define('C_TEAL_B', 110);
define('C_MINT_R', 204);  define('C_MINT_G', 251);  define('C_MINT_B', 241);
define('C_GREY_R', 100);  define('C_GREY_G', 116);  define('C_GREY_B', 139);
define('C_LINE_R', 226);  define('C_LINE_G', 232);  define('C_LINE_B', 240);

define('LOGO_PATH', __DIR__ . '/../assets/images/logo_barchathon.jpg');

class CommandePDF extends FPDF
{
    public $commandeId = 0;
    public $commandeDate = '';

    function Header()
    {
        $w = $this->GetPageWidth();

        // Navy banner
        $this->SetFillColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);
        $this->Rect(0, 0, $w, 32, 'F');

        // Teal accent stripe gauche
        $this->SetFillColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
        $this->Rect(0, 0, 3, 32, 'F');

        // Logo
        $logoPath = LOGO_PATH;
        $logoH    = 26;
        $xLogo    = 6;
        $yLogo    = 3;

        if (file_exists($logoPath)) {
            $this->SetFillColor(255, 255, 255);
            $this->RoundedRect($xLogo, $yLogo, $logoH * 1.6, $logoH, 4, 'F');
            $this->Image($logoPath, $xLogo + 1, $yLogo + 1, $logoH * 1.6 - 2, $logoH - 2);
            $afterLogo = $xLogo + $logoH * 1.6 + 4;
        } else {
            $this->SetFillColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
            $this->RoundedRect(6, 6, 20, 20, 4, 'F');
            $this->SetFont('Arial', 'B', 12);
            $this->SetTextColor(255, 255, 255);
            $this->SetXY(6, 11);
            $this->Cell(20, 10, 'BT', 0, 0, 'C');

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
        $this->Cell(60, 6, 'Front Office  -  Facture Commande', 0, 0, 'L');

        // Droite : commande id + date
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY($w - 78, 6);
        $this->Cell(73, 9, 'Commande #' . $this->commandeId, 0, 0, 'R');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(167, 243, 208);
        $this->SetXY($w - 78, 16);
        $this->Cell(73, 6, $this->commandeDate, 0, 0, 'R');

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
            'Genere automatiquement par BarchaThon Front Office  |  Commande #' . $this->commandeId,
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

// Build PDF
$pdf = new CommandePDF();
$pdf->commandeId = $commande['idcommande'];
$pdf->commandeDate = date('d/m/Y  H:i', strtotime($commande['datecommande']));
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

$pdf->SectionTitle('Details de la Commande');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(C_GREY_R, C_GREY_G, C_GREY_B);
$pdf->Cell(0, 5, "Recapitulatif des produits commandes", 0, 1, 'L');
$pdf->Ln(4);

// Order Info Block
$pdf->SetFillColor(C_MINT_R, C_MINT_G, C_MINT_B);
$pdf->RoundedRect(10, $pdf->GetY(), 90, 25, 3, 'F');
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);
$pdf->SetXY(15, $pdf->GetY() + 4);
$pdf->Cell(80, 6, 'Stand : ' . $commande['idstand'], 0, 1, 'L');
$pdf->SetX(15);
$pdf->Cell(80, 6, 'Statut : ' . iconv('UTF-8', 'windows-1252//TRANSLIT', $commande['statut']), 0, 1, 'L');
$pdf->SetX(15);
$pdf->Cell(80, 6, 'Montant Total : ' . number_format($commande['montanttotale'], 2, ',', ' ') . ' TND', 0, 1, 'L');
$pdf->Ln(8);

// Table header
$cols = [
    ['Produit', 70, 'L'],
    ['Quantite', 30, 'C'],
    ['Prix unitaire', 45, 'C'],
    ['Total ligne', 45, 'C'],
];

$pdf->SetFillColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetLineWidth(0);
foreach ($cols as [$label, $w]) {
    $pdf->Cell($w, 10, $label, 0, 0, 'C', true);
}
$pdf->Ln();

// Rows
$rowH = 12; 
$fill = false;
$totalLignes = 0;

foreach ($lignes as $ligne) {
    $yRow = $pdf->GetY();

    // Teal left stripe
    $pdf->SetFillColor(C_TEAL_R, C_TEAL_G, C_TEAL_B);
    $pdf->Rect(10, $yRow, 1.5, $rowH, 'F');

    if ($fill) $pdf->SetFillColor(C_MINT_R, C_MINT_G, C_MINT_B);
    else $pdf->SetFillColor(255, 255, 255);

    $pdf->SetDrawColor(C_LINE_R, C_LINE_G, C_LINE_B);
    $pdf->SetLineWidth(0.2);
    $pdf->SetTextColor(C_NAVY_R, C_NAVY_G, C_NAVY_B);

    $pdf->SetXY(11.5, $yRow);
    
    // Product info
    $prod = $produitC->getProduit($ligne['idproduit']);
    $nomProduit = $prod ? $prod['nom_produit'] : 'Produit inconnu (' . $ligne['idproduit'] . ')';

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(68.5, $rowH, iconv('UTF-8', 'windows-1252//TRANSLIT', $nomProduit), 'B', 0, 'L', true);

    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(30, $rowH, $ligne['quantite'], 'B', 0, 'C', true);
    
    $prixU = number_format($ligne['prixunitaire'], 2, ',', ' ') . ' TND';
    $pdf->Cell(45, $rowH, $prixU, 'B', 0, 'C', true);

    $totalLigne = $ligne['quantite'] * $ligne['prixunitaire'];
    $totalLignes += $totalLigne;
    $totalL = number_format($totalLigne, 2, ',', ' ') . ' TND';
    $pdf->Cell(45, $rowH, $totalL, 'B', 0, 'C', true);

    $pdf->Ln();
    $fill = !$fill;
}

// Total
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 10, '', 0, 0, 'L');
$pdf->Cell(45, 10, 'Total :', 0, 0, 'R');
$pdf->Cell(45, 10, number_format($totalLignes, 2, ',', ' ') . ' TND', 0, 1, 'C');

$tableW = array_sum(array_column($cols, 1));
$pdf->SetDrawColor(C_LINE_R, C_LINE_G, C_LINE_B);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 10 + $tableW, $pdf->GetY());

$pdf->Output('I', "commande_{$id}.pdf");
