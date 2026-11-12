<?php
// Partial form pesanan, dipakai oleh pesanan_tambah.php dan pesanan_ubah.php.
// Variabel yang harus sudah ada: $mode ('tambah' | 'ubah'), $formAction, $data,
// $daftarMenu, $tanggalMin, $labelSimpan, $urlBatal, $pesanForm (boleh kosong).
$barisMenu = !empty($data['baris']) ? $data['baris'] : [['id' => '', 'jumlah' => 1]];
?>
<?php if ($pesanForm !== ''): ?>
    <div class="form-message form-message--error"><?= psnE($pesanForm) ?></div>
<?php endif; ?>

<section class="order-form-panel">
    <form method="post" action="<?= psnE($formAction) ?>" class="order-form">
        <div class="order-form-grid">
            <label>
                Nomor HP
                <?php if ($mode === 'ubah'): ?>
                    <input type="tel" name="nomor" value="<?= psnE($data['nomor']) ?>" readonly>
                    <small>Nomor HP terkait akun pembeli, jadi tidak bisa diubah.</small>
                <?php else: ?>
                    <input type="tel" name="nomor" placeholder="Contoh: 081234567890" value="<?= psnE($data['nomor']) ?>" required>
                    <small>Kalau nomor belum terdaftar, akun pembeli dibuat otomatis.</small>
                <?php endif; ?>
            </label>
            <label>
                Nama Pembeli
                <input type="text" name="nama" placeholder="Nama pembeli" maxlength="50" value="<?= psnE($data['nama']) ?>" required>
            </label>
        </div>

        <label class="order-note-field">
            Alamat Pengiriman
            <textarea name="alamat" rows="2" maxlength="255" placeholder="Alamat lengkap pengiriman" required><?= psnE($data['alamat']) ?></textarea>
        </label>

        <!-- satu pembeli bisa banyak menu -->
        <div class="order-items-section">
            <div class="order-section-heading">
                <div>
                    <h2>Menu yang dipesan</h2>
                    <p>Pilih menu dan jumlah box untuk setiap baris.</p>
                </div>
                <button class="button button-add-item" type="button" id="tambah-baris-menu">+ Tambah Menu</button>
            </div>
            <div class="order-item-list" id="daftar-baris-menu">
                <?php foreach ($barisMenu as $baris): ?>
                    <?= psnBarisMenu($daftarMenu, $baris['id'], $baris['jumlah']) ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="order-form-grid">
            <label>
                Status Pesanan
                <select name="status_pesanan" required>
                    <option value="">Pilih status pesanan</option>
                    <?php foreach (psnStatusPesananForm() as $status): ?>
                        <option value="<?= psnE($status) ?>" <?= $data['status_pesanan'] === $status ? 'selected' : '' ?>><?= psnE($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Status Pembayaran
                <select name="status_pembayaran" id="status-pembayaran" required>
                    <option value="">Pilih status pembayaran</option>
                    <?php foreach (psnStatusBayarForm() as $status): ?>
                        <option value="<?= psnE($status) ?>" <?= $data['status_pembayaran'] === $status ? 'selected' : '' ?>><?= psnE($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Tanggal Kirim
                <input type="date" name="tanggal" min="<?= psnE($tanggalMin) ?>" value="<?= psnE($data['tanggal']) ?>" required>
            </label>
            <label>
                Jam Kirim
                <input type="time" name="jam" value="<?= psnE($data['jam']) ?>" required>
                <small>Pilih jam dari kontrol browser.</small>
            </label>
            <label>
                Ongkir (Rp) <span class="optional-label">Opsional</span>
                <input type="number" name="ongkir" min="0" step="1000" value="<?= psnE($data['ongkir'] !== '' ? $data['ongkir'] : '0') ?>">
            </label>
            <!-- dp muncul kalau status bayarnya dp (diatur pesanan.js) -->
            <label id="field-jumlah-dp" hidden>
                Jumlah DP (Rp)
                <input type="number" name="jumlah_dp" id="jumlah-dp" min="1" step="1" placeholder="Masukkan jumlah DP" value="<?= psnE($data['jumlah_dp']) ?>">
            </label>
        </div>

        <label class="order-note-field">
            Catatan Pesanan <span class="optional-label">Opsional</span>
            <textarea name="catatan" rows="3" maxlength="255" placeholder="Contoh: sambal dipisah"><?= psnE($data['catatan']) ?></textarea>
        </label>

        <div class="order-form-actions">
            <button class="button button-primary" type="submit"><?= psnE($labelSimpan) ?></button>
            <a class="button button-neutral" href="<?= psnE($urlBatal) ?>" onclick="return confirm('Yakin ingin membatalkan? Data yang belum disimpan akan hilang.');">Batal</a>
        </div>
    </form>
</section>

<!-- template baris menu berikutnya, dipakai pesanan.js -->
<template id="template-baris-menu">
    <?= psnBarisMenu($daftarMenu) ?>
</template>

<script src="assets/js/pesanan.js"></script>
