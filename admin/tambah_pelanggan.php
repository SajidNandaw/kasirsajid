<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$title = 'Tambah Pelanggan';
require '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);

    if ($nama !== '') {
        $stmt = $pdo->prepare("INSERT INTO pelanggan (nama, alamat, telepon) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $alamat, $telepon]);

        echo "<script>
            alert('✅ Pelanggan berhasil ditambahkan!');
            window.location.href = 'pelanggan.php';
        </script>";
        exit;
    } else {
        $error = 'Nama pelanggan wajib diisi.';
    }
}
?>

<style>
  body {
    background: linear-gradient(135deg, #e8e6ff, #f7f7ff);
    font-family: 'Poppins', sans-serif;
    color: #333;
  }

  h2 {
    text-align: center;
    margin-top: 30px;
    color: #2d2d2d;
    font-weight: 700;
  }

  .container {
    display: flex;
    justify-content: center;
    margin-top: 40px;
  }

  .card {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px 40px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
  }

  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #444;
  }

  input[type="text"] {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    outline: none;
    background: #f9f9f9;
    font-size: 14px;
    transition: 0.25s ease;
  }

  input[type="text"]:focus {
    border-color: #8b5cf6;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
  }

  .mb-3 {
    margin-bottom: 18px;
  }

  .btn {
    display: inline-block;
    background: linear-gradient(90deg, #8b5cf6, #6366f1);
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    cursor: pointer;
    width: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(99,102,241,0.3);
  }

  .btn-ghost {
    display: block;
    text-align: center;
    color: #555;
    text-decoration: none;
    margin-top: 14px;
    font-weight: 500;
    transition: color 0.2s ease;
  }

  .btn-ghost:hover {
    color: #000;
  }

  .error-card {
    background: #fff0f0;
    border-left: 4px solid #ef4444;
    padding: 10px;
    color: #b91c1c;
    border-radius: 8px;
    margin-bottom: 15px;
  }
</style>

<h2>Tambah Pelanggan Baru</h2>

<div class="container">
  <div class="card">
    <?php if (!empty($error)): ?>
      <div class="error-card"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" required>
      </div>
      <div class="mb-3">
        <label>Alamat</label>
        <input type="text" name="alamat">
      </div>
      <div class="mb-3">
        <label>Telepon</label>
        <input type="text" name="telepon"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
               maxlength="15">
      </div>
      <button type="submit" class="btn">Simpan</button>
      <a href="pelanggan.php" class="btn-ghost">← Kembali ke Daftar</a>
    </form>
  </div>
</div>

<?php require '../includes/footer.php'; ?>
