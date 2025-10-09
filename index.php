<?php
require 'config.php';

// Jika user sudah login, arahkan langsung ke dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$u]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $ok = false;
        if (function_exists('password_verify') &&
            (strpos($user['password'], '$2y$') === 0 || strpos($user['password'], '$argon2') === 0)) {
            $ok = password_verify($p, $user['password']);
        } else {
            $ok = ($p === $user['password']);
        }

        if ($ok) {
            unset($user['password']);
            $_SESSION['user'] = $user;
            header('Location: dashboard.php');
            exit;
        }
    }

    $err = 'Login gagal. Periksa username / password.';
}

$title = 'Login - Kasir';
require 'includes/header.php';
?>

<style>
  * {
    box-sizing: border-box;
  }

  html, body {
    height: 100%;
    margin: 0;
    font-family: 'Inter', sans-serif;
    overflow: hidden;
  }

  /* 🌈 Animated Gradient Background */
  body {
    background: linear-gradient(120deg, #7c3aed, #9333ea, #a855f7, #c084fc);
    background-size: 400% 400%;
    animation: gradientMove 12s ease infinite;
  }

  @keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  .login-container {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* ✨ Glassmorphism Box */
  .login-box {
    backdrop-filter: blur(15px);
    background: rgba(255, 255, 255, 0.15);
    padding: 40px 35px;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    width: 350px;
    text-align: center;
    color: #fff;
    animation: fadeInUp 0.8s ease;
  }

  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(25px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .login-box h2 {
    margin-bottom: 25px;
    font-weight: 800;
    font-size: 28px;
    letter-spacing: 1px;
  }

  .form-group {
    margin-bottom: 20px;
    text-align: left;
  }

  label {
    display: block;
    margin-bottom: 6px;
    color: #e9d5ff;
    font-size: 14px;
    font-weight: 600;
  }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    color: #111;
    background: rgba(255,255,255,0.8);
    transition: all 0.3s ease;
  }

  input:focus {
    outline: none;
    box-shadow: 0 0 10px rgba(167,139,250,0.6);
    transform: scale(1.02);
  }

  .btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #7c3aed, #9333ea);
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    font-weight: 700;
    transition: all 0.3s ease;
  }

  .btn:hover {
    background: linear-gradient(90deg, #6d28d9, #a855f7);
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }

  .btn:active {
    transform: translateY(1px);
  }

  .alert {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    padding: 10px;
    margin-bottom: 18px;
    border-radius: 10px;
    font-size: 14px;
    text-align: center;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255,255,255,0.4);
  }

  @media (max-width: 400px) {
    .login-box {
      width: 90%;
      padding: 30px 20px;
    }
  }
</style>

<div class="login-container">
  <div class="login-box">
    <h2>Login</h2>

    <?php if ($err): ?>
      <div class="alert"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required placeholder="Masukkan username">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Masukkan password">
      </div>
      <button type="submit" class="btn">Masuk</button>
    </form>
  </div>
</div>

<!-- 🌟 Sedikit animasi JS untuk efek masuk -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
      input.addEventListener('focus', () => input.parentNode.classList.add('active'));
      input.addEventListener('blur', () => input.parentNode.classList.remove('active'));
    });
  });
</script>

<?php require 'includes/footer.php'; ?>
