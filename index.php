
<?php
require_once "koneksi.php";

// Menghitung jumlah siswa
$querySiswa = mysqli_query($conn, "SELECT COUNT(*) AS total FROM siswa");
$totalSiswa = mysqli_fetch_assoc($querySiswa)['total'];

// Menghitung jumlah jadwal piket
$queryJadwal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jadwal_piket");
$totalJadwal = mysqli_fetch_assoc($queryJadwal)['total'];

// Mengambil jadwal piket
$queryData = mysqli_query($conn, "
    SELECT 
        jp.id_jadwal,
        jp.hari,
        s.nama_siswa,
        s.nis
    FROM jadwal_piket jp
    JOIN siswa s ON jp.id_siswa = s.id_siswa
    ORDER BY FIELD(
        jp.hari,
        'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'
    ), s.nama_siswa ASC
");

// Mengelompokkan jadwal berdasarkan hari
$jadwal = [];

while ($data = mysqli_fetch_assoc($queryData)) {
    $jadwal[$data['hari']][] = $data;
}

// Menentukan hari ini
$namaHari = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];

$hariIni = $namaHari[date('l')];
$tanggalHariIni = date('d-m-Y');

// Jumlah petugas hari ini
$totalHariIni = isset($jadwal[$hariIni])
    ? count($jadwal[$hariIni])
    : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | PiketKu</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .menu-link {
            transition: all 0.25s ease;
        }

        .menu-link:hover {
            transform: translateX(4px);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .day-card {
            transition: all 0.25s ease;
        }

        .day-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.06);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#172554] flex flex-col shrink-0">

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

                <!-- Dashboard Aktif -->
                <a href="index.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-950/20">

                    <i class="fa-solid fa-house w-5"></i>
                    Dashboard

                    <i class="fa-solid fa-chevron-right text-xs ml-auto"></i>
                </a>

                <a href="siswa.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-900 hover:text-white">

                    <i class="fa-solid fa-users w-5"></i>
                    Data Siswa
                </a>

                <a href="jadwal.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-900 hover:text-white">

                    <i class="fa-solid fa-calendar-days w-5"></i>
                    Jadwal Piket
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
                    Kelola jadwal piket kelas dengan mudah dan teratur melalui PiketKu.
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
                    Dashboard
                </h2>

                <p class="text-sm text-slate-400 mt-1">
                    Selamat datang di sistem jadwal piket kelas.
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


        <!-- ISI DASHBOARD -->
        <div class="p-8">

            <!-- SAPAAN -->
            <div class="mb-8">

                <div class="flex items-center gap-2 mb-2">

                    <h2 class="text-2xl font-extrabold text-slate-800">
                        Halo, Admin!
                    </h2>

                    <span class="text-2xl">👋</span>

                </div>

                <p class="text-sm text-slate-500">
                    Berikut ringkasan jadwal piket kelas hari ini.
                </p>

            </div>


            <!-- STATISTIK -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

                <!-- TOTAL SISWA -->
                <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-sm text-slate-500 font-medium">
                                Total Siswa
                            </p>

                            <h3 class="text-3xl font-extrabold mt-3 text-slate-800">
                                <?= $totalSiswa ?>
                            </h3>

                            <p class="text-xs text-slate-400 mt-2">
                                Siswa terdaftar
                            </p>

                        </div>

                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">

                            <i class="fa-solid fa-users text-xl"></i>

                        </div>

                    </div>

                    <div class="mt-5 h-1 bg-blue-100 rounded-full overflow-hidden">
                        <div class="h-full w-2/3 bg-blue-600 rounded-full"></div>
                    </div>

                </div>


                <!-- TOTAL JADWAL -->
                <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-sm text-slate-500 font-medium">
                                Total Jadwal
                            </p>

                            <h3 class="text-3xl font-extrabold mt-3 text-slate-800">
                                <?= $totalJadwal ?>
                            </h3>

                            <p class="text-xs text-slate-400 mt-2">
                                Penempatan petugas
                            </p>

                        </div>

                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">

                            <i class="fa-solid fa-calendar-days text-xl"></i>

                        </div>

                    </div>

                    <div class="mt-5 h-1 bg-indigo-100 rounded-full overflow-hidden">
                        <div class="h-full w-2/3 bg-indigo-600 rounded-full"></div>
                    </div>

                </div>


                <!-- PETUGAS HARI INI -->
                <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-sm text-slate-500 font-medium">
                                Petugas Hari Ini
                            </p>

                            <h3 class="text-3xl font-extrabold mt-3 text-slate-800">
                                <?= $totalHariIni ?>
                            </h3>

                            <p class="text-xs text-slate-400 mt-2">
                                <?= $hariIni ?>
                            </p>

                        </div>

                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">

                            <i class="fa-solid fa-user-check text-xl"></i>

                        </div>

                    </div>

                    <div class="mt-5 h-1 bg-emerald-100 rounded-full overflow-hidden">
                        <div class="h-full w-2/3 bg-emerald-500 rounded-full"></div>
                    </div>

                </div>

            </div>


            <!-- JADWAL HARI INI -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">

                    <div>

                        <div class="flex items-center gap-2">

                            <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">

                                <i class="fa-solid fa-calendar-day"></i>

                            </div>

                            <h3 class="font-bold text-lg text-slate-800">
                                Jadwal Piket Hari Ini
                            </h3>

                        </div>

                        <p class="text-sm text-slate-400 mt-2">
                            <?= $hariIni ?>, <?= $tanggalHariIni ?>
                        </p>

                    </div>

                    <span class="bg-blue-50 text-blue-700 border border-blue-100 px-4 py-2 rounded-lg text-xs font-bold">
                        <i class="fa-solid fa-circle text-blue-500 text-[7px] mr-1"></i>
                        Hari Ini
                    </span>

                </div>


                <div class="p-6">

                    <?php if ($totalHariIni > 0): ?>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                            <?php foreach ($jadwal[$hariIni] as $petugas): ?>

                                <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-blue-50 hover:border-blue-100 transition">

                                    <div class="w-11 h-11 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold">

                                        <?= strtoupper(substr($petugas['nama_siswa'], 0, 1)) ?>

                                    </div>

                                    <div class="min-w-0">

                                        <p class="font-bold text-sm text-slate-800">
                                            <?= htmlspecialchars($petugas['nama_siswa']) ?>
                                        </p>

                                        <p class="text-xs text-slate-400 mt-1">
                                            NIS: <?= htmlspecialchars($petugas['nis']) ?>
                                        </p>

                                    </div>

                                    <i class="fa-solid fa-circle-check text-emerald-500 ml-auto"></i>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="text-center py-10">

                            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto">

                                <i class="fa-regular fa-calendar-xmark text-3xl text-blue-300"></i>

                            </div>

                            <p class="font-semibold text-slate-600 mt-4">
                                Belum ada jadwal piket hari ini.
                            </p>

                            <p class="text-sm text-slate-400 mt-1">
                                Silakan atur jadwal melalui menu Jadwal Piket.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- JADWAL MINGGUAN -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-100">

                    <div class="flex items-center gap-2">

                        <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">

                            <i class="fa-solid fa-calendar-week"></i>

                        </div>

                        <h3 class="font-bold text-lg text-slate-800">
                            Ringkasan Jadwal Mingguan
                        </h3>

                    </div>

                    <p class="text-sm text-slate-400 mt-2">
                        Pembagian petugas piket setiap hari.
                    </p>

                </div>


                <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

                    <?php
                    $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

                    foreach ($daftarHari as $hari):
                    ?>

                        <div class="day-card border border-slate-200 rounded-xl p-4 bg-white">

                            <div class="flex items-center justify-between mb-4">

                                <div class="flex items-center gap-2">

                                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">

                                        <i class="fa-solid fa-calendar-day text-sm"></i>

                                    </div>

                                    <h4 class="font-bold text-slate-700">
                                        <?= $hari ?>
                                    </h4>

                                </div>

                                <span class="text-xs bg-blue-50 text-blue-700 px-3 py-1 rounded-full font-semibold">

                                    <?= isset($jadwal[$hari]) ? count($jadwal[$hari]) : 0 ?> Petugas

                                </span>

                            </div>


                            <?php if (!empty($jadwal[$hari])): ?>

                                <div class="space-y-3">

                                    <?php foreach ($jadwal[$hari] as $petugas): ?>

                                        <div class="flex items-center gap-2 text-sm text-slate-600">

                                            <i class="fa-solid fa-circle-user text-blue-500"></i>

                                            <span>
                                                <?= htmlspecialchars($petugas['nama_siswa']) ?>
                                            </span>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php else: ?>

                                <div class="bg-slate-50 rounded-lg p-3">

                                    <p class="text-sm text-slate-400">
                                        <i class="fa-regular fa-calendar-minus mr-1"></i>
                                        Belum ada petugas.
                                    </p>

                                </div>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>


            <!-- FOOTER -->
            <footer class="text-center text-xs text-slate-400 mt-8 pb-4">

                © <?= date('Y') ?> <span class="font-bold text-blue-600">PiketKu.</span>
                Sistem Informasi Jadwal Piket Kelas.

            </footer>

        </div>

    </main>

</div>

</body>
</html>
