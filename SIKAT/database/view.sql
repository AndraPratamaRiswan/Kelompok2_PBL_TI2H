-- view buat nampilin data pesanan (gabungan dari pesanan, detail_pesanan, nohp sama nama menu), 
--backend mikir gimana caranya biar datanya gak double2
create view v_data_pesanan as
select
    p.id_pesanan, 
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
    from pesanan p
    join pembeli pb
    on p.id_pembeli = pb.id_pembeli
    join detail_pesanan dp
    on p.id_pesanan = dp.id_pesanan
    join menu m
    on dp.id_menu = m.id_menu;

--view buat cetak laporan pemesanan admin
create view v_cetak_laporan_admin as
select
    p.tanggal_dipesan,
    m.nama_menu,
    dp.jumlah,
    dp.harga_satuan,
    dp.subtotal,
    p.total_harga
from pesanan p
join detail_pesanan dp
on p.id_pesanan = dp.id_pesanan
join menu m
on dp.id_menu = m.id_menu;

-- buat query menu terlaris 
create view v_menu_terlaris as
select
    dp.id_menu,
    m.nama_menu,
    p.tanggal_dipesan,
    sum(dp.jumlah) as total_unit_dipesan
from detail_pesanan dp
join menu m
on dp.id_menu = m.id_menu
join pesanan p
on dp.id_pesanan = p.id_pesanan
group by dp.id_menu, m.nama_menu, p.tanggal_dipesan;

CREATE VIEW v_tampil_menu_catalog AS
SELECT
    m.id_menu,
    m.nama_menu,
    m.deskripsi,
    m.harga_menu,
    m.gambar_url,
    m.is_tersedia,
    COALESCE(STRING_AGG(DISTINCT k.nama_kategori, ', '), 'Belum ada kategori') AS daftar_kategori
FROM menu m
LEFT JOIN kategori_menu km ON km.id_menu = m.id_menu
LEFT JOIN kategori k ON k.id_kategori = km.id_kategori
GROUP BY m.id_menu;