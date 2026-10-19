<?php
// fitur 2, data dummy kartu statistik
$statistik = [
    ['label' => 'Total Pesanan', 'nilai' => '140', 'tema' => 'soft'],
    ['label' => 'Pesanan Diproses', 'nilai' => '12', 'tema' => 'dark'],
    ['label' => 'Pesanan Selesai', 'nilai' => '136', 'tema' => 'soft'],
    ['label' => 'Total Pendapatan', 'nilai' => 'Rp 1M', 'tema' => 'dark'],
];

// fitur 3, data dummy tren mingguan
$trenPesanan = [
    ['hari' => 'Sen', 'tinggi' => 58],
    ['hari' => 'Sel', 'tinggi' => 68],
    ['hari' => 'Rab', 'tinggi' => 46],
    ['hari' => 'Kam', 'tinggi' => 58],
    ['hari' => 'Jum', 'tinggi' => 29],
    ['hari' => 'Sab', 'tinggi' => 72],
    ['hari' => 'Ming', 'tinggi' => 44],
];

// fitur 3, data dummy menu terlaris
$menuTerlaris = [
    ['nama' => 'Ayam Goreng', 'persentase' => 86],
    ['nama' => 'Nasi Kuning', 'persentase' => 76],
    ['nama' => 'Ayam Geprek', 'persentase' => 63],
    ['nama' => 'Nasi Rendang', 'persentase' => 48],
    ['nama' => 'Nasi Tumpeng', 'persentase' => 34],
];

// fitur 4, data dummy daftar pesanan
$daftarPesanan = [
    [
        'id' => 'SKKT-001', 'nomor' => '0812-6786-0980', 'nama' => 'Bu Narsis',
        'alamat' => 'Malang', 'menu' => 'Nasi Ayam Bakar Sedap', 'qty' => 50,
        'harga_satuan' => 25000, 'tanggal' => '2026-09-01', 'jam' => '10:40',
        'status' => 'Selesai', 'pembayaran' => 'Lunas', 'ongkir' => 15000,
        'dp' => 0, 'catatan' => 'Sambal dipisah, minta diantar tepat pukul 10:40 WIB'
    ],
    [
        'id' => 'SKKT-002', 'nomor' => '0812-6786-0981', 'nama' => 'Bu Sulami',
        'alamat' => 'Lowokwaru, Malang', 'menu' => 'Nasi Ayam Geprek Jos', 'qty' => 50,
        'harga_satuan' => 25000, 'tanggal' => '2026-09-01', 'jam' => '11:00',
        'status' => 'Selesai', 'pembayaran' => 'Lunas', 'ongkir' => 15000,
        'dp' => 0, 'catatan' => 'Sambal level sedang'
    ],
    [
        'id' => 'SKKT-003', 'nomor' => '0812-6786-0982', 'nama' => 'Pak Yanto',
        'alamat' => 'Blimbing, Malang', 'menu' => 'Nasi Rendang Nyam', 'qty' => 50,
        'harga_satuan' => 25000, 'tanggal' => '2026-09-02', 'jam' => '11:30',
        'status' => 'Selesai', 'pembayaran' => 'Lunas', 'ongkir' => 20000,
        'dp' => 0, 'catatan' => 'Mohon pesanan diberi label'
    ],
    [
        'id' => 'SKKT-004', 'nomor' => '0812-6786-0983', 'nama' => 'Bu Ratna',
        'alamat' => 'Klojen, Malang', 'menu' => 'Nasi Kuning', 'qty' => 50,
        'harga_satuan' => 25000, 'tanggal' => '2026-09-02', 'jam' => '12:00',
        'status' => 'Selesai', 'pembayaran' => 'Lunas', 'ongkir' => 15000,
        'dp' => 0, 'catatan' => ''
    ],
    [
        'id' => 'SKKT-005', 'nomor' => '0812-6786-0984', 'nama' => 'Ibu Siti',
        'alamat' => 'Dinoyo, Malang', 'menu' => 'Nasi Ayam Bakar Sedap', 'qty' => 50,
        'harga_satuan' => 25000, 'tanggal' => '2026-10-12', 'jam' => '10:00',
        'status' => 'Dikonfirmasi', 'pembayaran' => 'Lunas', 'ongkir' => 15000,
        'dp' => 0, 'catatan' => 'Pesanan untuk acara keluarga'
    ],
    [
        'id' => 'SKKT-006', 'nomor' => '0812-6786-0985', 'nama' => 'Ibu Sulami',
        'alamat' => 'Sukun, Malang', 'menu' => 'Nasi Kuning', 'qty' => 30,
        'harga_satuan' => 22000, 'tanggal' => '2026-10-12', 'jam' => '13:00',
        'status' => 'Diproses', 'pembayaran' => 'DP', 'ongkir' => 15000,
        'dp' => 200000, 'catatan' => 'Jangan terlalu pedas'
    ],
    [
        'id' => 'SKKT-007', 'nomor' => '0812-6786-0986', 'nama' => 'Pak Budi',
        'alamat' => 'Sawojajar, Malang', 'menu' => 'Snack Box Premium', 'qty' => 25,
        'harga_satuan' => 22000, 'tanggal' => '2026-10-15', 'jam' => '09:30',
        'status' => 'Dikirim', 'pembayaran' => 'DP', 'ongkir' => 10000,
        'dp' => 150000, 'catatan' => 'Hubungi sebelum sampai'
    ],
    [
        'id' => 'SKKT-008', 'nomor' => '0812-6786-0987', 'nama' => 'Bu Dewi',
        'alamat' => 'Tlogomas, Malang', 'menu' => 'Nasi Ayam Geprek Jos', 'qty' => 40,
        'harga_satuan' => 25000, 'tanggal' => '2026-10-18', 'jam' => '11:00',
        'status' => 'Selesai', 'pembayaran' => 'Lunas', 'ongkir' => 15000,
        'dp' => 0, 'catatan' => ''
    ],
    [
        'id' => 'SKKT-009', 'nomor' => '0812-6786-0988', 'nama' => 'Pak Arif',
        'alamat' => 'Kedungkandang, Malang', 'menu' => 'Tumpeng Mini', 'qty' => 5,
        'harga_satuan' => 150000, 'tanggal' => '2026-08-21', 'jam' => '08:00',
        'status' => 'Selesai', 'pembayaran' => 'Lunas', 'ongkir' => 25000,
        'dp' => 0, 'catatan' => 'Acara kantor'
    ],
    [
        'id' => 'SKKT-010', 'nomor' => '0812-6786-0989', 'nama' => 'Bu Maya',
        'alamat' => 'Batu', 'menu' => 'Snack Box Premium', 'qty' => 20,
        'harga_satuan' => 22000, 'tanggal' => '2026-08-25', 'jam' => '14:00',
        'status' => 'Selesai', 'pembayaran' => 'Lunas', 'ongkir' => 20000,
        'dp' => 0, 'catatan' => ''
    ],
];

// fitur 1, konfigurasi header/navigasi
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<!-- fitur 1, judul dashboard -->
<section class="page-heading">
    <div>
        <p class="eyebrow">RINGKASAN TOKO</p>
        <h1>Dashboard</h1>
        <p class="page-description">Pantau pesanan dan aktivitas SIKAT hari ini.</p>
    </div>
    <!-- fitur 5, tombol cetak laporan (demo) -->
    <button class="button button-report" id="printReport" type="button">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v7H7z"/><path d="M17 11h.01"/></svg>
        Cetak Laporan
    </button>
</section>

<!-- fitur 2, kartu statistik -->
<section class="stats-grid" aria-label="Ringkasan statistik">
    <?php foreach ($statistik as $stat): ?>
        <article class="stat-card stat-card--<?= htmlspecialchars($stat['tema']) ?>">
            <p><?= htmlspecialchars($stat['label']) ?></p>
            <strong><?= htmlspecialchars($stat['nilai']) ?></strong>
        </article>
    <?php endforeach; ?>
</section>

<!-- fitur 3, analisis dashboard -->
<section class="analytics-grid" aria-label="Analisis pesanan dan menu">
    <!-- fitur 3, grafik tren pesanan -->
    <article class="panel trend-panel">
        <div class="panel-heading">
            <div>
                <h2>Tren Pesanan Mingguan</h2>
                <p class="panel-subtitle">Jumlah pesanan selama satu minggu</p>
            </div>
            <span class="mini-tag">Minggu ini</span>
        </div>

        <div class="bar-chart" role="img" aria-label="Grafik batang tren pesanan selama Senin sampai Minggu">
            <?php foreach ($trenPesanan as $data): ?>
                <div class="bar-chart__item">
                    <div class="bar-chart__track">
                        <span class="bar-chart__bar" style="height: <?= (int) $data['tinggi'] ?>%"></span>
                    </div>
                    <span class="bar-chart__label"><?= htmlspecialchars($data['hari']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- fitur 3, daftar menu terlaris -->
    <article class="panel popular-panel">
        <div class="panel-heading">
            <div>
                <h2>Menu Terlaris</h2>
                <p class="panel-subtitle">Menu yang paling sering dipesan</p>
            </div>
        </div>
        <div class="popular-list">
            <?php foreach ($menuTerlaris as $menu): ?>
                <div class="popular-item">
                    <span class="popular-item__name"><?= htmlspecialchars($menu['nama']) ?></span>
                    <span class="popular-item__track" aria-label="Indeks popularitas <?= (int) $menu['persentase'] ?> persen">
                        <span style="width: <?= (int) $menu['persentase'] ?>%"></span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<!-- fitur 4, daftar pesanan prototype -->
<section class="orders-panel" aria-labelledby="ordersTitle">
    <div class="orders-toolbar">
        <div class="orders-title-group">
            <div>
                <h2 id="ordersTitle">Daftar Pesanan</h2>
                <p class="panel-subtitle">Contoh tampilan pesanan terbaru</p>
            </div>
            
            <!-- fitur 4, filter bulan (demo) -->
            <label class="month-filter">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                <select id="monthFilter" aria-label="Pilih bulan pesanan">
                    <option value="2026-10" selected>Oktober 2026</option>
                    <option value="2026-09">September 2026</option>
                    <option value="2026-08">Agustus 2026</option>
                </select>
            </label>
        </div>
        
        <!-- fitur 4, tombol tambah dan lihat semua (demo) -->
        <div class="orders-actions">
            <button class="button button-primary button-small" id="addOrderButton" type="button">+ Add</button>
            <button class="text-button" id="viewAllButton" type="button" aria-label="Lihat semua pesanan belum aktif">Lihat Semua <span aria-hidden="true">→</span></button>
        </div>
    </div>

    <!-- fitur 4, baris dummy pesanan -->
    <div class="order-list" id="orderList" aria-live="polite"></div>
    <p class="empty-orders" id="emptyOrders" hidden>Tidak ada pesanan pada bulan yang dipilih.</p>
    <p class="prototype-note"><span class="prototype-dot"></span> Data dummy, belum terhubung ke database PostgreSQL.</p>
</section>

<!-- fitur 4, overlay form tambah dan edit pesanan -->
<div class="modal-overlay" id="orderModal" hidden>
    <section class="modal-panel modal-panel--form" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
        <header class="modal-header">
            <div>
                <p class="modal-eyebrow">PENGELOLAAN PESANAN</p>
                <h2 id="orderModalTitle">Tambah Pesanan</h2>
            </div>
            <button class="modal-close" type="button" data-close-order aria-label="Tutup formulir">×</button>
        </header>

        <form id="orderForm">
            <input type="hidden" id="orderId">
            <div class="form-grid">
                <!-- fitur 4, nomor WhatsApp dan nama. -->
                <label class="form-field">
                    <span>Nomor / WhatsApp <b>*</b></span>
                    <input id="orderPhone" name="nomor" type="tel" placeholder="Contoh: 081234567890" required>
                </label>
                <label class="form-field">
                    <span>Nama Pemesan <b>*</b></span>
                    <input id="orderName" name="nama" type="text" placeholder="Nama pelanggan" required>
                </label>

                <!-- fitur 4, dropdown menu dan counter qty.
                     Harga satuan disimpan pada data price untuk dihitung oleh JawaScript. -->
                <label class="form-field">
                    <span>Menu yang dipesan <b>*</b></span>
                    <select id="orderMenu" name="menu" required>
                        <option value="">Pilih Menu</option>
                        <option value="Nasi Ayam Bakar Sedap" data-price="25000">Nasi Ayam Bakar Sedap — Rp25.000</option>
                        <option value="Nasi Ayam Geprek Jos" data-price="25000">Nasi Ayam Geprek Jos — Rp25.000</option>
                        <option value="Nasi Rendang Nyam" data-price="25000">Nasi Rendang Nyam — Rp25.000</option>
                        <option value="Nasi Kuning" data-price="22000">Nasi Kuning — Rp22.000</option>
                        <option value="Snack Box Premium" data-price="22000">Snack Box Premium — Rp22.000</option>
                        <option value="Tumpeng Mini" data-price="150000">Tumpeng Mini — Rp150.000</option>
                    </select>
                    <small class="field-help" id="unitPriceHint">Harga satuan akan mengikuti menu pilihan.</small>
                </label>
                <div class="form-field">
                    <span>Jumlah pesanan <b>*</b></span>
                    <div class="quantity-control">
                        <button type="button" id="quantityMinus" aria-label="Kurangi jumlah">−</button>
                        <output id="quantityValue" for="quantityMinus quantityPlus">0</output>
                        <button type="button" id="quantityPlus" aria-label="Tambah jumlah">+</button>
                    </div>
                </div>

                <!-- fitur 4, status pesanan dan status bayar -->
                <label class="form-field">
                    <span>Status Pesanan <b>*</b></span>
                    <select id="orderStatus" required>
                        <option value="Dikonfirmasi">Dikonfirmasi</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Dikirim">Dikirim</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </label>
                <label class="form-field">
                    <span>Status Pembayaran <b>*</b></span>
                    <select id="paymentStatus" required>
                        <option value="DP">DP</option>
                        <option value="Lunas">Lunas</option>
                    </select>
                </label>

                <!-- fitur 4, tanggal dan jam ngirim -->
                <label class="form-field">
                    <span>Tanggal <b>*</b></span>
                    <input id="orderDate" type="date" required>
                </label>
                <label class="form-field">
                    <span>Jam Kirim <b>*</b></span>
                    <input id="orderTime" type="time" required>
                </label>

                <!-- fitur 4, ongkir dan dp).
                     kotak field DP ditampilkan dan diwajibkan hanya jika status pembayaran DP. -->
                <label class="form-field">
                    <span>Ongkir (Rp)</span>
                    <input id="shippingCost" type="number" min="0" step="1000" placeholder="15000" value="0">
                </label>
                <label class="form-field" id="dpAmountField">
                    <span>Jumlah DP (Rp) <b>*</b></span>
                    <input id="dpAmount" type="number" min="0" step="1000" placeholder="Contoh: 100000" value="0">
                </label>

                <!-- fitur 4, alamat dan note psanan -->
                <label class="form-field form-field--full">
                    <span>Alamat Pengiriman</span>
                    <input id="orderAddress" type="text" placeholder="Alamat pengiriman pelanggan">
                </label>
                <label class="form-field form-field--full">
                    <span>Catatan Pesanan</span>
                    <textarea id="orderNotes" rows="2" placeholder="Catatan khusus untuk pesanan"></textarea>
                </label>
            </div>
            <!-- fitur 4, preview nilai dan tombol simpan
                 tombol simpan masih menyimpan ke array JavaScript sementara (demo). -->
            <div class="form-total" id="orderTotalPreview">Estimasi subtotal menu: Rp0</div>
            <div class="modal-footer modal-footer--center">
                <button class="button button-primary modal-save" type="submit" id="saveOrderButton">Simpan (Demo)</button>
                <button class="button button-quiet" type="button" data-close-order>Batal</button>
            </div>
        </form>
    </section>
</div>

<!-- fitur 4, overlay konfirmasi batal form
     Kembali menutup konfirmasi saja; Ya membuang isian dan menutup form. -->
<div class="modal-overlay modal-overlay--confirm" id="confirmCloseModal" hidden>
    <section class="confirm-panel" role="alertdialog" aria-modal="true" aria-labelledby="confirmCloseTitle" aria-describedby="confirmCloseMessage">
        <div class="confirm-icon" aria-hidden="true">i</div>
        <h2 id="confirmCloseTitle">Batalkan pengisian?</h2>
        <p id="confirmCloseMessage">Form Anda tidak tersimpan. Apakah Anda yakin ingin membatalkan pengisian?</p>
        <div class="confirm-actions">
            <button class="button button-outline" id="returnToFormButton" type="button">Kembali</button>
            <button class="button button-primary" id="discardFormButton" type="button">Ya</button>
        </div>
    </section>
</div>

     <!-- fitur 4, overlay detail ppesanan (READ ONLY)
     cuman menampilkan data pada pesanan tersebut, tidak bisa edit -->

<div class="modal-overlay" id="detailModal" hidden>
    <section class="modal-panel modal-panel--detail" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
        <header class="modal-header">
            <div>
                <p class="modal-eyebrow">PRATINJAU PESANAN</p>
                <h2 id="detailModalTitle">Detail Pesanan</h2>
                <p class="modal-description" id="detailCreatedAt">Data contoh pesanan</p>
            </div>
            <button class="modal-close" type="button" data-close-detail aria-label="Tutup detail">×</button>
        </header>
        <!--fitur 4, status pesanan dan pembayaran -->
        <div class="detail-status-row">
            <span class="detail-status" id="detailOrderStatus">Status pesanan</span>
            <span class="detail-status detail-status--payment" id="detailPaymentStatus">Status pembayaran</span>
        </div>
        <div class="detail-grid">
            <!-- fitur 4, ringkasan pemesan dan jadwal kirim -->
            <section class="detail-card">
                <h3>Informasi Pemesan & Pengiriman</h3>
                <dl>
                    <div><dt>Nama</dt><dd id="detailName">-</dd></div>
                    <div><dt>Nomor</dt><dd id="detailPhone">-</dd></div>
                    <div><dt>Alamat</dt><dd id="detailAddress">-</dd></div>
                    <div><dt>Tanggal Kirim</dt><dd id="detailDate">-</dd></div>
                    <div><dt>Jam Kirim</dt><dd id="detailTime">-</dd></div>
                </dl>
            </section>
            <!-- fitur 4, rincian menu, ongkir, dp, dan total -->
            <section class="detail-card">
                <h3>Ringkasan Pembayaran</h3>
                <dl>
                    <div><dt>Menu</dt><dd id="detailMenu">-</dd></div>
                    <div><dt>Jumlah</dt><dd id="detailQty">-</dd></div>
                    <div><dt>Harga Satuan</dt><dd id="detailUnitPrice">-</dd></div>
                    <div><dt>Subtotal Menu</dt><dd id="detailSubtotal">-</dd></div>
                    <div><dt>Ongkir</dt><dd id="detailShipping">-</dd></div>
                    <div><dt>Jumlah DP</dt><dd id="detailDp">-</dd></div>
                    <div class="detail-total"><dt>Total</dt><dd id="detailTotal">-</dd></div>
                </dl>
            </section>
            <!-- fitur 4, catatan pesanan -->
            <section class="detail-card detail-card--notes">
                <h3>Catatan Pesanan</h3>
                <p id="detailNotes">Tidak ada catatan.</p>
            </section>
        </div>
        <div class="modal-footer modal-footer--center">
            <button class="button button-primary" type="button" data-close-detail>Tutup</button>
        </div>
    </section>
</div>

<!-- fitur 5, overlay cetak laporan -->
<div class="modal-overlay" id="reportModal" hidden>
    <section class="modal-panel modal-panel--report" role="dialog" aria-modal="true" aria-labelledby="reportTitle">
        <header class="report-heading">
            <div>
                <p class="modal-eyebrow">LAPORAN PENJUALAN</p>
                <h2 id="reportTitle">Cetak Laporan Keuangan</h2>
                <p class="report-store-name">SIKAT — Catering Nasi Kotak Malang by Tya</p>
                <p class="report-store-info">Jl. Raya Malang No. 123 <span aria-hidden="true">|</span> WA: 0812-3768-xxxx</p>
            </div>
            <button class="modal-close" type="button" data-close-report aria-label="Tutup laporan">×</button>
        </header>
        <div class="report-content" id="reportPrintableArea">
            <!-- fitur 5, filter periode bulan dan keterangan status
                 Rekap hanya menghitung pesanan berstatus Selesai. -->
            <div class="report-period-row">
                <label for="reportMonthFilter">Periode laporan</label>
                <select id="reportMonthFilter">
                    <option value="2026-09" selected>September 2026</option>
                    <option value="2026-10">Oktober 2026</option>
                    <option value="2026-08">Agustus 2026</option>
                </select>
                <span class="report-status-note">Hanya pesanan berstatus Selesai</span>
            </div>
            <!-- fitur 5, 3 card ringkasan (kek di figma) pada cetak laporan -->
            <div class="report-summary-grid">
                <article class="report-summary-card"><span>Total Quantity</span><strong id="reportTotalQty">0</strong><small>box / porsi / unit</small></article>
                <article class="report-summary-card"><span>Jenis Menu</span><strong id="reportMenuKinds">0</strong><small>menu berbeda</small></article>
                <article class="report-summary-card report-summary-card--dark"><span>Total Pendapatan</span><strong id="reportTotalIncome">Rp0</strong><small>dari pesanan selesai</small></article>
            </div>
            <!-- fitur 5, tabel detail transaksi dan baris total -->
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>Tanggal</th><th>Menu</th><th>Qty</th><th>Harga Satuan</th><th>Total</th></tr></thead>
                    <tbody id="reportTableBody"></tbody>
                    <tfoot id="reportTableFoot"></tfoot>
                </table>
                <p class="report-empty" id="reportEmpty" hidden>Tidak ada pesanan selesai pada periode ini.</p>
            </div>
            <p class="report-footnote">Laporan ini dibuat dari data dummy untuk keperluan demonstrasi SIKAT.</p>
        </div>
        <!-- fitur 5, tombol tutup dan tombol unduh laporan
             Unduh PDF dibuat oleh JavaScript dari periode aktif -->
        <div class="modal-footer report-actions">
            <button class="button button-quiet" type="button" data-close-report>Batal</button>
            <button class="button button-primary" type="button" id="downloadReportPdf">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M5 17v4h14v-4"/></svg>
                Unduh PDF
            </button>
        </div>
    </section>
</div>

<!-- fitur 7, notifikasi toast -->
<div class="toast-message" id="toastMessage" role="status" aria-live="polite"></div>

<!-- fitur 4, menyerahkan array pesanan php ke js
     Data ini hanya salinan awal, perubahan berikutnya berlangsung di memori browser -->
<script>
    window.sikatOrders = <?= json_encode($daftarPesanan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>