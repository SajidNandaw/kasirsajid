<?php
if (!isset($title)) $title = 'Kasir App';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    :root {
      --bg:#f5f3ff;
      --card:#ffffff;
      --muted:#6b7280;
      --accent:#7c3aed;
      --accent-2:#a855f7;
      --accent-3:#c084fc;
      --radius:10px;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{
      font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
      background:var(--bg);
      color:#222;
    }

    /* 🌈 Header gradasi animasi */
    .site-header {
      background: linear-gradient(90deg,var(--accent),var(--accent-2),var(--accent-3));
      background-size: 300% 300%;
      animation: moveGradient 8s ease infinite;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    @keyframes moveGradient {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    .site-header .container {
      max-width: 1150px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
    }

    /* Logo / Brand */
    .brand {
      font-weight: 900;
      font-size: 22px;
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.25);
      transition: transform 0.2s ease;
    }
    .brand:hover { transform: scale(1.05); }

    /* 🧭 Navbar Links */
    .nav {
      display: flex;
      gap: 16px;
      align-items: center;
      transition: all 0.3s ease;
    }
    .nav a {
      display:inline-block;
      padding:8px 14px;
      border-radius:var(--radius);
      text-decoration:none;
      font-weight:600;
      font-size:14px;
      color:#fff;
      background:rgba(255,255,255,0.15);
      backdrop-filter: blur(4px);
      border:1px solid rgba(255,255,255,0.2);
      transition: all 0.25s ease;
    }
    .nav a:hover {
      background: #fff;
      color: var(--accent);
      box-shadow: 0 0 10px rgba(255,255,255,0.6);
      transform: translateY(-2px);
    }
    .nav a:active {
      transform: translateY(1px);
    }

    /* 🔽 Toggle (mobile) */
    .nav-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 4px;
    }
    .nav-toggle span {
      width: 25px;
      height: 3px;
      background: #fff;
      border-radius: 2px;
      transition: 0.3s;
    }

    /* Responsif */
    @media (max-width: 820px) {
      .nav {
        position: absolute;
        top: 58px;
        right: 15px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        flex-direction: column;
        padding: 12px;
        display: none;
      }
      .nav.show {
        display: flex;
        animation: fadeIn 0.3s ease;
      }
      @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-5px);}
        to {opacity: 1; transform: translateY(0);}
      }
      .nav a {
        width: 160px;
        text-align: center;
      }
      .nav-toggle {
        display: flex;
      }
    }

    /* Container utama */
    .container-main {
      max-width: 1100px;
      margin: 25px auto;
      padding: 18px;
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: 0 6px 18px rgba(124,58,237,0.15);
    }
  </style>
</head>
<body>

<?php 
if (!in_array($currentPage, ['nota.php','riwayat_cetak.php','laporan_cetak.php'])): ?> 
<header class="site-header">
  <div class="container">
    <div class="brand">
      <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['role']) : "Kasir App" ?>
    </div>

    <div class="nav-toggle" onclick="toggleNav()">
      <span></span><span></span><span></span>
    </div>

    <nav class="nav" id="navbar">
      <?php if (isset($_SESSION['user'])): ?>
        <a href="/kasirsajid/dashboard.php">Dashboard</a>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
          <a href="/kasirsajid/admin/barang.php">Barang</a>
          <a href="/kasirsajid/admin/users.php">Users</a>
          <a href="/kasirsajid/admin/laporan.php">Laporan</a>
          <a href="/kasirsajid/admin/pelanggan.php">Pelanggan</a>
        <?php else: ?>
          <a href="/kasirsajid/kasir/transaksi.php">Transaksi</a>
          <a href="/kasirsajid/kasir/riwayat.php">Riwayat</a>
        <?php endif; ?>
        <a href="/kasirsajid/logout.php">Logout (<?=htmlspecialchars($_SESSION['user']['username'])?>)</a>
      <?php else: ?>
        <?php if ($currentPage !== 'index.php'): ?>
          <a href="/kasirsajid/index.php">Login</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php endif; ?>

<?php if ($currentPage !== 'index.php'): ?>
<main class="container-main">
<?php endif; ?>

<script>
  function toggleNav() {
    document.getElementById('navbar').classList.toggle('show');
  }
</script>
