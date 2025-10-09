<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM barang WHERE id_barang = ?');
$stmt->execute([$id]);
$b = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$b) {
    header('Location: barang.php');
    exit;
}

$title = 'Edit Barang';
require '../includes/header.php';
?>

<style>
/* 🌈 Tema Ungu Elegan */
.edit-container {
  max-width: 500px;
  margin: 50px auto;
  background: linear-gradient(145deg,#faf5ff,#f3e8ff);
  border-radius: 16px;
  padding: 30px 25px;
  box-shadow: 0 6px 18px rgba(124,58,237,0.15);
  animation: fadeIn 0.8s ease;
}

.edit-container h3 {
  text-align: center;
  color: #4c1d95;
  margin-bottom: 25px;
  font-size: 24px;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

label {
  display: block;
  font-weight: 600;
  color: #5b21b6;
  margin-bottom: 6px;
}

input[type="text"],
input[type="number"] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d8b4fe;
  border-radius: 8px;
  background: #faf5ff;
  margin-bottom: 14px;
  font-size: 14px;
  color: #1f1f1f;
  transition: border 0.2s, box-shadow 0.2s;
}
input:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(167,139,250,0.3);
  outline: none;
}

/* Tombol utama */
.btn-update {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(90deg,#7c3aed,#9333ea);
  color: white;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(124,58,237,0.3);
  transition: all 0.25s ease;
}
.btn-update:hover {
  background: linear-gradient(90deg,#6d28d9,#a855f7);
  transform: translateY(-2px);
}

/* Tombol kembali */
.btn-back {
  display: inline-block;
  margin-bottom: 20px;
  text-decoration: none;
  background: linear-gradient(90deg,#ede9fe,#ddd6fe);
  color: #4c1d95;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(124,58,237,0.15);
}
.btn-back:hover {
  background: linear-gradient(90deg,#ddd6fe,#c4b5fd);
  transform: translateY(-2px);
}

/* ✨ Animasi */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="edit-container">
  <a href="barang.php" class="btn-back">← Kembali</a>
  <h3>Edit Barang</h3>

  <form method="post" action="barang.php">
    <input type="hidden" name="save_barang" value="1"/>
    <input type="hidden" name="id" value="<?= $b['id_barang'] ?>"/>

    <label>Kode Barang</label>
    <input name="kode" value="<?= htmlspecialchars($b['kode_barang']) ?>" required />

    <label>Nama Barang</label>
    <input name="nama" value="<?= htmlspecialchars($b['nama_barang']) ?>" required />

    <label>Harga</label>
    <input name="harga" type="number" step="0.01" value="<?= $b['harga'] ?>" required />

    <label>Stok</label>
    <input name="stok" type="number" value="<?= $b['stok'] ?>" required />

    <button class="btn-update">Update Barang</button>
  </form>
</div>

<?php require '../includes/footer.php'; ?>
