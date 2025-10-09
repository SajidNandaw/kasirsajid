<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$title = 'Laporan Penjualan';
require '../includes/header.php';

// === FILTER TANGGAL ===
$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date'] ?? '';

$where = "";
$params = [];
if ($start && $end) {
    $where = "WHERE t.tanggal BETWEEN ? AND ?";
    $params = [$start . " 00:00:00", $end . " 23:59:59"];
}

// === DATA MEMBER ===
$pelanggan_stmt = $pdo->query("SELECT nama FROM pelanggan");
$daftar_member = array_column($pelanggan_stmt->fetchAll(PDO::FETCH_ASSOC), 'nama');

// === TRANSAKSI ===
$sql = "SELECT t.*, u.username 
        FROM transaksi t 
        JOIN users u ON u.id_user = t.id_user
        $where
        ORDER BY t.tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === BARANG ===
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

// === PELANGGAN ===
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

<style>
  body {
    background: linear-gradient(135deg, #f0e6ff 0%, #e0d4ff 30%, #d0bcff 100%);
    font-family: 'Poppins', sans-serif;
    color: #2d0a54;
    min-height: 100vh;
  }

  h2, h3 {
    color: #4c1d95;
    font-weight: 600;
  }

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
  }

  .purple-btn {
    background: linear-gradient(90deg, #7c3aed, #9f67ff);
    border: none;
    color: white !important;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.25s;
    text-decoration: none;
    box-shadow: 0 3px 8px rgba(124, 58, 237, 0.4);
  }

  .purple-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(124, 58, 237, 0.6);
  }

  .glass-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(100, 0, 200, 0.1);
    margin-bottom: 24px;
    backdrop-filter: blur(6px);
  }

  .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
  }

  .filter-form label {
    font-weight: 500;
    color: #3b0764;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
  }

  th, td {
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
  }

  th {
    background: #ede9fe;
    color: #4c1d95;
    text-align: left;
  }

  tr:hover td {
    background: #f5f3ff;
  }

  .btn-area {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
  }

  .back-btn {
    background: white;
    color: #4c1d95;
    border: 2px solid #a78bfa;
    border-radius: 8px;
    padding: 8px 14px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.25s;
  }

  .back-btn:hover {
    background: #ede9fe;
  }

  .filter-btn {
    background: #8b5cf6;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 6px 14px;
    font-weight: 600;
  }

  .filter-btn:hover {
    background: #7c3aed;
  }
</style>

<div class="page-header">
  <h2>Laporan Penjualan</h2>
  <a href="../admin/dashboard.php" class="back-btn">← Kembali</a>
</div>

<!-- FILTER -->
<div class="glass-card">
  <form method="get" class="filter-form">
    <div>
      <label>Dari</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($start) ?>" class="form-control">
    </div>
    <div>
      <label>Sampai</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($end) ?>" class="form-control">
    </div>
    <div>
      <button type="submit" class="filter-btn">Filter</button>
      <a href="laporan.php" class="back-btn">Reset</a>
    </div>
  </form>
</div>

<!-- TRANSAKSI -->
<div class="glass-card">
  <h3>🧾 Transaksi</h3>
  <?php if(empty($transaksi)): ?>
    <p>Tidak ada transaksi pada periode ini.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Pelanggan</th>
          <th>Kasir</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $grand=0;
        foreach($transaksi as $t): 
          $q = $pdo->prepare("SELECT SUM(subtotal) FROM transaksi_detail WHERE id_transaksi=?");
          $q->execute([$t['id_transaksi']]);
          $tot = $q->fetchColumn();
          $grand += $tot;
          $nama_pelanggan = trim($t['pelanggan'] ?? 'Umum');
        ?>
          <tr>
            <td><?= $t['id_transaksi'] ?></td>
            <td><?= $t['tanggal'] ?></td>
            <td><?= htmlspecialchars($nama_pelanggan) ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td>Rp <?= number_format($tot,0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
        <tr style="font-weight:600;">
          <td colspan="4">Grand Total</td>
          <td>Rp <?= number_format($grand,0,",",".") ?></td>
        </tr>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- BARANG -->
<div class="glass-card">
  <h3>📦 Ringkasan Per Barang</h3>
  <?php if(empty($barang)): ?>
    <p>Tidak ada data barang terjual.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Barang</th><th>Jumlah Terjual</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach($barang as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['nama_barang']) ?></td>
            <td><?= $b['total_qty'] ?></td>
            <td>Rp <?= number_format($b['total_amount'],0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- PELANGGAN -->
<div class="glass-card">
  <h3>👥 Ringkasan Per Pelanggan</h3>
  <?php if(empty($pelanggan)): ?>
    <p>Tidak ada data pelanggan.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Pelanggan</th><th>Status</th><th>Total Belanja</th></tr></thead>
      <tbody>
        <?php foreach($pelanggan as $p): 
          $nama = trim($p['nama_pelanggan']);
          $status = in_array($nama, $daftar_member) ? 'Member' : 'Bukan Member';
        ?>
          <tr>
            <td><?= htmlspecialchars($nama) ?></td>
            <td><?= $status ?></td>
            <td>Rp <?= number_format($p['total_belanja'],0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- CETAK -->
<div class="btn-area">
  <?php $q = http_build_query(['start_date'=>$start,'end_date'=>$end]); ?>
  <a href="laporan_cetak.php?<?= $q ?>" class="purple-btn">🖨 Cetak Laporan</a>
</div>

<?php require '../includes/footer.php'; ?>
