<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

$title = 'Riwayat Transaksi';
require '../includes/header.php';

// ambil parameter tanggal dari GET
$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date'] ?? '';

// Ambil semua nama pelanggan dari tabel pelanggan untuk cek member
$pelanggan_stmt = $pdo->query("SELECT nama FROM pelanggan");
$daftar_member = array_column($pelanggan_stmt->fetchAll(PDO::FETCH_ASSOC), 'nama');

// prepare query range
$where  = "";
$params = [];
if ($start && $end) {
    $where = "WHERE t.tanggal BETWEEN ? AND ?";
    $params[] = $start . " 00:00:00";
    $params[] = $end . " 23:59:59";
}

// Ambil transaksi (filtered)
$stmt = $pdo->prepare("
    SELECT t.*, u.username 
    FROM transaksi t 
    JOIN users u ON u.id_user = t.id_user
    $where
    ORDER BY t.tanggal DESC
");
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* ======== STYLE GLOBAL ======== */
body {
  background: #f8f6ff;
}

h2 {
  color: #5b21b6;
  font-weight: 700;
  margin-bottom: 15px;
  text-align: center;
}

.card {
  background: #fff;
  border-radius: 12px;
  padding: 20px 25px;
  margin-bottom: 20px;
  box-shadow: 0 4px 10px rgba(124, 58, 237, 0.15);
  border-top: 5px solid #7c3aed;
}

label {
  font-weight: 600;
  color: #4c1d95;
  display: block;
  margin-bottom: 4px;
}

input[type="date"], select {
  padding: 6px 10px;
  border: 1px solid #d6d3e0;
  border-radius: 8px;
  font-size: 14px;
  width: 180px;
}

.btn {
  background: linear-gradient(135deg, #8b5cf6, #6d28d9);
  color: white !important;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}

.btn:hover {
  background: #5b21b6;
}

.btn-ghost {
  color: #6d28d9;
  text-decoration: none;
  padding: 8px 14px;
  border: 1.5px solid #6d28d9;
  border-radius: 8px;
  font-weight: 600;
  transition: 0.3s;
}

.btn-ghost:hover {
  background: #ede9fe;
}

/* ======== TABEL ======== */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  font-size: 14px;
}

table th {
  background: #ede9fe;
  color: #4c1d95;
  padding: 8px;
  text-align: left;
}

table td {
  padding: 8px;
  border-bottom: 1px solid #eee;
}

table tr:hover {
  background: #faf5ff;
}

.mt-3 {
  margin-top: 20px;
}

/* ======== FILTER FORM ======== */
.filter-form {
  display: flex;
  gap: 15px;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-form div {
  display: flex;
  flex-direction: column;
}

/* ======== RINGKASAN ======== */
.total-row td {
  background: #f5f3ff;
  color: #4c1d95;
  font-weight: 600;
}
</style>

<h2>📜 Riwayat Transaksi</h2>

<!-- ======== FILTER ======== -->
<div class="card">
  <form method="get" class="filter-form">
    <div>
      <label>Start</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($start) ?>">
    </div>
    <div>
      <label>End</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($end) ?>">
    </div>
    <div>
      <button type="submit" class="btn">Filter</button>
      <a class="btn-ghost" href="riwayat.php">Reset</a>
    </div>
  </form>
</div>

<!-- ======== DAFTAR TRANSAKSI ======== -->
<div class="card">
  <h3 style="color:#4c1d95; margin-bottom:8px;">Daftar Transaksi</h3>
  <?php if (empty($transaksi)): ?>
    <div class="small">Belum ada transaksi pada rentang waktu tersebut.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Pelanggan</th>
          <th>Status</th>
          <th>Kasir</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transaksi as $t): 
            $nama_pelanggan = trim($t['pelanggan'] ?? 'Umum');
            $status_member = in_array($nama_pelanggan, $daftar_member) ? 'Member' : 'Bukan Member';
        ?>
          <tr>
            <td><?= $t['id_transaksi'] ?></td>
            <td><?= $t['tanggal'] ?></td>
            <td><?= htmlspecialchars($nama_pelanggan) ?></td>
            <td><?= $status_member ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td>
              <a href="/kasirsajid/kasir/nota.php?id=<?= $t['id_transaksi'] ?>" class="btn-ghost">Cetak Nota</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- ======== RINGKASAN PENJUALAN ======== -->
<div class="card">
  <h3 style="color:#4c1d95; margin-bottom:8px;">Ringkasan Penjualan</h3>
  <?php
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

  <?php if (empty($agg)): ?>
    <div class="small">Tidak ada penjualan pada rentang waktu tersebut.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Barang</th>
          <th>Jumlah Terjual</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($agg as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['nama_barang']) ?></td>
            <td><?= (int)$r['total_qty'] ?></td>
            <td>Rp <?= number_format($r['total_amount'],0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td><strong>Grand Total</strong></td>
          <td></td>
          <td><strong>Rp <?= number_format($grandAll,0,",",".") ?></strong></td>
        </tr>
      </tbody>
    </table>

    <div style="margin-top:15px;">
      <?php
        $q = http_build_query(['start_date'=>$start,'end_date'=>$end]);
        $cetakUrl = "/kasirsajid/kasir/riwayat_cetak.php?$q";
      ?>
      <a href="<?= $cetakUrl ?>" class="btn">Cetak Ringkasan</a>
    </div>
  <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>
