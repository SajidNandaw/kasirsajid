# Kasir (PHP + MySQL) - versi sederhana (password plaintext)

Instruksi singkat:
1. Salin folder `kasir` ke `C:/xampp/htdocs/` (atau direktori htdocs XAMPP Anda).
2. Import file `kasir.sql` ke phpMyAdmin atau jalankan via `mysql` CLI untuk membuat database dan data contoh.
   - Nama database: `kasir`
3. Pastikan `config.php` cocok dengan kredensial MySQL Anda (default: root tanpa password).
4. Buka `http://localhost/kasir/` di browser.
5. Login default:
   - admin: `admin / admin123`
   - kasir: `kasir1 / kasir123`

Catatan keamanan: file ini menyimpan password dalam bentuk plaintext hanya sesuai permintaan; sebaiknya setelah berhasil, ganti ke `password_hash()` untuk keamanan.
