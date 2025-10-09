<?php
require 'config.php';
require_login();
$title = 'Dashboard';
require 'includes/header.php';
?>

<style>
/* 🌈 Tema Dashboard */
.dashboard-header {
  text-align: center;
  margin-bottom: 30px;
  animation: fadeInDown 0.8s ease;
}
.dashboard-header h2 {
  font-size: 28px;
  color: #4c1d95;
  margin-bottom: 8px;
}
.dashboard-header p {
  color: #6b21a8;
  font-weight: 500;
  font-size: 15px;
}

/* 🎨 Grid layout — dibuat simetris */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  justify-content: center;
  gap: 22px;
  animation: fadeIn 1s ease;
  max-width: 900px;
  margin: 0 auto;
}

/* 🧊 Card */
.card-stat {
  background: linear-gradient(145deg, #faf5ff, #ede9fe);
  border-radius: 14px;
  padding: 25px 20px;
  box-shadow: 0 6px 16px rgba(124,58,237,0.15);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  text-align: center;
}
.card-stat::before {
  content: "";
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at top left, rgba(167,139,250,0.3), transparent 50%);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.card-stat:hover::before { opacity: 1; }
.card-stat:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 25px rgba(124,58,237,0.25);
}
.card-stat h5 {
  font-size: 16px;
  color: #5b21b6;
  margin-bottom: 12px;
  font-weight: 700;
}
.card-stat .number {
  font-size: 44px;
  color: #4c1d95;
  font-weight: 900;
}

/* ✨ Animasi */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 🌙 Responsif */
@media (max-width:600px){
  .dashboard-header h2 { font-size: 22px; }
  .number { font-size: 36px !important; }
}
</style>

<div class="dashboard-header">
  <h2>Dashboard</h2>
  <p>Selamat datang, 
    <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong> 
    (Role: <?= htmlspecialchars($_SESSION['user']['role']) ?>)
  </p>
</div>

<!-- Grid utama -->
<div class="dashboard-grid">
  <div class="card-stat">
    <h5>Total Transaksi</h5>
    <div class="number">
      <?= $pdo->query('SELECT COUNT(*) as c FROM transaksi')->fetch(PDO::FETCH_ASSOC)['c']; ?>
    </div>
  </div>

  <div class="card-stat">
    <h5>Jumlah Barang</h5>
    <div class="number">
      <?= $pdo->query('SELECT COUNT(*) as c FROM barang')->fetch(PDO::FETCH_ASSOC)['c']; ?>
    </div>
  </div>

  <div class="card-stat">
    <h5>Total Pengguna</h5>
    <div class="number">
      <?= $pdo->query('SELECT COUNT(*) as c FROM users')->fetch(PDO::FETCH_ASSOC)['c']; ?>
    </div>
  </div>

  <!-- Baris kedua tengah -->
  <div class="card-stat" style="grid-column: 1 / -1; justify-self: center; width: 60%; max-width: 350px;">
    <h5>Pelanggan Terdaftar</h5>
    <div class="number">
      <?= $pdo->query('SELECT COUNT(*) as c FROM pelanggan')->fetch(PDO::FETCH_ASSOC)['c']; ?>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
