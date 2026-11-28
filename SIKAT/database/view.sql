-- =====================================================================
-- VIEW
-- Butuh tabel menu, kategori, kategori_menu, pesanan, pembeli, detail_pesanan.
-- Dijalankan SEBELUM function, karena fn_cetak_laporan dan fn_menu_terlaris
-- mengembalikan tipe dari view.
-- =====================================================================

-- v_cetak_laporan_admin
CREATE OR REPLACE VIEW public.v_cetak_laporan_admin AS
 SELECT p.tanggal_dipesan,
    m.nama_menu,
    dp.jumlah,
    dp.harga_satuan,
    dp.subtotal,
    p.total_harga
   FROM ((public.pesanan p
     JOIN public.detail_pesanan dp ON ((p.id_pesanan = dp.id_pesanan)))
     JOIN public.menu m ON ((dp.id_menu = m.id_menu)));

-- v_menu_terlaris
CREATE OR REPLACE VIEW public.v_menu_terlaris AS
 SELECT dp.id_menu,
    m.nama_menu,
    sum(dp.jumlah) AS total_unit_dipesan
   FROM ((public.detail_pesanan dp
     JOIN public.menu m ON ((dp.id_menu = m.id_menu)))
     JOIN public.pesanan p ON ((dp.id_pesanan = p.id_pesanan)))
  GROUP BY dp.id_menu, m.nama_menu;

-- v_data_pesanan
CREATE OR REPLACE VIEW public.v_data_pesanan AS
 SELECT p.id_pesanan,
    p.nama_pembeli,
    pb.no_hp,
    m.nama_menu,
    dp.harga_satuan,
    dp.subtotal,
    p.tanggal_dipesan,
    p.tanggal_pengiriman,
    p.alamat_pengiriman,
    p.status_pesanan,
    p.status_pembayaran,
    p.total_harga,
    p.biaya_ongkir,
    p.updated_at,
    p.jumlah_dp
   FROM (((public.pesanan p
     JOIN public.pembeli pb ON ((p.id_pembeli = pb.id_pembeli)))
     JOIN public.detail_pesanan dp ON ((p.id_pesanan = dp.id_pesanan)))
     JOIN public.menu m ON ((dp.id_menu = m.id_menu)));

-- v_tampil_menu_catalog
CREATE OR REPLACE VIEW public.v_tampil_menu_catalog AS
 SELECT m.id_menu,
    m.nama_menu,
    m.deskripsi,
    m.harga_menu,
    m.gambar_url,
    m.is_tersedia,
    COALESCE(string_agg(DISTINCT (k.nama_kategori)::text, ', '::text), 'Belum ada kategori'::text) AS daftar_kategori
   FROM ((public.menu m
     LEFT JOIN public.kategori_menu km ON ((km.id_menu = m.id_menu)))
     LEFT JOIN public.kategori k ON ((k.id_kategori = km.id_kategori)))
  GROUP BY m.id_menu;
