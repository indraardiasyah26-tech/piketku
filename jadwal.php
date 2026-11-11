
<?php
session_start();
require_once "koneksi.php";

$daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

$pesan = "";
$jenisPesan = "success";

// Proses menyimpan jadwal
if (isset($_POST['simpan_jadwal'])) {

    $hari = $_POST['hari'] ?? '';
    $siswaDipilih = $_POST['siswa'] ?? [];

    if (!in_array($hari, $daftarHari, true)) {
        $pesan = "Hari tidak valid.";
        $jenisPesan = "error";

    } elseif (empty($siswaDipilih)) {
        $pesan = "Pilih minimal satu siswa.";
        $jenisPesan = "error";

    } else {

        $siswaDipilih = array_unique(
            array_map('intval', $siswaDipilih)
        );

        $valid = true;

        $cek = mysqli_prepare($conn,
            "SELECT COUNT(*) AS total FROM siswa WHERE id_siswa = ?"
        );

        foreach ($siswaDipilih as $idSiswa) {

            if ($idSiswa <= 0) {
                $valid = false;
                break;
            }

            mysqli_stmt_bind_param($cek, "i", $idSiswa);
            mysqli_stmt_execute($cek);

            $hasil = mysqli_stmt_get_result($cek);
            $baris = mysqli_fetch_assoc($hasil);

            if ($baris['total'] != 1) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            $pesan = "Terdapat data siswa yang tidak valid.";
            $jenisPesan = "error";

        } else {

            mysqli_begin_transaction($conn);

            try {

                // Menghapus jadwal lama pada hari yang dipilih
                $hapus = mysqli_prepare($conn,
                    "DELETE FROM jadwal_piket WHERE hari = ?"
                );

                mysqli_stmt_bind_param($hapus, "s", $hari);
                mysqli_stmt_execute($hapus);

                // Menyimpan petugas baru
                $insert = mysqli_prepare($conn,
                    "INSERT INTO jadwal_piket (id_siswa, hari) VALUES (?, ?)"
                );

                foreach ($siswaDipilih as $idSiswa) {

                    mysqli_stmt_bind_param(
                        $insert, "is", $idSiswa, $hari
                    );

                    mysqli_stmt_execute($insert);
                }

                mysqli_commit($conn);

                header("Location: jadwal.php?pesan=berhasil");
                exit;

            } catch (Throwable $e) {

                mysqli_rollback($conn);

                $pesan = "Gagal menyimpan jadwal.";
                $jenisPesan = "error";
            }
        }
    }
}


// Proses menghapus satu petugas
if (isset($_POST['hapus_petugas'])) {

    $idJadwal = (int) $_POST['id_jadwal'];

    $hapus = mysqli_prepare($conn,
        "DELETE FROM jadwal_piket WHERE id_jadwal = ?"
    );

    mysqli_stmt_bind_param($hapus, "i", $idJadwal);

    if (mysqli_stmt_execute($hapus)) {
        header("Location: jadwal.php?pesan=dihapus");
        exit;
    }

    $pesan = "Gagal menghapus petugas.";
    $jenisPesan = "error";
}


// Notifikasi
if (isset($_GET['pesan'])) {

    if ($_GET['pesan'] == 'berhasil') {
        $pesan = "Jadwal piket berhasil disimpan.";

    } elseif ($_GET['pesan'] == 'dihapus') {
        $pesan = "Petugas berhasil dihapus dari jadwal.";
    }
}


// Mengambil data siswa
$querySiswa = mysqli_query($conn,
    "SELECT * FROM siswa ORDER BY nama_siswa ASC"
);


// Mengambil seluruh jadwal
$queryJadwal = mysqli_query($conn, "
    SELECT
        jp.id_jadwal,
        jp.hari,
        s.nama_siswa,
        s.nis
    FROM jadwal_piket jp
    JOIN siswa s ON jp.id_siswa = s.id_siswa
    ORDER BY FIELD(
        jp.hari,
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat'
    ), s.nama_siswa ASC
");


$jadwal = [];

while ($data = mysqli_fetch_assoc($queryJadwal)) {
    $jadwal[$data['hari']][] = $data;
}


function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jadwal Piket | PiketKu</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        input[type="checkbox"] {
            accent-color: #2563eb;
        }

        .menu-link {
            transition: all 0.25s ease;
        }

        .menu-link:hover {
            transform: translateX(4px);
        }

        .content-card {
            transition: all 0.3s ease;
        }

        .content-card:hover {
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        .student-card {
            transition: all 0.2s ease;
        }

        .student-card:hover {
            border-color: #60a5fa;
            background-color: #eff6ff;
        }

        .day-card {
            transition: all 0.25s ease;
        }

        .day-card:hover {
            transform: translateY(-3px);
            border-color: #93c5fd;
        }

    </style>

</head>


<body class="bg-slate-50 text-slate-800">

<div class="flex min-h-screen">


    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#172554] flex flex-col shrink-0 hidden md:flex">

        <!-- LOGO -->
        <div class="px-6 py-7 border-b border-blue-900">

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-950/30">

                    <i class="fa-solid fa-calendar-check text-xl"></i>

                </div>

                <div>

                    <h1 class="font-extrabold text-lg text-white">
                        PiketKu
                    </h1>

                    <p class="text-xs text-blue-200">
                        Manajemen Jadwal Piket
                    </p>

                </div>

            </div>

        </div>


        <!-- MENU -->
        <div class="px-5 pt-7">

            <p class="text-xs font-bold text-blue-300 uppercase tracking-wider mb-4">
                Menu Utama
            </p>

            <nav class="space-y-2">

                <a href="index.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-900 hover:text-white">

                    <i class="fa-solid fa-house w-5"></i>
                    Dashboard

                </a>


                <a href="siswa.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-900 hover:text-white">

                    <i class="fa-solid fa-users w-5"></i>
                    Data Siswa

                </a>


                <!-- MENU AKTIF -->
                <a href="jadwal.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-950/20">

                    <i class="fa-solid fa-calendar-days w-5"></i>
                    Jadwal Piket

                    <i class="fa-solid fa-chevron-right text-xs ml-auto"></i>

                </a>


                <a href="pelaksanaan.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-900 hover:text-white">

                    <i class="fa-solid fa-clipboard-check w-5"></i>
                    Pelaksanaan Piket

                </a>


                <a href="laporan.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-900 hover:text-white">

                    <i class="fa-solid fa-file-lines w-5"></i>
                    Laporan

                </a>

            </nav>

        </div>


        <!-- INFORMASI -->
        <div class="mt-auto p-5">

            <div class="bg-blue-900 rounded-xl p-4 border border-blue-800">

                <div class="flex items-center gap-2 text-blue-300 mb-2">

                    <i class="fa-solid fa-circle-info"></i>

                    <span class="font-bold text-sm text-white">
                        Informasi
                    </span>

                </div>

                <p class="text-xs text-blue-200 leading-relaxed">
                    Atur pembagian jadwal piket kelas dengan mudah dan teratur melalui PiketKu.
                </p>

            </div>

            <p class="text-center text-xs text-blue-400 mt-5">
                PiketKu v1.0
            </p>

        </div>

    </aside>



    <!-- KONTEN UTAMA -->
    <main class="flex-1 min-w-0">


        <!-- HEADER -->
        <header class="bg-white border-b border-slate-200 px-8 py-5 flex items-center justify-between shadow-sm">

            <div>

                <h2 class="text-xl font-bold text-slate-800">
                    Jadwal Piket
                </h2>

                <p class="text-sm text-slate-400 mt-1">
                    Atur pembagian petugas piket kelas.
                </p>

            </div>


            <div class="flex items-center gap-3">

                <div class="text-right">

                    <p class="text-sm font-bold text-slate-700">
                        Admin Kelas
                    </p>

                    <p class="text-xs text-slate-400">
                        Administrator
                    </p>

                </div>


                <div class="w-11 h-11 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center border border-blue-200">

                    <i class="fa-solid fa-user"></i>

                </div>

            </div>

        </header>



        <!-- ISI HALAMAN -->
        <div class="p-8">


            <!-- JUDUL -->
            <div class="mb-8">

                <h2 class="text-2xl font-extrabold text-slate-800">
                    Manajemen Jadwal Piket
                </h2>

                <p class="text-sm text-slate-500 mt-2">
                    Tentukan sendiri siswa yang bertugas setiap hari.
                </p>

            </div>



            <!-- RINGKASAN -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 mb-7 text-white shadow-lg shadow-blue-100">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-blue-100 text-sm font-medium">
                            Total Penempatan Petugas
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2">
                            <?= array_sum(array_map('count', $jadwal)) ?>
                        </h3>

                        <p class="text-xs text-blue-100 mt-2">
                            Jumlah seluruh siswa yang mendapatkan jadwal piket.
                        </p>

                    </div>

                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center">

                        <i class="fa-solid fa-calendar-check text-2xl"></i>

                    </div>

                </div>

            </div>



            <!-- NOTIFIKASI -->
            <?php if ($pesan != ""): ?>

                <div class="<?= $jenisPesan == 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?> border p-4 rounded-xl mb-6 text-sm">

                    <i class="fa-solid <?= $jenisPesan == 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> mr-2"></i>

                    <?= e($pesan) ?>

                </div>

            <?php endif; ?>



            <!-- FORM JADWAL -->
            <div class="content-card bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">


                <!-- JUDUL FORM -->
                <div class="mb-6 flex items-center gap-3">

                    <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">

                        <i class="fa-solid fa-calendar-plus text-lg"></i>

                    </div>

                    <div>

                        <h3 class="font-bold text-lg text-slate-800">
                            Atur Jadwal Piket
                        </h3>

                        <p class="text-sm text-slate-400 mt-1">
                            Pilih hari dan siswa yang akan bertugas.
                        </p>

                    </div>

                </div>



                <form method="POST">


                    <!-- PILIH HARI -->
                    <div class="mb-7 max-w-sm">

                        <label class="block text-sm font-semibold mb-2 text-slate-700">

                            <i class="fa-solid fa-calendar-day text-blue-600 mr-1"></i>
                            Pilih Hari

                        </label>


                        <select name="hari"
                                required
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                            <option value="">
                                Pilih hari piket
                            </option>


                            <?php foreach ($daftarHari as $hari): ?>

                                <option value="<?= e($hari) ?>">
                                    <?= e($hari) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>



                    <!-- PILIH SISWA -->
                    <div class="mb-6">


                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">

                            <label class="text-sm font-semibold text-slate-700">

                                <i class="fa-solid fa-users text-blue-600 mr-1"></i>
                                Pilih Petugas

                            </label>


                            <span class="text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-medium">

                                <i class="fa-solid fa-circle-info mr-1"></i>

                                Bisa memilih lebih dari satu siswa

                            </span>

                        </div>



                        <?php if (mysqli_num_rows($querySiswa) > 0): ?>


                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">


                                <?php while ($siswa = mysqli_fetch_assoc($querySiswa)): ?>


                                    <label class="student-card flex items-center gap-3 p-4 border border-slate-200 rounded-xl cursor-pointer">


                                        <input type="checkbox"
                                               name="siswa[]"
                                               value="<?= $siswa['id_siswa'] ?>"
                                               class="w-4 h-4">


                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">

                                            <i class="fa-solid fa-user"></i>

                                        </div>


                                        <div class="min-w-0">

                                            <p class="text-sm font-semibold text-slate-700">
                                                <?= e($siswa['nama_siswa']) ?>
                                            </p>

                                            <p class="text-xs text-slate-400 mt-1">
                                                NIS: <?= e($siswa['nis']) ?>
                                            </p>

                                        </div>


                                    </label>


                                <?php endwhile; ?>


                            </div>


                        <?php else: ?>


                            <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl p-5 text-sm">

                                <i class="fa-solid fa-circle-exclamation mr-2"></i>

                                Belum ada siswa. Silakan tambahkan siswa terlebih dahulu.

                                <a href="siswa.php" class="font-bold underline ml-1">
                                    Tambah siswa
                                </a>

                            </div>


                        <?php endif; ?>


                    </div>



                    <!-- TOMBOL SIMPAN -->
                    <div class="flex justify-end pt-5 border-t border-slate-100">


                        <button type="submit"
                                name="simpan_jadwal"
                                <?= mysqli_num_rows($querySiswa) == 0 ? 'disabled' : '' ?>
                                onclick="return confirm('Simpan jadwal untuk hari yang dipilih? Jadwal lama pada hari tersebut akan diganti.')"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-md shadow-blue-100 transition">


                            <i class="fa-solid fa-floppy-disk mr-2"></i>

                            Simpan Jadwal


                        </button>


                    </div>


                </form>

            </div>




            <!-- DAFTAR JADWAL -->
            <div class="mb-5">

                <h3 class="text-xl font-bold text-slate-800">
                    Daftar Jadwal Mingguan
                </h3>

                <p class="text-sm text-slate-400 mt-1">
                    Daftar siswa yang bertugas setiap hari.
                </p>

            </div>



            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">


                <?php foreach ($daftarHari as $hari): ?>


                    <div class="day-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">


                        <!-- HEADER HARI -->
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">


                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">

                                    <i class="fa-solid fa-calendar-day"></i>

                                </div>

                                <h4 class="font-bold text-lg text-slate-800">
                                    <?= e($hari) ?>
                                </h4>

                            </div>


                            <span class="bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1.5 rounded-full text-xs font-bold">

                                <i class="fa-solid fa-users mr-1"></i>

                                <?= isset($jadwal[$hari]) ? count($jadwal[$hari]) : 0 ?> Petugas

                            </span>


                        </div>



                        <!-- ISI HARI -->
                        <div class="p-5">


                            <?php if (!empty($jadwal[$hari])): ?>


                                <div class="space-y-3">


                                    <?php foreach ($jadwal[$hari] as $petugas): ?>


                                        <div class="flex items-center justify-between gap-3 bg-slate-50 p-3 rounded-xl hover:bg-blue-50 transition">


                                            <div class="flex items-center gap-3 min-w-0">


                                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">

                                                    <i class="fa-solid fa-user"></i>

                                                </div>


                                                <div class="min-w-0">

                                                    <p class="font-semibold text-sm text-slate-700">
                                                        <?= e($petugas['nama_siswa']) ?>
                                                    </p>

                                                    <p class="text-xs text-slate-400 mt-1">
                                                        NIS: <?= e($petugas['nis']) ?>
                                                    </p>

                                                </div>


                                            </div>



                                            <!-- HAPUS PETUGAS -->
                                            <form method="POST"
                                                  onsubmit="return confirm('Hapus siswa ini dari jadwal <?= e($hari) ?>?')">


                                                <input type="hidden"
                                                       name="id_jadwal"
                                                       value="<?= $petugas['id_jadwal'] ?>">


                                                <button type="submit"
                                                        name="hapus_petugas"
                                                        class="w-9 h-9 shrink-0 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition"
                                                        title="Hapus petugas">

                                                    <i class="fa-solid fa-trash"></i>

                                                </button>


                                            </form>


                                        </div>


                                    <?php endforeach; ?>


                                </div>


                            <?php else: ?>


                                <div class="text-center py-9">


                                    <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto">

                                        <i class="fa-regular fa-calendar-xmark text-2xl text-blue-300"></i>

                                    </div>


                                    <p class="text-sm font-semibold text-slate-500 mt-3">
                                        Belum ada petugas piket.
                                    </p>

                                    <p class="text-xs text-slate-400 mt-1">
                                        Silakan atur jadwal untuk hari ini.
                                    </p>


                                </div>


                            <?php endif; ?>


                        </div>


                    </div>


                <?php endforeach; ?>


            </div>



            <!-- FOOTER -->
            <footer class="text-center text-xs text-slate-400 mt-8 pb-4">

                © <?= date('Y') ?>

                <span class="font-bold text-blue-600">
                    PiketKu.
                </span>

                Sistem Informasi Jadwal Piket Kelas.

            </footer>


        </div>

    </main>

</div>

</body>
</html>
