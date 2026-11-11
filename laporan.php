
<?php
require_once "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

// Filter tanggal
$tanggal_awal = $_GET['awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-d');

// Validasi tanggal
if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir) ||
    $tanggal_awal > $tanggal_akhir
) {
    $tanggal_awal = date('Y-m-01');
    $tanggal_akhir = date('Y-m-d');
}

// Mengambil data laporan
$stmt = mysqli_prepare($conn, "
    SELECT
        p.id_pelaksanaan,
        s.nama_siswa,
        s.nis,
        p.tanggal,
        p.status
    FROM pelaksanaan_piket p
    JOIN siswa s ON p.id_siswa = s.id_siswa
    WHERE p.tanggal BETWEEN ? AND ?
    ORDER BY p.tanggal DESC, s.nama_siswa ASC
");

mysqli_stmt_bind_param($stmt, "ss", $tanggal_awal, $tanggal_akhir);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$laporan = [];
$totalSudah = 0;
$totalBelum = 0;

while ($data = mysqli_fetch_assoc($result)) {
    $laporan[] = $data;

    if ($data['status'] === 'Sudah') {
        $totalSudah++;
    } else {
        $totalBelum++;
    }
}

$total = count($laporan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laporan Piket - PiketKu</title>

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

        @media print {
            aside,
            .no-print,
            header {
                display: none !important;
            }

            main {
                padding: 0 !important;
            }

            body {
                background: white !important;
            }

            .print-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }

            table {
                width: 100% !important;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }

            .print-title {
                display: block !important;
            }
        }

        .print-title {
            display: none;
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
                   class="menu-link flex items-center gap-3 p-3 rounded-xl text-blue-100 hover:bg-blue-900">
                    <i class="fa-solid fa-clipboard-check w-5"></i>
                    Pelaksanaan Piket
                </a>

                <a href="laporan.php"
                   class="menu-link flex items-center gap-3 p-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-950/20">
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
                    Laporan Piket
                </h2>

                <p class="text-xs text-slate-500 mt-1">
                    Rekapitulasi pelaksanaan piket siswa.
                </p>
            </div>

            <div class="flex items-center gap-3">

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-700">
                        Administrator
                    </p>
                    <p class="text-xs text-slate-400">
                        Pengelola Sistem
                    </p>
                </div>

                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

            </div>

        </header>

        <div class="p-8">

            <!-- JUDUL HALAMAN -->
            <div class="flex flex-wrap justify-between items-center gap-4 mb-7">

                <div>
                    <h3 class="text-2xl font-extrabold text-slate-800">
                        Rekapitulasi Laporan
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Lihat dan cetak riwayat pelaksanaan piket siswa.
                    </p>
                </div>

                <button onclick="window.print()"
                    class="no-print px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                    <i class="fa-solid fa-print mr-2"></i>
                    Cetak Laporan
                </button>

            </div>

            <!-- JUDUL KHUSUS CETAK -->
            <div class="print-title mb-6 text-center">
                <h1 class="text-2xl font-bold">LAPORAN PELAKSANAAN PIKET</h1>
                <p class="text-sm mt-2">
                    PiketKu - Manajemen Jadwal Piket
                </p>
            </div>

            <!-- FILTER TANGGAL -->
            <div class="print-card no-print bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">

                <div class="mb-5">
                    <h3 class="font-extrabold text-slate-800">
                        <i class="fa-solid fa-filter text-blue-600 mr-2"></i>
                        Filter Periode Laporan
                    </h3>

                    <p class="text-xs text-slate-400 mt-1">
                        Pilih rentang tanggal untuk melihat laporan.
                    </p>
                </div>

                <form method="GET" class="flex flex-wrap items-end gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Tanggal Awal
                        </label>

                        <input type="date"
                               name="awal"
                               value="<?= htmlspecialchars($tanggal_awal) ?>"
                               required
                               class="border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Tanggal Akhir
                        </label>

                        <input type="date"
                               name="akhir"
                               value="<?= htmlspecialchars($tanggal_akhir) ?>"
                               required
                               class="border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <button type="submit"
                        class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition">
                        <i class="fa-solid fa-magnifying-glass mr-2"></i>
                        Tampilkan
                    </button>

                    <a href="laporan.php"
                       class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold transition">
                        <i class="fa-solid fa-rotate-left mr-1"></i>
                        Reset
                    </a>

                </form>

            </div>

            <!-- STATISTIK -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-7">

                <!-- TOTAL -->
                <div class="stat-card print-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">

                    <div class="flex justify-between items-start">

                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Total Pelaksanaan
                            </p>

                            <h3 class="text-3xl font-extrabold text-slate-800 mt-3">
                                <?= $total ?>
                            </h3>

                            <p class="text-xs text-slate-400 mt-2">
                                Catatan pelaksanaan piket
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-clipboard-list text-xl"></i>
                        </div>

                    </div>
                </div>

                <!-- SUDAH -->
                <div class="stat-card print-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">

                    <div class="flex justify-between items-start">

                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Sudah Piket
                            </p>

                            <h3 class="text-3xl font-extrabold text-emerald-600 mt-3">
                                <?= $totalSudah ?>
                            </h3>

                            <p class="text-xs text-slate-400 mt-2">
                                Berhasil dilaksanakan
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-xl"></i>
                        </div>

                    </div>
                </div>

                <!-- BELUM -->
                <div class="stat-card print-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">

                    <div class="flex justify-between items-start">

                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Belum Piket
                            </p>

                            <h3 class="text-3xl font-extrabold text-amber-500 mt-3">
                                <?= $totalBelum ?>
                            </h3>

                            <p class="text-xs text-slate-400 mt-2">
                                Belum dilaksanakan
                            </p>
                        </div>

                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                            <i class="fa-solid fa-clock text-xl"></i>
                        </div>

                    </div>
                </div>

            </div>

            <!-- TABEL LAPORAN -->
            <div class="print-card bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                <div class="p-6 border-b border-slate-100">

                    <h3 class="font-extrabold text-lg text-slate-800">
                        <i class="fa-solid fa-file-lines text-blue-600 mr-2"></i>
                        Riwayat Pelaksanaan Piket
                    </h3>

                    <p class="text-sm text-slate-400 mt-2">
                        Periode:
                        <?= date('d-m-Y', strtotime($tanggal_awal)) ?>
                        sampai
                        <?= date('d-m-Y', strtotime($tanggal_akhir)) ?>
                    </p>

                </div>

                <div class="overflow-x-auto">

                    <table class="w-full text-sm text-left">

                        <thead class="bg-slate-50 text-slate-500">

                            <tr>
                                <th class="px-6 py-4 font-bold">No</th>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold">NIS</th>
                                <th class="px-6 py-4 font-bold">Tanggal</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                        <?php if ($total > 0): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($laporan as $data): ?>

                                <tr class="hover:bg-blue-50/40 transition">

                                    <td class="px-6 py-4 text-slate-500">
                                        <?= $no++ ?>
                                    </td>

                                    <td class="px-6 py-4 font-bold text-slate-700">
                                        <?= htmlspecialchars($data['nama_siswa']) ?>
                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        <?= htmlspecialchars($data['nis']) ?>
                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        <?= date('d-m-Y', strtotime($data['tanggal'])) ?>
                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        <?php if ($data['status'] === 'Sudah'): ?>

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

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">

                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-folder-open text-2xl"></i>
                                    </div>

                                    <p class="font-bold text-slate-600">
                                        Belum Ada Data Laporan
                                    </p>

                                    <p class="text-xs text-slate-400 mt-2">
                                        Data akan muncul setelah pelaksanaan piket tercatat.
                                    </p>

                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 no-print">
                    <p class="text-xs text-slate-400">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Gunakan tombol Cetak Laporan untuk mencetak atau menyimpan laporan sebagai PDF.
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
