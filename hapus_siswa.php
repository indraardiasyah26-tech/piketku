
<?php
require_once "koneksi.php";

if (!isset($_GET['id'])) {
    header("Location: siswa.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare($conn, 
    "DELETE FROM siswa WHERE id_siswa = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: siswa.php?pesan=dihapus");
    exit;
}

die("Gagal menghapus data siswa.");
?>
