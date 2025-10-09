<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date'] ?? '';

$where  = "";
$params = [];
if ($start && $end) {
    $where = "WHERE t.tanggal BETWEEN ? AND ?";
    $params[] = $start . " 00:00:00";
    $params[] = $end . " 23:59:59";
}

$sqlAgg = "
  SELECT b.nama_barang, SUM(td.qty) AS total_qty, SUM(td.subtotal) AS total_amount
  FROM transaksi_detail td
  JOIN transaksi t ON t.id_transaksi = td.id_transaksi
  JOIN barang b ON b.id_barang = td.id_barang
";
if ($where) $sqlAgg .= " $where ";
$sqlAgg .= " GROUP BY td.id_barang ORDER BY total_amount DESC";

$stAgg = $pdo->prepare($sqlAgg);
$stAgg->execute($params);
$agg = $stAgg->fetchAll(PDO::FETCH_ASSOC);

$grandAll = 0;
foreach ($agg as $r) $grandAll += $r['total_amount'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Ringkasan Penjualan</title>
  <style>
    :root {
      --primary: #6a0dad;
      --primary-light: #b066ff;
      --bg-light: #f8f3fc;
      --text-dark: #1a1a1a;
      --white: #fff;
    }
    body {
      font-family: "Poppins", Arial, sans-serif;
      background: linear-gradient(135deg, var(--bg-light), #e9d7ff);
      color: var(--text-dark);
      margin: 40px auto;
      max-width: 900px;
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(106,13,173,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 10px;
      color: var(--primary);
      font-weight: 700;
      letter-spacing: 1px;
    }
    .periode {
      text-align: center;
      color: #555;
      margin-bottom: 20px;
      font-style: italic;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--white);
      border-radius: 10px;
      overflow: hidden;
      font-size: 14px;
    }
    th, td {
      border: 1px solid #e0d0f5;
      padding: 10px 12px;
      text-align: left;
    }
    th {
      background: var(--primary);
      color: var(--white);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    tr:nth-child(even) td {
      background: #f4ebff;
    }
    .right { text-align: right; }

    /* tombol */
    .btn {
      display: inline-block;
      background: var(--primary);
      color: var(--white);
      font-weight: 600;
      border: none;
      border-radius: 8px;
      padding: 10px 18px;
      text-decoration: none;
      box-shadow: 0 4px 10px rgba(106,13,173,0.3);
      transition: all 0.2s ease;
    }
    .btn:hover {
      background: var(--primary-light);
      transform: translateY(-2px);
    }
    .btn-black {
      background: #222;
      color: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .btn-black:hover {
      background: #444;
    }

    .no-print {
      text-align: center;
      margin-top: 20px;
    }

    @media print {
      .no-print { display: none; }
      body {
        margin: 0;
        box-shadow: none;
        background: #fff;
      }
      table {
        border: 1px solid #000;
      }
      th {
        background: #eee;
        color: #000;
      }
    }
  </style>
</head>
<body>

  <h2>Ringkasan Penjualan</h2>
  <div class="periode">
    Periode: <?= ($start && $end) ? htmlspecialchars($start . " — " . $end) : "-" ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>Barang</th>
        <th>Jumlah</th>
        <th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($agg as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['nama_barang']) ?></td>
          <td><?= (int)$r['total_qty'] ?></td>
          <td class="right">Rp <?= number_format($r['total_amount'], 0, ",", ".") ?></td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td><strong>Grand Total</strong></td>
        <td></td>
        <td class="right"><strong>Rp <?= number_format($grandAll, 0, ",", ".") ?></strong></td>
      </tr>
    </tbody>
  </table>

  <div class="no-print">
    <a href="#" onclick="window.print();return false;" class="btn">🖨 Cetak Ringkasan</a>
    <a href="/kasirsajid/kasir/riwayat.php" class="btn btn-black" style="margin-left:10px;">← Kembali</a>
  </div>

</body>
</html>
