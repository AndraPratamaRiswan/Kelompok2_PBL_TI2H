CREATE TABLE pesanan (
    id_pesanan         SERIAL        PRIMARY KEY,
    id_admin           INTEGER       NOT NULL,    -- admin yang memproses/mencatat
    id_pembeli         INTEGER       NOT NULL,
    tanggal_dipesan    TIMESTAMP     NOT NULL DEFAULT NOW(),
    tanggal_pengiriman DATE          NOT NULL,
    jam_pengiriman     TIME          NOT NULL,
    alamat_pengiriman  VARCHAR(255)  NOT NULL,
    catatan            VARCHAR(255),
    status_pesanan     VARCHAR(15)   NOT NULL DEFAULT 'Dikonfirmasi',
    status_pembayaran  VARCHAR(15)   NOT NULL DEFAULT 'Belum Bayar',
    updated_at         TIMESTAMP     NOT NULL DEFAULT NOW(),
    jumlah_dp          NUMERIC(12,2) NOT NULL DEFAULT 0,  -- diisi manual oleh admin
    CONSTRAINT fk_pesanan_admin
        FOREIGN KEY (id_admin) REFERENCES admin (id_admin)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pesanan_pembeli
        FOREIGN KEY (id_pembeli) REFERENCES pembeli (id_pembeli)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_pesanan_status
        CHECK (status_pesanan IN ('Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai')),
    CONSTRAINT chk_pesanan_pembayaran
        CHECK (status_pembayaran IN ('Belum Bayar', 'DP', 'Lunas')),
    CONSTRAINT chk_pesanan_dp CHECK (jumlah_dp >= 0),
    CONSTRAINT chk_pesanan_tanggal
        CHECK (tanggal_pengiriman >= tanggal_dipesan::date)
);