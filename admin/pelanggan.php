<?php
require_once __DIR__ . '/../config.php';
require_role('admin'); // hanya admin yang bisa akses

$title = 'Data Pelanggan';
require '../includes/header.php';

// ===== Hapus pelanggan =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?")->execute([$id]);
    echo "<script>
        alert('🗑️ Pelanggan berhasil dihapus!');
        window.location.href = 'pelanggan.php';
    </script>";
    exit;
}

// ===== Ambil data pelanggan =====
$stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
$pelanggan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
  :root {
    --ungu1: #ede9fe;
    --ungu2: #c4b5fd;
    --ungu3: #8b5cf6;
    --ungu4: #6d28d9;
  }

  body {
    background: linear-gradient(135deg, var(--ungu1), var(--ungu2));
  }

  h2 {
    text-align: center;
    color: var(--ungu4);
    font-weight: 700;
    margin-bottom: 20px;
  }

  .btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.25s ease;
    border: none;
  }

  .btn-black {
    background: white;
    color: var(--ungu4) !important;
    border: 2px solid var(--ungu3);
    box-shadow: 2px 2px 0 rgba(0, 0, 0, 0.1);
  }
  .btn-black:hover {
    background: var(--ungu3);
    color: #fff !important;
  }

  .btn-danger {
    background: #dc2626;
    color: #fff !important;
    border: none;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
  }
  .btn-danger:hover {
    background: #b91c1c;
  }

  .card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(107, 33, 168, 0.15);
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
  }

  th, td {
    border: 1px solid #e5e7eb;
    padding: 10px 12px;
    text-align: left;
  }

  th {
    background: var(--ungu3);
    color: #fff;
  }

  tr:nth-child(even) td {
    background: #f5f3ff;
  }

  .small {
    color: #666;
  }
</style>

<h2>Daftar Pelanggan Member</h2>

<div class="mb-3" style="text-align: center;">
  <a href="tambah_pelanggan.php" class="btn btn-black">+ Tambah Pelanggan</a>
</div>

<div class="card">
  <?php if (empty($pelanggan)): ?>
    <p class="small text-center">Belum ada data pelanggan.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Alamat</th>
          <th>Telepon</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pelanggan as $p): ?>
        <tr>
          <td><?= $p['id_pelanggan'] ?></td>
          <td><?= htmlspecialchars($p['nama']) ?></td>
          <td><?= htmlspecialchars($p['alamat']) ?></td>
          <td><?= htmlspecialchars($p['telepon']) ?></td>
          <td>
            <a href="?delete=<?= $p['id_pelanggan'] ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">
               Hapus
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>
