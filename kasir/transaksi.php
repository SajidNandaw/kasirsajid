<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

$barang_list = $pdo->query('SELECT * FROM barang ORDER BY nama_barang ASC')->fetchAll(PDO::FETCH_ASSOC);
$pelanggan_list = $pdo->query('SELECT * FROM pelanggan ORDER BY nama ASC')->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $idb = (int)$_POST['id_barang'];
    $qty = max(1, (int)$_POST['qty']);
    $st = $pdo->prepare('SELECT * FROM barang WHERE id_barang = ?');
    $st->execute([$idb]);
    $it = $st->fetch(PDO::FETCH_ASSOC);

    if ($it && $qty <= $it['stok']) {
        if (isset($_SESSION['cart'][$idb])) {
            $_SESSION['cart'][$idb]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$idb] = [
                'id'    => $idb,
                'nama'  => $it['nama_barang'],
                'harga' => $it['harga'],
                'qty'   => $qty
            ];
        }
        header('Location: transaksi.php');
        exit;
    } else {
        $msg = "Stok barang '" . htmlspecialchars($it['nama_barang'] ?? 'Unknown') . "' tidak mencukupi.";
    }
}

if (isset($_GET['remove'])) {
    $r = (int)$_GET['remove'];
    unset($_SESSION['cart'][$r]);
    header('Location: transaksi.php');
    exit;
}

if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $msg = 'Keranjang kosong.';
    } else {
        $mode = $_POST['tipe_pelanggan'] ?? 'non_member';
        if ($mode === 'member' && !empty($_POST['id_pelanggan'])) {
            $stmt = $pdo->prepare("SELECT nama FROM pelanggan WHERE id_pelanggan = ?");
            $stmt->execute([$_POST['id_pelanggan']]);
            $pelanggan = $stmt->fetchColumn() ?: 'Member Tidak Dikenal';
        } else {
            $pelanggan = trim($_POST['pelanggan'] ?? '');
            if ($pelanggan === '') $pelanggan = 'Umum';
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO transaksi (tanggal,id_user,pelanggan) VALUES (NOW(),?,?)');
            $stmt->execute([$_SESSION['user']['id_user'], $pelanggan]);
            $idtr = $pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO transaksi_detail (id_transaksi,id_barang,qty,subtotal) VALUES (?,?,?,?)');
            $up  = $pdo->prepare('UPDATE barang SET stok = stok - ? WHERE id_barang = ?');
            foreach ($_SESSION['cart'] as $c) {
                $sub = $c['harga'] * $c['qty'];
                $ins->execute([$idtr, $c['id'], $c['qty'], $sub]);
                $up->execute([$c['qty'], $c['id']]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];

            header("Location: nota.php?id=" . $idtr);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal menyimpan transaksi: ' . $e->getMessage();
        }
    }
}

$title = 'Transaksi';
require '../includes/header.php';
?>

<style>
  body {
    background: linear-gradient(135deg, #6d28d9, #8b5cf6, #a78bfa);
    min-height: 100vh;
    font-family: 'Segoe UI', sans-serif;
    color: #222;
  }

  h2 {
    color: white;
    text-align: center;
    font-weight: 700;
    margin-bottom: 30px;
  }

  .row {
    display: flex;
    gap: 20px;
    justify-content: center;
  }

  .col-6 {
    flex: 0 0 46%;
  }

  .card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 20px;
    transition: 0.3s;
  }

  .card:hover {
    transform: translateY(-3px);
  }

  label {
    font-weight: 600;
    color: #4c1d95;
  }

  select, input[type="text"], input[type="number"] {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 8px;
    outline: none;
    transition: 0.3s;
  }

  select:focus, input:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 5px rgba(139,92,246,0.4);
  }

  .btn {
    background: #7e22ce;
    color: #fff !important;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    transition: 0.25s;
    cursor: pointer;
  }

  .btn:hover {
    background: #6d28d9;
    transform: scale(1.05);
  }

  .btn-ghost {
    color: #7e22ce;
    text-decoration: none;
    font-weight: 600;
  }

  .btn-ghost:hover {
    text-decoration: underline;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
  }

  th {
    background: #7e22ce;
    color: #fff;
    padding: 10px;
    text-align: left;
  }

  td {
    padding: 10px;
    border-bottom: 1px solid #eee;
  }

  tr:hover {
    background-color: #f3e8ff;
  }

  #search_member {
    width: 100%;
    margin-bottom: 6px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 8px;
  }

  .btn-text-black {
    background: #a855f7;
    color: white !important;
    font-weight: 600;
  }

  .btn-text-black:hover {
    background: #9333ea;
  }

  .hidden {
    display: none;
  }
</style>

<h2>Transaksi Penjualan</h2>

<?php if (isset($msg)): ?>
  <div class="card" style="border-left:4px solid #facc15; margin-bottom:12px;">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-6">
    <div class="card">
      <h3 class="mb-3" style="color:#4c1d95;">Tambah Barang</h3>
      <form method="post">
        <label>Pilih Barang</label>
        <select name="id_barang">
          <?php foreach ($barang_list as $b): ?>
            <option value="<?= $b['id_barang'] ?>" <?= $b['stok'] <= 0 ? 'disabled' : '' ?>>
              <?= htmlspecialchars($b['kode_barang'].' - '.$b['nama_barang'].' (Stok: '.$b['stok'].')') ?>
            </option>
          <?php endforeach; ?>
        </select>

        <div class="mb-3">
          <label>Qty</label>
          <input type="number" name="qty" value="1" min="1" />
        </div>

        <button type="submit" name="add_item" class="btn">Tambah ke Keranjang</button>
      </form>
    </div>
  </div>

  <div class="col-6">
    <div class="card">
      <h3 class="mb-2" style="color:#4c1d95;">Keranjang</h3>
      <?php if (empty($_SESSION['cart'])): ?>
        <div class="small">Keranjang kosong.</div>
      <?php else: ?>
        <table>
          <thead><tr><th>Nama</th><th>Qty</th><th>Harga</th><th>Subtotal</th><th></th></tr></thead>
          <tbody>
            <?php $total = 0;
            foreach ($_SESSION['cart'] as $c):
              $subtotal = $c['qty'] * $c['harga'];
              $total += $subtotal; ?>
              <tr>
                <td><?= htmlspecialchars($c['nama']) ?></td>
                <td><?= $c['qty'] ?></td>
                <td>Rp <?= number_format($c['harga'], 0, ",", ".") ?></td>
                <td>Rp <?= number_format($subtotal, 0, ",", ".") ?></td>
                <td><a href="?remove=<?= $c['id'] ?>" class="btn-ghost">Hapus</a></td>
              </tr>
            <?php endforeach; ?>
            <tr>
              <td colspan="3"><strong>Total</strong></td>
              <td colspan="2"><strong>Rp <?= number_format($total, 0, ",", ".") ?></strong></td>
            </tr>
          </tbody>
        </table>

        <form method="post" style="margin-top:12px;">
          <div class="mb-3">
            <label>Pelanggan</label>
            <select name="tipe_pelanggan" id="tipe_pelanggan" onchange="togglePelangganInput()" style="margin-bottom:8px;">
              <option value="non_member">Bukan Member</option>
              <option value="member">Member</option>
            </select>

            <div id="input_non_member">
              <input type="text" name="pelanggan" placeholder="Isi nama pelanggan" />
            </div>

            <div id="input_member" class="hidden">
              <input type="text" id="search_member" placeholder="Cari nama member..." onkeyup="filterMember()">
              <select name="id_pelanggan" id="member_select">
                <option value="">Pilih Nama Member</option>
                <?php foreach ($pelanggan_list as $p): ?>
                  <option value="<?= $p['id_pelanggan'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <button type="submit" name="checkout" class="btn-text-black">Checkout</button>
        </form>

        <script>
          function togglePelangganInput() {
            const tipe = document.getElementById('tipe_pelanggan').value;
            const memberDiv = document.getElementById('input_member');
            const nonMemberDiv = document.getElementById('input_non_member');
            if (tipe === 'member') {
              memberDiv.classList.remove('hidden');
              nonMemberDiv.classList.add('hidden');
            } else {
              memberDiv.classList.add('hidden');
              nonMemberDiv.classList.remove('hidden');
            }
          }

          function filterMember() {
            const input = document.getElementById('search_member');
            const filter = input.value.toLowerCase();
            const select = document.getElementById('member_select');
            const options = select.getElementsByTagName('option');
            for (let i = 0; i < options.length; i++) {
              const txt = options[i].textContent || options[i].innerText;
              options[i].style.display = txt.toLowerCase().includes(filter) ? "" : "none";
            }
          }
        </script>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require '../includes/footer.php'; ?>
