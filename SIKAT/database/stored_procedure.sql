-- stored procedure buat nambah pesanan
-- note : belum menyelesaikan masalah kalo nomor hp belum terdaftar di database pembeli, kalo flow udah bener bakal ku tambahin lagi
create or replace procedure sp_tambah_pesanan(
	p_id_admin integer,
    p_nama_pembeli varchar(50),
    p_id_pembeli integer,
    p_id_menu integer[],
    p_jumlah integer[],
    p_alamat_pengiriman varchar(255),
    p_status_pesanan varchar(15),
    p_Status_pembayaran varchar(15),
    p_tanggal_pengiriman date,
    p_jam_pengiriman time,
    p_catatan varchar(255),
    p_jumlah_dp numeric(12,2),
    p_biaya_ongkir numeric(12,2)

)
language plpgsql
as $$
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

-- stored procedure buat edit pesanan
-- note : nggak ada updated_at pada detail_pesanan karena setiap pesanan diupdate, maka row di detail_pesanan dihapus dan dibuat lagi
create or replace procedure sp_edit_pesanan(
    p_id_pesanan integer,
    p_nama_pembeli varchar(50),
    p_id_menu integer[],
    p_jumlah integer[],
    p_alamat_pengiriman varchar(255),
    p_status_pesanan varchar(15),
    p_Status_pembayaran varchar(15),
    p_tanggal_pengiriman date,
    p_jam_pengiriman time,
    p_catatan varchar(255),
    p_jumlah_dp numeric(12,2),
    p_biaya_ongkir numeric(12,2)

)
language plpgsql
as $$
begin
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
exception
    when others then
    rollback;
    raise;
    commit;
    
    end;
    $$;

    
