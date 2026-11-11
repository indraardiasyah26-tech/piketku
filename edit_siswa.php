
<?php
require_once "koneksi.php";

if (!isset($_GET['id'])) {
    header("Location: siswa.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare($conn, 
    "SELECT * FROM siswa WHERE id_siswa = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data siswa tidak ditemukan.");
}

$pesan = "";

if (isset($_POST['update'])) {
    $nama = trim($_POST['nama_siswa']);
    $nis = trim($_POST['nis']);
    $jk = $_POST['jenis_kelamin'];

    $update = mysqli_prepare($conn, 
        "UPDATE siswa 
         SET nama_siswa = ?, nis = ?, jenis_kelamin = ?
         WHERE id_siswa = ?"
    );

    mysqli_stmt_bind_param($update, "sssi", $nama, $nis, $jk, $id);

    if (mysqli_stmt_execute($update)) {
        header("Location: siswa.php?pesan=diupdate");
        exit;
    } else {
        $pesan = mysqli_errno($conn) == 1062
            ? "NIS sudah digunakan siswa lain!"
            : "Gagal memperbarui data.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa | PiketKu</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-5">

    <div class="bg-white w-full max-w-xl rounded-2xl shadow-sm border border-slate-200 p-8">

        <h2 class="text-2xl font-bold text-slate-800">
            Edit Data Siswa
        </h2>

        <p class="text-sm text-slate-400 mt-2 mb-7">
            Perbarui informasi siswa.
        </p>

        <?php if ($pesan != ""): ?>
            <div class="bg-rose-50 text-rose-600 p-3 rounded-xl mb-5 text-sm">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">

            <div>
                <label class="block text-sm font-semibold mb-2">
                    Nama Siswa
                </label>

                <input type="text"
                       name="nama_siswa"
                       value="<?= htmlspecialchars($data['nama_siswa']) ?>"
                       required
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">
                    NIS
                </label>

                <input type="text"
                       name="nis"
                       value="<?= htmlspecialchars($data['nis']) ?>"
                       required
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">
                    Jenis Kelamin
                </label>

                <select name="jenis_kelamin" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

                    <option value="Laki-laki"
                        <?= $data['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>
                        Laki-laki
                    </option>

                    <option value="Perempuan"
                        <?= $data['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>
                        Perempuan
                    </option>

                </select>
            </div>

            <div class="flex justify-end gap-3 pt-3">

                <a href="siswa.php"
                   class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold">
                    Kembali
                </a>

                <button type="submit"
                        name="update"
                        class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</body>
</html>
