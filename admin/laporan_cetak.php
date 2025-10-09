<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date'] ?? '';

$where = "";
$params = [];
if ($start && $end) {
    $where = "WHERE t.tanggal BETWEEN ? AND ?";
    $params = [$start . " 00:00:00", $end . " 23:59:59"];
}

// === Data Transaksi ===
$sql = "SELECT t.*, u.username 
        FROM transaksi t 
        JOIN users u ON u.id_user = t.id_user
        $where
        ORDER BY t.tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === Ringkasan Barang ===
$sqlBarang = "SELECT b.nama_barang, SUM(td.qty) as total_qty, SUM(td.subtotal) as total_amount
              FROM transaksi_detail td
              JOIN barang b ON b.id_barang = td.id_barang
              JOIN transaksi t ON t.id_transaksi = td.id_transaksi
              $where
              GROUP BY td.id_barang
              ORDER BY total_amount DESC";
$stBarang = $pdo->prepare($sqlBarang);
$stBarang->execute($params);
$barang = $stBarang->fetchAll(PDO::FETCH_ASSOC);

// === Ringkasan Pelanggan ===
$sqlPelanggan = "SELECT COALESCE(t.pelanggan,'Umum') as nama_pelanggan, SUM(td.subtotal) as total_belanja
                 FROM transaksi t
                 JOIN transaksi_detail td ON td.id_transaksi = t.id_transaksi
                 $where
                 GROUP BY nama_pelanggan
                 ORDER BY total_belanja DESC";
$stPelanggan = $pdo->prepare($sqlPelanggan);
$stPelanggan->execute($params);
$pelanggan = $stPelanggan->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Laporan Penjualan</title>
  <style>
    :root {
      --ungu1: #ede9fe;
      --ungu2: #c4b5fd;
      --ungu3: #8b5cf6;
      --text: #2e1065;
    }
    body {
      font-family: "Poppins", Arial, sans-serif;
      background: linear-gradient(135deg, var(--ungu1), var(--ungu2));
      color: var(--text);
      margin: 30px auto;
      max-width: 900px;
      line-height: 1.6;
      border-radius: 14px;
      padding: 20px 30px;
      box-shadow: 0 4px 20px rgba(139, 92, 246, 0.2);
    }
    h2, h3 {
      text-align: center;
      color: var(--text);
      margin-bottom: 8px;
    }
    .periode {
      text-align: center;
      font-style: italic;
      color: #5b21b6;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 14px;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      border: 1px solid #e5e7eb;
      padding: 8px 10px;
      text-align: left;
    }
    th {
      background: var(--ungu3);
      color: #fff;
    }
    tr:nth-child(even) td {
      background: #f5f3ff;
    }
    .right { text-align: right; }

    .no-print {
      text-align: center;
      margin-top: 20px;
    }
    .btn {
      display: inline-block;
      background: var(--ungu3);
      color: white;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      padding: 8px 16px;
      text-decoration: none;
      transition: 0.25s;
    }
    .btn:hover {
      background: #7c3aed;
      transform: translateY(-2px);
    }
    .btn-back {
      background: white;
      color: var(--ungu3);
      border: 2px solid var(--ungu3);
      margin-left: 10px;
    }
    .btn-back:hover {
      background: var(--ungu1);
    }

    @media print {
      body {
        background: white;
        color: black;
        box-shadow: none;
        padding: 0;
      }
      .no-print { display: none; }
      table { page-break-inside: avoid; }
    }
  </style>
</head>
<body>

  <h2>Laporan Penjualan</h2>
  <div class="periode">
    Periode: <?= htmlspecialchars($start ?: '-') ?> — <?= htmlspecialchars($end ?: '-') ?>
  </div>

  <!-- === Transaksi === -->
  <h3>Data Transaksi</h3>
  <table>
    <thead>
      <tr><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Kasir</th><th class="right">Total</th></tr>
    </thead>
    <tbody>
      <?php 
      $grand = 0;
      foreach ($transaksi as $t):
        $q = $pdo->prepare("SELECT SUM(subtotal) FROM transaksi_detail WHERE id_transaksi=?");
        $q->execute([$t['id_transaksi']]);
        $tot = $q->fetchColumn();
        $grand += $tot;
      ?>
        <tr>
          <td><?= $t['id_transaksi'] ?></td>
          <td><?= $t['tanggal'] ?></td>
          <td><?= htmlspecialchars($t['pelanggan'] ?? 'Umum') ?></td>
          <td><?= htmlspecialchars($t['username']) ?></td>
          <td class="right">Rp <?= number_format($tot,0,",",".") ?></td>
        </tr>
      <?php endforeach; ?>
      <tr style="font-weight:600;background:var(--ungu2);">
        <td colspan="4">Grand Total</td>
        <td class="right">Rp <?= number_format($grand,0,",",".") ?></td>
      </tr>
    </tbody>
  </table>

  <!-- === Ringkasan Barang === -->
  <h3>Ringkasan Per Barang</h3>
  <table>
    <thead><tr><th>Barang</th><th>Jumlah</th><th class="right">Total</th></tr></thead>
    <tbody>
      <?php foreach($barang as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['nama_barang']) ?></td>
          <td><?= $b['total_qty'] ?></td>
          <td class="right">Rp <?= number_format($b['total_amount'],0,",",".") ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- === Ringkasan Pelanggan === -->
  <h3>Ringkasan Per Pelanggan</h3>
  <table>
    <thead><tr><th>Pelanggan</th><th class="right">Total Belanja</th></tr></thead>
    <tbody>
      <?php foreach($pelanggan as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['nama_pelanggan']) ?></td>
          <td class="right">Rp <?= number_format($p['total_belanja'],0,",",".") ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Tombol -->
  <div class="no-print">
    <a href="#" onclick="window.print();return false;" class="btn">🖨 Cetak</a>
    <a href="laporan.php" class="btn btn-back">← Kembali</a>
  </div>

</body>
</html>
