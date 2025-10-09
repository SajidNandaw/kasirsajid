<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

if (!isset($_GET['id'])) {
    die("ID transaksi tidak ditemukan.");
}
$id = (int)$_GET['id'];

// Ambil data transaksi
$stmt = $pdo->prepare("
    SELECT t.*, u.username
    FROM transaksi t
    JOIN users u ON u.id_user = t.id_user
    WHERE t.id_transaksi = ?
");
$stmt->execute([$id]);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$trx) die("Transaksi tidak ditemukan.");

// Ambil detail transaksi
$detail = $pdo->prepare("
    SELECT td.*, b.nama_barang
    FROM transaksi_detail td
    JOIN barang b ON b.id_barang = td.id_barang
    WHERE td.id_transaksi = ?
");
$detail->execute([$id]);
$items = $detail->fetchAll(PDO::FETCH_ASSOC);

$title = "Nota #{$id}";
require '../includes/header.php';
?>

<div class="nota-container">
  <h2>🧾 Nota Transaksi</h2>

  <div class="nota-card">
    <div class="nota-info">
      <p><strong>ID:</strong> <?= $trx['id_transaksi'] ?></p>
      <p><strong>Tanggal:</strong> <?= $trx['tanggal'] ?></p>
      <p><strong>Kasir:</strong> <?= htmlspecialchars($trx['username']) ?></p>
      <p><strong>Pelanggan:</strong> <?= htmlspecialchars($trx['pelanggan'] ?? 'Umum') ?></p>
    </div>

    <table class="nota-table">
      <thead>
        <tr>
          <th>Barang</th>
          <th>Qty</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php $grand = 0;
        foreach ($items as $it):
          $grand += $it['subtotal']; ?>
          <tr>
            <td><?= htmlspecialchars($it['nama_barang']) ?></td>
            <td><?= $it['qty'] ?></td>
            <td>Rp <?= number_format($it['subtotal'],0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="nota-total">
          <td colspan="2"><strong>Total</strong></td>
          <td><strong>Rp <?= number_format($grand,0,",",".") ?></strong></td>
        </tr>
      </tbody>
    </table>

    <div class="nota-actions">
      <button class="btn-print" onclick="window.print()">🖨 Cetak Nota</button>
      <a href="/kasirsajid/dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
    </div>
  </div>
</div>

<style>
/* ====== STYLE HALAMAN NOTA ====== */
body {
  background: #f9f7ff;
}

.nota-container {
  max-width: 600px;
  margin: 30px auto;
  padding: 20px;
}

h2 {
  text-align: center;
  color: #5b21b6;
  margin-bottom: 15px;
  font-weight: 700;
}

.nota-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px 25px;
  box-shadow: 0 4px 10px rgba(91, 33, 182, 0.15);
  border-top: 5px solid #7c3aed;
}

.nota-info p {
  margin: 4px 0;
  font-size: 15px;
  color: #333;
}

.nota-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 12px;
}

.nota-table th {
  background: #ede9fe;
  color: #4c1d95;
  text-align: left;
  padding: 8px;
  font-weight: 600;
}

.nota-table td {
  padding: 8px;
  border-bottom: 1px solid #eee;
}

.nota-total td {
  background: #f5f3ff;
  color: #4c1d95;
  font-size: 16px;
}

.nota-actions {
  margin-top: 18px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.btn-print {
  background: linear-gradient(135deg, #8b5cf6, #6d28d9);
  color: white;
  border: none;
  padding: 10px 14px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}

.btn-print:hover {
  background: #5b21b6;
}

.btn-back {
  background: transparent;
  color: #6d28d9;
  border: 1.5px solid #6d28d9;
  padding: 10px 14px;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  transition: 0.3s;
}

.btn-back:hover {
  background: #ede9fe;
}

/* ====== CETAK ====== */
@media print {
  body {
    background: #fff;
    font-family: monospace;
    font-size: 14px;
    width: 280px;
    margin: 0 auto;
  }

  .nota-card {
    box-shadow: none;
    border: none;
    padding: 0;
  }

  .nota-container, h2 {
    margin: 0;
    padding: 0;
  }

  h2 {
    font-size: 16px;
    text-align: center;
    border-bottom: 1px dashed #000;
    padding-bottom: 4px;
    margin-bottom: 8px;
    color: #000;
  }

  .nota-actions, .btn-print, .btn-back {
    display: none;
  }

  .nota-table th, .nota-table td {
    padding: 2px 0;
    border: none;
  }
}
</style>

<?php require '../includes/footer.php'; ?>
