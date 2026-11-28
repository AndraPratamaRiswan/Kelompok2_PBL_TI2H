-- =====================================================================
-- PESANAN
-- Butuh tabel admin dan pembeli.
-- =====================================================================

CREATE TABLE public.pesanan (
    id_pesanan         serial        PRIMARY KEY,
    id_admin           integer       NOT NULL,
    id_pembeli         integer       NOT NULL,
    tanggal_dipesan    date          NOT NULL DEFAULT now(),
    tanggal_pengiriman date          NOT NULL,
    jam_pengiriman     time          NOT NULL,
    alamat_pengiriman  varchar(255)  NOT NULL,
    nama_pembeli       varchar(50)   NOT NULL,
    catatan            varchar(255),
    status_pesanan     varchar(20)   NOT NULL DEFAULT 'Belum Dikonfirmasi',
    status_pembayaran  varchar(15)   NOT NULL DEFAULT 'Belum Bayar',
    total_harga        numeric(12,2) NOT NULL DEFAULT 0,
    biaya_ongkir       numeric(12,2) NOT NULL DEFAULT 0,
    created_at         timestamp     NOT NULL DEFAULT now(),
    updated_at         timestamp     NOT NULL DEFAULT now(),
    jumlah_dp          numeric(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT chk_pesanan_dp         CHECK (jumlah_dp >= 0),
    CONSTRAINT chk_pesanan_pembayaran CHECK (status_pembayaran IN ('Belum Bayar', 'DP', 'Lunas')),
    CONSTRAINT chk_pesanan_status     CHECK (status_pesanan IN
        ('Belum Dikonfirmasi', 'Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai')),
    CONSTRAINT chk_pesanan_tanggal    CHECK (tanggal_pengiriman >= tanggal_dipesan),
    CONSTRAINT fk_pesanan_admin   FOREIGN KEY (id_admin)
        REFERENCES public.admin (id_admin)     ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pesanan_pembeli FOREIGN KEY (id_pembeli)
        REFERENCES public.pembeli (id_pembeli) ON UPDATE CASCADE ON DELETE RESTRICT
);
