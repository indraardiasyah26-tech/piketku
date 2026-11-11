
<?php
require_once "koneksi.php";

// Mengambil pesan notifikasi
$pesan = $_GET['pesan'] ?? '';

// Mengambil seluruh data siswa
$querySiswa = mysqli_query($conn, "
    SELECT * FROM siswa
    ORDER BY nama_siswa ASC
");

// Menghitung jumlah siswa
$queryTotal = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM siswa
");

$dataTotal = mysqli_fetch_assoc($queryTotal);
$total = $dataTotal['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Siswa | PiketKu</title>

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

        .content-card {
            transition: all 0.3s ease;
        }

        .content-card:hover {
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        input, select, button {
            transition: all 0.2s ease;
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

                <!-- MENU AKTIF -->
                <a href="siswa.php"
                   class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-950/20">

                    <i class="fa-solid fa-users w-5"></i>
                    Data Siswa

                    <i class="fa-solid fa-chevron-right text-xs ml-auto"></i>
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
                    Kelola data siswa kelas dengan mudah dan teratur melalui PiketKu.
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
                    Data Siswa
                </h2>

                <p class="text-sm text-slate-400 mt-1">
                    Kelola data siswa kelas.
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

                <div class="flex items-center gap-2 mb-2">

                    <h2 class="text-2xl font-extrabold text-slate-800">
                        Manajemen Data Siswa
                    </h2>

                </div>

                <p class="text-sm text-slate-500">
                    Tambahkan dan kelola seluruh data siswa kelas.
                </p>

            </div>


            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>

                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-5 text-sm">

                    <i class="fa-solid fa-circle-check mr-2"></i>

                    Data siswa berhasil ditambahkan.

                </div>

            <?php endif; ?>


            <?php if ($pesan != "" && $pesan != "berhasil"): ?>

                <div class="bg-rose-50 border border-rose-200 text-rose-600 p-4 rounded-xl mb-5 text-sm">

                    <i class="fa-solid fa-circle-exclamation mr-2"></i>

                    <?= htmlspecialchars($pesan) ?>

                </div>

            <?php endif; ?>


            <!-- RINGKASAN -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 mb-7 text-white shadow-lg shadow-blue-100">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-blue-100 text-sm font-medium">
                            Total Siswa Terdaftar
                        </p>

                        <h3 class="text-3xl font-extrabold mt-2">
                            <?= $total ?> Siswa
                        </h3>

                        <p class="text-xs text-blue-100 mt-2">
                            Data seluruh siswa yang tersimpan dalam sistem.
                        </p>

                    </div>

                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center">

                        <i class="fa-solid fa-users text-2xl"></i>

                    </div>

                </div>

            </div>


            <!-- FORM TAMBAH -->
            <div class="content-card bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-7">

                <div class="mb-6 flex items-center gap-3">

                    <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">

                        <i class="fa-solid fa-user-plus text-lg"></i>

                    </div>

                    <div>

                        <h3 class="font-bold text-lg text-slate-800">
                            Tambah Siswa
                        </h3>

                        <p class="text-sm text-slate-400 mt-1">
                            Masukkan informasi siswa baru.
                        </p>

                    </div>

                </div>


                <form method="POST" action="tambah_siswa.php">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                        <!-- NAMA -->
                        <div>

                            <label class="block text-sm font-semibold mb-2 text-slate-700">
                                Nama Lengkap
                            </label>

                            <input
                                type="text"
                                name="nama_siswa"
                                placeholder="Masukkan nama siswa"
                                required
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >

                        </div>


                        <!-- NIS -->
                        <div>

                            <label class="block text-sm font-semibold mb-2 text-slate-700">
                                NIS
                            </label>

                            <input
                                type="text"
                                name="nis"
                                placeholder="Masukkan NIS"
                                required
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >

                        </div>


                        <!-- JENIS KELAMIN -->
                        <div>

                            <label class="block text-sm font-semibold mb-2 text-slate-700">
                                Jenis Kelamin
                            </label>

                            <select
                                name="jenis_kelamin"
                                required
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >

                                <option value="">Pilih jenis kelamin</option>

                                <option value="Laki-laki">
                                    Laki-laki
                                </option>

                                <option value="Perempuan">
                                    Perempuan
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- TOMBOL -->
                    <div class="mt-6 flex justify-end">

                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-md shadow-blue-100 hover:shadow-lg"
                        >

                            <i class="fa-solid fa-plus mr-2"></i>
                            Tambah Siswa

                        </button>

                    </div>

                </form>

            </div>


            <!-- TABEL SISWA -->
            <div class="content-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <!-- HEADER TABEL -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">

                    <div>

                        <div class="flex items-center gap-2">

                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">

                                <i class="fa-solid fa-table-list"></i>

                            </div>

                            <h3 class="font-bold text-lg text-slate-800">
                                Daftar Siswa
                            </h3>

                        </div>

                        <p class="text-sm text-slate-400 mt-2">
                            Seluruh siswa yang terdaftar.
                        </p>

                    </div>

                    <span class="bg-blue-50 text-blue-700 border border-blue-100 px-4 py-2 rounded-xl text-sm font-bold">

                        <i class="fa-solid fa-users mr-1"></i>

                        <?= $total ?> Siswa

                    </span>

                </div>


                <!-- ISI TABEL -->
                <div class="overflow-x-auto">

                    <table class="w-full text-sm text-left">

                        <thead class="bg-slate-50 text-slate-500">

                            <tr>

                                <th class="px-6 py-4 font-bold">
                                    No
                                </th>

                                <th class="px-6 py-4 font-bold">
                                    Nama Siswa
                                </th>

                                <th class="px-6 py-4 font-bold">
                                    NIS
                                </th>

                                <th class="px-6 py-4 font-bold">
                                    Jenis Kelamin
                                </th>

                                <th class="px-6 py-4 text-center font-bold">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100">

                            <?php if ($total > 0): ?>

                                <?php $no = 1; ?>

                                <?php while ($data = mysqli_fetch_assoc($querySiswa)): ?>

                                    <tr class="hover:bg-blue-50/50 transition duration-200">

                                        <td class="px-6 py-4 text-slate-400">
                                            <?= $no++ ?>
                                        </td>


                                        <td class="px-6 py-4">

                                            <div class="flex items-center gap-3">

                                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">

                                                    <?= strtoupper(substr($data['nama_siswa'], 0, 1)) ?>

                                                </div>

                                                <span class="font-semibold text-slate-700">
                                                    <?= htmlspecialchars($data['nama_siswa']) ?>
                                                </span>

                                            </div>

                                        </td>


                                        <td class="px-6 py-4 text-slate-500">

                                            <?= htmlspecialchars($data['nis']) ?>

                                        </td>


                                        <td class="px-6 py-4">

                                            <?php if ($data['jenis_kelamin'] == 'Laki-laki'): ?>

                                                <span class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-semibold">

                                                    <i class="fa-solid fa-mars"></i>
                                                    Laki-laki

                                                </span>

                                            <?php else: ?>

                                                <span class="inline-flex items-center gap-2 bg-pink-50 text-pink-700 px-3 py-1.5 rounded-lg text-xs font-semibold">

                                                    <i class="fa-solid fa-venus"></i>
                                                    Perempuan

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-6 py-4 text-center">

                                            <div class="flex justify-center items-center gap-2">

                                                <!-- EDIT -->
                                                <a
                                                    href="edit_siswa.php?id=<?= urlencode($data['id_siswa']) ?>"
                                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition"
                                                    title="Edit siswa"
                                                >

                                                    <i class="fa-solid fa-pen-to-square"></i>

                                                </a>


                                                <!-- HAPUS -->
                                                <a
                                                    href="hapus_siswa.php?id=<?= urlencode($data['id_siswa']) ?>"
                                                    onclick="return confirm('Yakin ingin menghapus siswa ini beserta jadwal dan riwayat piketnya?')"
                                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition"
                                                    title="Hapus siswa"
                                                >

                                                    <i class="fa-solid fa-trash"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="5" class="px-6 py-12 text-center">

                                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto">

                                            <i class="fa-solid fa-users text-3xl text-blue-300"></i>

                                        </div>

                                        <p class="font-semibold text-slate-600 mt-4">
                                            Belum ada data siswa
                                        </p>

                                        <p class="text-xs text-slate-400 mt-1">
                                            Silakan tambahkan siswa melalui form di atas.
                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

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
