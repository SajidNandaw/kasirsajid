<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$title = 'Manajemen Users';

// ==============================
// LOGIKA TAMBAH / HAPUS USER
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    $role = $_POST['role'];

    $stmt = $pdo->prepare('INSERT INTO users (username,password,role) VALUES (?,?,?)');
    $stmt->execute([$u, $p, $role]);

    header('Location: users.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM users WHERE id_user = ?')->execute([$id]);
    header('Location: users.php');
    exit;
}

// ==============================
// AMBIL DATA USER
// ==============================
$users = $pdo->query('SELECT id_user, username, role FROM users')->fetchAll(PDO::FETCH_ASSOC);

// ==============================
// LOAD HEADER
// ==============================
require '../includes/header.php';
?>

<style>
/* === Tema Modern Flat Ungu === */
.wrapper {
  max-width: 900px;
  margin: 40px auto;
  background: #fff;
  border-radius: 14px;
  padding: 30px;
  box-shadow: 0 4px 14px rgba(124,58,237,0.1);
  animation: fadeIn 0.5s ease;
}

h3 {
  color: #4c1d95;
  text-align: center;
  font-weight: 800;
  margin-bottom: 25px;
  letter-spacing: 0.5px;
}

.table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 25px;
}

.table th, .table td {
  padding: 12px 14px;
  text-align: center;
}

.table th {
  background: #7c3aed;
  color: #fff;
  font-weight: 700;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.table tr:nth-child(even) {
  background: #faf5ff;
}

.table tr:hover td {
  background: #f3e8ff;
}

.btn {
  display: inline-block;
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
  transform: scale(1.05);
}

.btn-primary {
  background: #7c3aed;
  color: #fff;
}

.btn-primary:hover {
  background: #6d28d9;
  transform: translateY(-2px);
}

.btn-back {
  background: #f3e8ff;
  color: #5b21b6;
  margin-bottom: 25px;
  font-weight: 700;
}

.btn-back:hover {
  background: #ede9fe;
}

.form-card {
  background: #faf5ff;
  border: 1px solid #e9d5ff;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}

.form-card label {
  display: block;
  font-weight: 600;
  color: #4c1d95;
  margin-bottom: 6px;
}

.form-card input, .form-card select {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #d8b4fe;
  background: #fff;
  font-size: 14px;
  margin-bottom: 14px;
}

.form-card input:focus, .form-card select:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(167,139,250,0.3);
  outline: none;
}

@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}
</style>

<div class="wrapper">

  <a href="/kasirsajid/dashboard.php" class="btn btn-back">← Kembali ke Dashboard</a>

  <h3>👥 Manajemen Pengguna</h3>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $row): ?>
        <tr>
          <td><?= $row['id_user'] ?></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= htmlspecialchars(ucfirst($row['role'])) ?></td>
          <td>
            <?php if ($row['id_user'] != $_SESSION['user']['id_user']): ?>
              <a href="?delete=<?= $row['id_user'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus user ini?')">Hapus</a>
            <?php else: ?>
              <span class="text-muted" style="font-size:13px;">(tidak dapat hapus diri sendiri)</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="form-card">
    <h4 style="color:#5b21b6; text-align:center; margin-bottom:18px;">Tambah User Baru</h4>
    <form method="post">
      <input type="hidden" name="add_user" value="1"/>

      <label>Username</label>
      <input name="username" required />

      <label>Password</label>
      <input name="password" type="password" required />

      <label>Role</label>
      <select name="role" required>
        <option value="kasir">Kasir</option>
        <option value="admin">Admin</option>
      </select>

      <button class="btn btn-primary w-100">Tambah User</button>
    </form>
  </div>

</div>

<?php require '../includes/footer.php'; ?>
