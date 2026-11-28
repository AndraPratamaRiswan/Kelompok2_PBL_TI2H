-- =====================================================================
-- STORED PROCEDURE
-- Butuh function (fn_hitung_total, fn_cari_atau_buat_pembeli).
-- =====================================================================

-- sp_edit_menu
CREATE OR REPLACE PROCEDURE public.sp_edit_menu(IN p_id_menu integer, IN p_id_kategori integer[], IN p_nama_menu character varying, IN p_harga numeric, IN p_deskripsi character varying, IN p_gambar_menu character varying)
    LANGUAGE plpgsql
    AS $$
    begin
    update menu
    set
    nama_menu = p_nama_menu,
    deskripsi = p_deskripsi,
    harga_menu = p_harga,
    gambar_url = p_gambar_menu,
    updated_at = current_timestamp
    where id_menu = p_id_menu;
    raise notice 'data pesanan diedit';

    delete from kategori_menu km
    where km.id_menu = p_id_menu;

     insert into kategori_menu (
        id_menu,
        id_kategori
    )
    select
        p_id_menu,
        unnest(p_id_kategori);

    raise notice 'relasi kategori dan menu berhasil diedit kembali';
    end;
    $$;

-- sp_edit_pesanan
CREATE OR REPLACE PROCEDURE public.sp_edit_pesanan(IN p_id_pesanan integer, IN p_nama_pembeli character varying, IN p_id_menu integer[], IN p_jumlah integer[], IN p_alamat_pengiriman character varying, IN p_status_pesanan character varying, IN p_status_pembayaran character varying, IN p_tanggal_pengiriman date, IN p_jam_pengiriman time without time zone, IN p_catatan character varying, IN p_jumlah_dp numeric, IN p_biaya_ongkir numeric)
    LANGUAGE plpgsql
    AS $$
begin
    update pesanan
    set
    nama_pembeli = p_nama_pembeli,
    alamat_pengiriman = p_alamat_pengiriman,
    status_pesanan = p_status_pesanan,
    status_pembayaran = p_status_pembayaran,
    tanggal_pengiriman = p_tanggal_pengiriman,
    jam_pengiriman = p_jam_pengiriman,
    catatan = p_catatan,
    jumlah_dp = p_jumlah_dp,
    biaya_ongkir = p_biaya_ongkir,
	updated_at = current_timestamp
    where
    id_pesanan = p_id_pesanan;
    raise notice 'data pesanan masuk';

   delete from detail_pesanan dp where
   dp.id_pesanan  = p_id_pesanan;

    insert into detail_pesanan (
    id_pesanan,
    id_menu,
    jumlah,
    harga_satuan
    )
    select
    p_id_pesanan,
    m.id_menu,
    x.jumlah,
    m.harga_menu
    from unnest(p_id_menu, p_jumlah) as x(id_menu, jumlah)
    join menu m on m.id_menu = x.id_menu;
    raise notice 'data detail_pesanan masuk';

    update pesanan
    set total_harga = fn_hitung_total(p_id_pesanan)
    where id_pesanan = p_id_pesanan;
end;
    $$;

-- sp_hapus_pesanan
CREATE OR REPLACE PROCEDURE public.sp_hapus_pesanan(IN p_id_pesanan integer)
    LANGUAGE plpgsql
    AS $$
BEGIN
    DELETE FROM pesanan WHERE id_pesanan = p_id_pesanan;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Pesanan % tidak ditemukan', p_id_pesanan
            USING ERRCODE = 'P0002';
    END IF;
END;
$$;

-- sp_tambah_menu
CREATE OR REPLACE PROCEDURE public.sp_tambah_menu(IN p_id_admin integer, IN p_id_kategori integer[], IN p_nama_menu character varying, IN p_harga numeric, IN p_deskripsi character varying, IN p_gambar_menu character varying)
    LANGUAGE plpgsql
    AS $$
    declare
    v_id_menu integer;
    begin
    insert into menu (
        id_admin,
        nama_menu,
        deskripsi,
        harga_menu,
        gambar_url
    )
    values (
        p_id_admin,
        p_nama_menu,
        p_deskripsi,
        p_harga,
        p_gambar_menu
    )
    returning id_menu into v_id_menu;
    raise notice 'data menu masuk';
    insert into kategori_menu (
        id_menu,
        id_kategori
    )
    select
        v_id_menu,
        unnest(p_id_kategori);

    raise notice 'relasi kategori dan menu berhasil ditambahkan';
    end;
    $$;

-- sp_tambah_pesanan
CREATE OR REPLACE PROCEDURE public.sp_tambah_pesanan(IN p_id_admin integer, IN p_nama_pembeli character varying, IN p_id_pembeli integer, IN p_id_menu integer[], IN p_jumlah integer[], IN p_alamat_pengiriman character varying, IN p_status_pesanan character varying, IN p_status_pembayaran character varying, IN p_tanggal_pengiriman date, IN p_jam_pengiriman time without time zone, IN p_catatan character varying, IN p_jumlah_dp numeric, IN p_biaya_ongkir numeric)
    LANGUAGE plpgsql
    AS $$
declare v_id_pesanan integer;
begin
    insert into pesanan (
		id_admin,
        nama_pembeli,
        id_pembeli,
        alamat_pengiriman,
        status_pesanan,
        status_pembayaran,
        tanggal_pengiriman,
        jam_pengiriman,
        catatan,
        jumlah_dp,
        biaya_ongkir)
    values (
		p_id_admin,
        p_nama_pembeli,
        p_id_pembeli,
        p_alamat_pengiriman,
        p_status_pesanan,
        p_status_pembayaran,
        p_tanggal_pengiriman,
        p_jam_pengiriman,
        p_catatan,
        p_jumlah_dp,
        p_biaya_ongkir
    )
	returning id_pesanan into v_id_pesanan;
    raise notice 'data pesanan masuk';
    insert into detail_pesanan (
    id_pesanan,
    id_menu,
    jumlah,
    harga_satuan
    )
    select
    v_id_pesanan,
    m.id_menu,
    x.jumlah,
    m.harga_menu
    from unnest(p_id_menu, p_jumlah) as x(id_menu, jumlah)
    join menu m on m.id_menu = x.id_menu;
    raise notice 'data detail_pesanan masuk';

    update pesanan
    set total_harga = fn_hitung_total(v_id_pesanan)
    where id_pesanan = v_id_pesanan;
    end;
    $$;

-- sp_tambah_pesanan_admin
CREATE OR REPLACE PROCEDURE public.sp_tambah_pesanan_admin(IN p_id_admin integer, IN p_no_hp character varying, IN p_nama_pembeli character varying, IN p_id_menu integer[], IN p_jumlah integer[], IN p_alamat_pengiriman character varying, IN p_status_pesanan character varying, IN p_status_pembayaran character varying, IN p_tanggal_pengiriman date, IN p_jam_pengiriman time without time zone, IN p_catatan character varying, IN p_jumlah_dp numeric, IN p_biaya_ongkir numeric)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_id_pembeli integer;
BEGIN
    v_id_pembeli := fn_cari_atau_buat_pembeli(p_no_hp);

    CALL sp_tambah_pesanan(
        p_id_admin, p_nama_pembeli, v_id_pembeli,
        p_id_menu, p_jumlah,
        p_alamat_pengiriman, p_status_pesanan, p_status_pembayaran,
        p_tanggal_pengiriman, p_jam_pengiriman,
        p_catatan, p_jumlah_dp, p_biaya_ongkir
    );
END;
$$;
