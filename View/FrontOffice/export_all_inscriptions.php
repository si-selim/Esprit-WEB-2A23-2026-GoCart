<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/MARATHONS/lib/dompdf/autoload.inc.php";
require_once "../../Controller/InscriptionController.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$controller = new InscriptionController();
$liste = $controller->getAll();

// Encode logo as base64 so dompdf can embed it
$logo_path = $_SERVER['DOCUMENT_ROOT'] . "/MARATHONS/assets/logo.jpg";
$logo_base64 = "";
if (file_exists($logo_path)) {
    $logo_data = file_get_contents($logo_path);
    $logo_base64 = "data:image/jpeg;base64," . base64_encode($logo_data);
}

$export_date = date('d/m/Y à H:i');
$total_inscrits = 0;
foreach ($liste as $row) {
    $total_inscrits += (int)$row['nb_personnes'];
}

$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f7f6f2;
    color: #1e293b;
    font-size: 12px;
  }

  /* ── HEADER ── */
  .topbar {
    background: #0b2032;
    padding: 14px 24px;
    display: table;
    width: 100%;
  }

  .topbar-left {
    display: table-cell;
    vertical-align: middle;
  }

  .brand-mark {
    display: inline-block;
    background: #0f766e;
    color: white;
    font-weight: bold;
    font-size: 16px;
    width: 36px;
    height: 36px;
    line-height: 36px;
    text-align: center;
    border-radius: 8px;
    margin-right: 10px;
    vertical-align: middle;
  }

  .brand-name {
    color: white;
    font-size: 18px;
    font-weight: bold;
    vertical-align: middle;
  }

  .brand-sub {
    color: #94a3b8;
    font-size: 10px;
    display: block;
    margin-left: 46px;
    margin-top: -2px;
  }

  .topbar-right {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
  }

  .topbar-right img {
    height: 52px;
    border-radius: 6px;
  }

  /* ── META BAR ── */
  .meta-bar {
    background: #0f766e;
    color: white;
    padding: 8px 24px;
    display: table;
    width: 100%;
    font-size: 11px;
  }

  .meta-left  { display: table-cell; vertical-align: middle; }
  .meta-right { display: table-cell; vertical-align: middle; text-align: right; }

  /* ── TITLE SECTION ── */
  .title-section {
    padding: 20px 24px 10px 24px;
  }

  .title-section h1 {
    color: #0b2032;
    font-size: 20px;
    font-weight: bold;
  }

  .title-section p {
    color: #64748b;
    font-size: 11px;
    margin-top: 3px;
  }

  /* ── STATS CARDS ── */
  .stats-row {
    display: table;
    width: 100%;
    padding: 0 24px 16px 24px;
    border-spacing: 12px 0;
  }

  .stat-card {
    display: table-cell;
    background: white;
    border: 1px solid #e2e8f0;
    border-top: 3px solid #0f766e;
    border-radius: 8px;
    padding: 12px 16px;
    text-align: center;
    width: 25%;
  }

  .stat-value {
    font-size: 22px;
    font-weight: bold;
    color: #0b2032;
  }

  .stat-label {
    font-size: 10px;
    color: #64748b;
    margin-top: 2px;
  }

  /* ── TABLE CARD ── */
  .card {
    margin: 0 24px 24px 24px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
  }

  .card-header {
    background: #0b2032;
    padding: 12px 18px;
    color: white;
    font-size: 13px;
    font-weight: bold;
  }

  .card-header span {
    color: #94a3b8;
    font-size: 11px;
    font-weight: normal;
    margin-left: 8px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  thead tr {
    background: #1e3a5f;
  }

  thead th {
    color: #e2e8f0;
    padding: 10px 12px;
    text-align: center;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  tbody tr:nth-child(even) {
    background: #f8fafc;
  }

  tbody tr:nth-child(odd) {
    background: #ffffff;
  }

  tbody td {
    padding: 9px 12px;
    text-align: center;
    color: #334155;
    font-size: 11px;
    border-bottom: 1px solid #e2e8f0;
  }

  .badge-paid {
    background: #dcfce7;
    color: #166534;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
  }

  .badge-unpaid {
    background: #fee2e2;
    color: #991b1b;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
  }

  .circuit-tag {
    background: #e0f2fe;
    color: #075985;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
  }

  /* ── FOOTER ── */
  .footer {
    margin: 0 24px 0 24px;
    padding: 12px 0;
    border-top: 1px solid #e2e8f0;
    display: table;
    width: calc(100% - 48px);
    color: #94a3b8;
    font-size: 10px;
  }

  .footer-left  { display: table-cell; }
  .footer-right { display: table-cell; text-align: right; }

</style>
</head>
<body>

<!-- ═══════ TOPBAR ═══════ -->
<div class="topbar">
  <div class="topbar-left">
    <span class="brand-mark">BT</span>
    <span class="brand-name">BarchaThon</span>
    <span class="brand-sub">Rapport d\'inscriptions — Marathon Carthage</span>
  </div>
  <div class="topbar-right">';

if ($logo_base64 !== "") {
    $html .= '<img src="' . $logo_base64 . '" alt="Logo">';
} else {
    $html .= '<span style="color:#94a3b8;font-size:10px;">Logo introuvable</span>';
}

$html .= '
  </div>
</div>

<!-- ═══════ META BAR ═══════ -->
<div class="meta-bar">
  <div class="meta-left">📅 Exporté le : <strong>' . $export_date . '</strong></div>
  <div class="meta-right">Front Office &nbsp;|&nbsp; Marathon Carthage 2025</div>
</div>

<!-- ═══════ TITLE ═══════ -->
<div class="title-section">
  <h1>Liste complète des inscriptions</h1>
  <p>Toutes les inscriptions enregistrées sur la plateforme BarchaThon</p>
</div>
';


$paid_count   = 0;
$unpaid_count = 0;
foreach ($liste as $row) {
    if ($row['statut_paiement'] === 'paid') $paid_count++;
    else $unpaid_count++;
}

$html .= '
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-value">' . count($liste) . '</div>
    <div class="stat-label">Total inscriptions</div>
  </div>
  <div class="stat-card">
    <div class="stat-value">' . $total_inscrits . '</div>
    <div class="stat-label">Total participants</div>
  </div>
  <div class="stat-card">
    <div class="stat-value" style="color:#166534;">' . $paid_count . '</div>
    <div class="stat-label">Paiements confirmés</div>
  </div>
  <div class="stat-card">
    <div class="stat-value" style="color:#991b1b;">' . $unpaid_count . '</div>
    <div class="stat-label">En attente de paiement</div>
  </div>
</div>
';


$html .= '
<div class="card">
  <div class="card-header">
    Inscriptions récentes
    <span>' . count($liste) . ' entrées</span>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Date inscription</th>
        <th>Mode paiement</th>
        <th>Circuit</th>
        <th>Nb personnes</th>
        <th>Date paiement</th>
        <th>Statut</th>
      </tr>
    </thead>
    <tbody>';

$circuit_labels = [1 => '10 km', 2 => '21 km', 3 => '42 km'];
$i = 1;

foreach ($liste as $row) {
    $circuit_id  = (int)$row['id_parcours'];
    $circuit_lbl = isset($circuit_labels[$circuit_id]) ? $circuit_labels[$circuit_id] : $circuit_id;
    $is_paid     = $row['statut_paiement'] === 'paid';
    $statut_html = $is_paid
        ? '<span class="badge-paid">Payé</span>'
        : '<span class="badge-unpaid">Non payé</span>';

    $date_p = date("d/m/Y", strtotime($row['date_paiement']));
    $date_i = date("d/m/Y", strtotime($row['date_inscription']));

    $html .= "
      <tr>
        <td>{$i}</td>
        <td>{$date_i}</td>
        <td>{$row['mode_de_paiement']}</td>
        <td><span class='circuit-tag'>{$circuit_lbl}</span></td>
        <td>{$row['nb_personnes']}</td>
        <td>{$date_p}</td>
        <td>{$statut_html}</td>
      </tr>";
    $i++;
}

$html .= '
    </tbody>
  </table>
</div>

<!-- ═══════ FOOTER ═══════ -->
<div class="footer">
  <div class="footer-left">BarchaThon &copy; ' . date('Y') . ' — Document généré automatiquement</div>
  <div class="footer-right">Marathon Carthage &nbsp;|&nbsp; Front Office</div>
</div>

</body>
</html>';


$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("inscriptions_" . date('Ymd_His') . ".pdf", ["Attachment" => true]);