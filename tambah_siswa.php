
<?php
require_once "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: siswa.php");
    exit;
}

$nama = trim($_POST['nama_siswa'] ?? '');
$nis = trim($_POST['nis'] ?? '');
$jk = $_POST['jenis_kelamin'] ?? '';

if ($nama === '' || $nis === '' ||
    !in_array($jk, ['Laki-laki', 'Perempuan'], true)) {
    header("Location: siswa.php?pesan=kosong");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO siswa (nama_siswa, nis, jenis_kelamin)
     VALUES (?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, "sss", $nama, $nis, $jk);

if (mysqli_stmt_execute($stmt)) {
    header("Location: siswa.php?pesan=berhasil");
    exit;
}

if (mysqli_errno($conn) == 1062) {
    header("Location: siswa.php?pesan=nis_sama");
    exit;
}

die("Gagal menambahkan siswa: " . mysqli_error($conn));
?>
