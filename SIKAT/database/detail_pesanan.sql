-- =====================================================================
-- DETAIL_PESANAN
-- Butuh tabel pesanan dan menu.
-- =====================================================================

CREATE TABLE public.detail_pesanan (
    id_pesanan   integer       NOT NULL,
    id_menu      integer       NOT NULL,
    jumlah       integer       NOT NULL,
    harga_satuan numeric(12,2) NOT NULL,
    subtotal     numeric(12,2) GENERATED ALWAYS AS (jumlah::numeric * harga_satuan) STORED,
    CONSTRAINT pk_detail_pesanan PRIMARY KEY (id_pesanan, id_menu),
    CONSTRAINT chk_detail_harga  CHECK (harga_satuan >= 0),
    CONSTRAINT chk_detail_jumlah CHECK (jumlah > 0),
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (id_pesanan)
        REFERENCES public.pesanan (id_pesanan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_menu    FOREIGN KEY (id_menu)
        REFERENCES public.menu (id_menu)       ON UPDATE CASCADE ON DELETE RESTRICT
);
