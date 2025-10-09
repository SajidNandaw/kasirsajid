<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$title = 'Manajemen Barang';

// ===== LOGIKA SIMPAN / NONAKTIFKAN =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_barang'])) {
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE barang SET kode_barang=?, nama_barang=?, harga=?, stok=? WHERE id_barang=?');
        $stmt->execute([$kode, $nama, $harga, $stok, $_POST['id']]);
    } else {
        $cek = $pdo->prepare("SELECT id_barang FROM barang WHERE kode_barang = ? AND is_active = 0 LIMIT 1");
        $cek->execute([$kode]);
        $lama = $cek->fetch(PDO::FETCH_ASSOC);

        if ($lama) {
            $stmt = $pdo->prepare("UPDATE barang SET nama_barang=?, harga=?, stok=?, is_active=1 WHERE id_barang=?");
            $stmt->execute([$nama, $harga, $stok, $lama['id_barang']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO barang (kode_barang, nama_barang, harga, stok, is_active) VALUES (?,?,?,?,1)");
            $stmt->execute([$kode, $nama, $harga, $stok]);
        }
    }

    header('Location: barang.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('UPDATE barang SET is_active = 0 WHERE id_barang = ?')->execute([$id]);
    header('Location: barang.php');
    exit;
}

// ===== QUERY DATA =====
$barang = $pdo->query('SELECT * FROM barang WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
?>

<style>
/* 🌈 Tema Ungu Elegan */
.page-title {
  text-align: center;
  color: #4c1d95;
  font-size: 28px;
  margin-bottom: 25px;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

/* 🧾 Table bergaya */
.table-wrap {
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(124,58,237,0.15);
  padding: 20px;
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
th {
  background: linear-gradient(90deg,#7c3aed,#a855f7);
  color: white;
  font-weight: 700;
  text-align: left;
  padding: 12px;
  border-top-left-radius: 6px;
  border-top-right-radius: 6px;
}
td {
  padding: 12px;
  border-bottom: 1px solid #ede9fe;
  color: #333;
}
tr:hover td {
  background: #faf5ff;
}
.btn {
  display: inline-block;
  padding: 8px 14px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}
.btn-warning {
  background: #facc15;
  color: #000 !important;
  box-shadow: 0 3px 8px rgba(250,204,21,0.3);
}
.btn-warning:hover { background: #fde047; }
.btn-danger {
  background: #ef4444;
  color: white;
  box-shadow: 0 3px 8px rgba(239,68,68,0.3);
}
.btn-danger:hover { background: #dc2626; }

/* 🪄 Form Tambah Barang */
.add-form {
  margin-top: 35px;
  background: linear-gradient(145deg,#faf5ff,#f3e8ff);
  padding: 25px;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(124,58,237,0.15);
  animation: fadeIn 0.8s ease;
}
.add-form h4 {
  color: #4c1d95;
  margin-bottom: 16px;
  text-align: center;
}
.add-form label {
  display: block;
  color: #5b21b6;
  font-weight: 600;
  margin-bottom: 6px;
}
.add-form input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #d8b4fe;
  background: #faf5ff;
  margin-bottom: 14px;
}
.btn-primary {
  background: linear-gradient(90deg,#7c3aed,#9333ea);
  color: white !important;
  font-weight: 600;
  border: none;
  box-shadow: 0 3px 8px rgba(147,51,234,0.3);
  transition: all 0.25s ease;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(147,51,234,0.45);
}

/* ✨ Animasi */
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}
</style>

<h3 class="page-title">Manajemen Barang</h3>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Kode</th>
        <th>Nama</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($barang as $b): ?>
        <tr>
          <td><?= $b['id_barang'] ?></td>
          <td><?= htmlspecialchars($b['kode_barang']) ?></td>
          <td><?= htmlspecialchars($b['nama_barang']) ?></td>
          <td><?= number_format($b['harga'], 2, ",", ".") ?></td>
          <td><?= $b['stok'] ?></td>
          <td>
            <a href="barang_edit.php?id=<?= $b['id_barang'] ?>" class="btn btn-warning">Edit</a>
            <a href="?delete=<?= $b['id_barang'] ?>" class="btn btn-danger" onclick="return confirm('Nonaktifkan barang ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($barang)): ?>
        <tr><td colspan="6" style="text-align:center; color:#777;">Belum ada data barang</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="add-form">
  <h4>Tambah Barang</h4>
  <form method="post">
    <input type="hidden" name="save_barang" value="1"/>
    <label>Kode Barang</label>
    <input name="kode" required />

    <label>Nama Barang</label>
    <input name="nama" required />

    <label>Harga</label>
    <input name="harga" type="number" step="0.01" required />

    <label>Stok</label>
    <input name="stok" type="number" required />

    <button class="btn btn-primary">Simpan</button>
  </form>
</div>

<?php require '../includes/footer.php'; ?>
