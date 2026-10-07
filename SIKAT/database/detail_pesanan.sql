CREATE TABLE detail_pesanan (
    id_pesanan   INTEGER       NOT NULL,
    id_menu      INTEGER       NOT NULL,
    jumlah       INTEGER       NOT NULL,          -- jumlah box
    harga_satuan NUMERIC(12,2) NOT NULL,          -- salinan harga menu saat dipesan
    -- Dihitung otomatis oleh PostgreSQL. JANGAN diisi lewat INSERT/UPDATE.
    subtotal     NUMERIC(12,2) GENERATED ALWAYS AS (jumlah * harga_satuan) STORED,
    -- keterangan_tambahan VARCHAR(255),          -- (belum ada di PDM, lihat catatan)
    CONSTRAINT pk_detail_pesanan PRIMARY KEY (id_pesanan, id_menu),
    CONSTRAINT fk_detail_pesanan
        FOREIGN KEY (id_pesanan) REFERENCES pesanan (id_pesanan)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_menu
        FOREIGN KEY (id_menu) REFERENCES menu (id_menu)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_detail_jumlah CHECK (jumlah > 0),
    CONSTRAINT chk_detail_harga  CHECK (harga_satuan >= 0)
);