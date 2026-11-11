
<?php
require_once "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

// Mengambil tanggal dan hari saat ini
$tanggal = date('Y-m-d');

$daftarHari = [
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
    'Sunday'    => 'Minggu'
];

$hari = $daftarHari[date('l')];

// Memperbarui status pelaksanaan piket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($id_siswa > 0 && in_array($status, ['Sudah', 'Belum'], true)) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO pelaksanaan_piket (id_siswa, tanggal, status)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $id_siswa,
            $tanggal,
            $status
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: pelaksanaan.php");
    exit;
}

// Mengambil siswa yang memiliki jadwal pada hari ini
$stmt = mysqli_prepare($conn, "
    SELECT
        s.id_siswa,
        s.nama_siswa,
        s.nis,
        COALESCE(p.status, 'Belum') AS status
    FROM jadwal_piket jp
    JOIN siswa s ON jp.id_siswa = s.id_siswa
    LEFT JOIN pelaksanaan_piket p
        ON p.id_siswa = s.id_siswa
        AND p.tanggal = ?
    WHERE jp.hari = ?
    ORDER BY s.nama_siswa ASC
");

mysqli_stmt_bind_param($stmt, "ss", $tanggal, $hari);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$siswaHariIni = [];

while ($data = mysqli_fetch_assoc($result)) {
    $siswaHariIni[] = $data;
}

mysqli_stmt_close($stmt);

// Menghitung statistik
$total = count($siswaHariIni);
$sudah = 0;
$belum = 0;

foreach ($siswaHariIni as $siswa) {
    if ($siswa['status'] === 'Sudah') {
        $sudah++;
    } else {
        $belum++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaksanaan Piket - PiketKu</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .menu-link {
            transition: all 0.2s ease;
        }

        .menu-link:hover {
            transform: translateX(3px);
        }

        .stat-card {
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.07);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#172554] text-white min-h-screen shrink-0">

        <div class="p-6 border-b border-blue-900">
            <h1 class="text-xl font-extrabold">
                <i class="fa-solid fa-calendar-check text-blue-400 mr-2"></i>
                PiketKu
            </h1>
            <p class="text-xs text-blue-200 mt-1">
                Manajemen Jadwal Piket
            </p>
        </div>

        <div class="px-4 py-6">

            <p class="text-xs font-bold text-blue-300 mb-4 px-3 tracking-wider">
                MENU UTAMA
            </p>

            <nav class="space-y-2">

                <a href="index.php"
                   class="menu-link flex items-center gap-3 p-3 rounded-xl text-blue-100 hover:bg-blue-900">
                    <i class="fa-solid fa-house w-5"></i>
                    Dashboard
                </a>

                <a href="siswa.php"
                   class="menu-link flex items-center gap-3 p-3 rounded-xl text-blue-100 hover:bg-blue-900">
                    <i class="fa-solid fa-users w-5"></i>
                    Data Siswa
                </a>

                <a href="jadwal.php"
                   class="menu-link flex items-center gap-3 p-3 rounded-xl text-blue-100 hover:bg-blue-900">
                    <i class="fa-solid fa-calendar-days w-5"></i>
                    Jadwal Piket
                </a>

                <a href="pelaksanaan.php"
                   class="menu-link flex items-center gap-3 p-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-950/20">
                    <i class="fa-solid fa-clipboard-check w-5"></i>
                    Pelaksanaan Piket
                </a>

                <a href="laporan.php"
                   class="menu-link flex items-center gap-3 p-3 rounded-xl text-blue-100 hover:bg-blue-900">
                    <i class="fa-solid fa-file-lines w-5"></i>
                    Laporan
                </a>

            </nav>
        </div>

        <div class="px-6 mt-10">
            <div class="border-t border-blue-900 pt-5">
                <p class="text-xs text-blue-300">
                    PiketKu System
                </p>
                <p class="text-xs text-blue-400 mt-1">
                    Manajemen Piket Kelas
                </p>
            </div>
        </div>

    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 min-w-0">

        <!-- HEADER -->
        <header class="bg-white border-b border-slate-200 px-8 py-5 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800">
                    Pelaksanaan Piket
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Kelola dan pantau kegiatan piket siswa.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-700">Administrator</p>
                    <p class="text-xs text-slate-400">Pengelola Sistem</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
        </header>

        <div class="p-8">

            <!-- JUDUL HALAMAN -->
            <div class="mb-7">
                <h3 class="text-2xl font-extrabold text-slate-800">
                    Aktivitas Piket Hari Ini
                </h3>
                <p class="text-sm text-slate-500 mt-2">
                    Pantau dan catat pelaksanaan piket siswa setiap hari.
                </p>
            </div>

            <!-- INFORMASI HARI -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-6 mb-7 text-white shadow-lg shadow-blue-200">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center">
                        <i class="fa-solid fa-calendar-day text-2xl"></i>
                    </div>

                    <div>
                        <p class="text-sm text-blue-100">
                            Pelaksanaan Hari Ini
                        </p>
                        <h3 class="text-xl font-extrabold mt-1">
                            <?= htmlspecialchars($hari) ?>,
                            <?= date('d-m-Y', strtotime($tanggal)) ?>
                        </h3>
                    </div>

                    <div class="ml-auto hidden sm:block text-right">
                        <p class="text-xs text-blue-100">Sistem PiketKu</p>
                        <p class="text-sm font-semibold mt-1">Monitoring Harian</p>
                    </div>
                </div>
            </div>

            <!-- STATISTIK -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">

                <!-- TOTAL PETUGAS -->
                <div class="stat-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Total Petugas
                            </p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-3">
                                <?= $total ?>
                            </h3>
                            <p class="text-xs text-slate-400 mt-2">
                                Siswa bertugas hari ini
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-users text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- SUDAH -->
                <div class="stat-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Sudah Melaksanakan
                            </p>
                            <h3 class="text-3xl font-extrabold text-emerald-600 mt-3">
                                <?= $sudah ?>
                            </h3>
                            <p class="text-xs text-slate-400 mt-2">
                                Piket telah selesai
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- BELUM -->
                <div class="stat-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Belum Melaksanakan
                            </p>
                            <h3 class="text-3xl font-extrabold text-amber-500 mt-3">
                                <?= $belum ?>
                            </h3>
                            <p class="text-xs text-slate-400 mt-2">
                                Menunggu pelaksanaan
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                            <i class="fa-solid fa-clock text-xl"></i>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TABEL PELAKSANAAN -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                <div class="p-6 border-b border-slate-100 flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <h3 class="font-extrabold text-lg text-slate-800">
                            <i class="fa-solid fa-clipboard-list text-blue-600 mr-2"></i>
                            Daftar Petugas Piket
                        </h3>
                        <p class="text-sm text-slate-400 mt-1">
                            Perbarui status siswa setelah melaksanakan piket.
                        </p>
                    </div>

                    <span class="px-4 py-2 rounded-xl bg-blue-50 text-blue-600 text-xs font-bold">
                        <?= $total ?> Petugas
                    </span>
                </div>

                <div class="overflow-x-auto">

                    <table class="w-full text-sm text-left">

                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-4 font-bold">No</th>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold">NIS</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                        <?php if ($total > 0): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($siswaHariIni as $siswa): ?>

                                <tr class="hover:bg-blue-50/40 transition">

                                    <td class="px-6 py-4 text-slate-500">
                                        <?= $no++ ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <i class="fa-solid fa-user"></i>
                                            </div>

                                            <span class="font-bold text-slate-700">
                                                <?= htmlspecialchars($siswa['nama_siswa']) ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        <?= htmlspecialchars($siswa['nis']) ?>
                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        <?php if ($siswa['status'] === 'Sudah'): ?>

                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 font-bold text-xs">
                                                <i class="fa-solid fa-circle-check"></i>
                                                Sudah
                                            </span>

                                        <?php else: ?>

                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 text-amber-600 font-bold text-xs">
                                                <i class="fa-solid fa-clock"></i>
                                                Belum
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        <form method="POST" class="inline-flex">

                                            <input type="hidden"
                                                   name="id_siswa"
                                                   value="<?= (int) $siswa['id_siswa'] ?>">

                                            <?php if ($siswa['status'] === 'Belum'): ?>

                                                <input type="hidden" name="status" value="Sudah">

                                                <button type="submit"
                                                    class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm">
                                                    <i class="fa-solid fa-check mr-1"></i>
                                                    Tandai Sudah
                                                </button>

                                            <?php else: ?>

                                                <input type="hidden" name="status" value="Belum">

                                                <button type="submit"
                                                    class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-sm">
                                                    <i class="fa-solid fa-rotate-left mr-1"></i>
                                                    Ubah ke Belum
                                                </button>

                                            <?php endif; ?>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">

                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                                    </div>

                                    <p class="font-bold text-slate-600">
                                        Belum Ada Jadwal Piket Hari Ini
                                    </p>

                                    <p class="text-xs text-slate-400 mt-2">
                                        Silakan atur jadwal melalui menu Jadwal Piket.
                                    </p>

                                    <a href="jadwal.php"
                                       class="inline-flex items-center gap-2 mt-5 px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">
                                        <i class="fa-solid fa-calendar-plus"></i>
                                        Atur Jadwal
                                    </a>

                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>
                    </table>

                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <p class="text-xs text-slate-400">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Status pelaksanaan piket dapat diperbarui melalui tombol aksi.
                    </p>
                </div>

            </div>

            <!-- FOOTER -->
            <div class="text-center text-xs text-slate-400 mt-8">
                &copy; <?= date('Y') ?> PiketKu. Manajemen Jadwal Piket.
            </div>

        </div>

    </main>

</div>

</body>
</html>
