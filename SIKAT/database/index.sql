-- =====================================================================
-- INDEX
-- =====================================================================

CREATE INDEX IF NOT EXISTS idx_log_pesanan_pesanan_waktu ON public.log_pesanan USING btree (id_pesanan, waktu DESC);

CREATE INDEX IF NOT EXISTS idx_menu_dokumen_cari ON public.menu USING gin (dokumen_cari);

CREATE INDEX IF NOT EXISTS idx_pesanan_pembeli_created ON public.pesanan USING btree (id_pembeli, created_at DESC, id_pesanan DESC);

CREATE INDEX IF NOT EXISTS idx_pesanan_selesai_tanggal ON public.pesanan USING btree (tanggal_dipesan) WHERE ((status_pesanan)::text = 'Selesai'::text);

CREATE INDEX IF NOT EXISTS idx_pesanan_tanggal_dipesan ON public.pesanan USING btree (tanggal_dipesan);
