--fungsi hitung_subtotal buat ngehitung subtotal pesanan * jumlah per row di detail_pesanan
create or replace function fn_hitung_subtotal(
    p_harga_satuan numeric (12,2), 
    p_jumlah integer)
    returns numeric (12,2)
    language plpgsql
    as $$
    begin
        return p_harga_Satuan * p_jumlah;
    end;
    $$;

-- fungsi hitung_total_pesanan gawe ngitung total harga 1 pesanan, kabeh subtotal 1 tabel, ambek biaya tambahan
create or replace function fn_hitung_total(
    p_id_pesanan int
)
    returns numeric (12,2)
    language plpgsql
    as $$
    declare 
        v_total numeric := 0;
        r record;
    begin 
        for r in
        select dp.subtotal
        from detail_pesanan dp
        where dp.id_pesanan = p_id_pesanan
        loop
        raise notice 'sedang looping';
        v_total := v_total + r.subtotal;
        end loop;
	raise notice 'nilai dikembalikan!';
    return v_total + (select p.biaya_ongkir from pesanan p where p.id_pesanan = p_id_pesanan);
    end;
    $$;

    -- fungsi buat cetak laporan, untuk tanggal disesuaikan oleh backend

create or replace function fn_cetak_laporan(
    p_tanggal_awal text,
    p_tanggal_akhir text
)
    returns setof v_cetak_laporan_admin
    language plpgsql
    as $$
    begin
    return query execute format(
        'select * from v_cetak_laporan_admin where tanggal_dipesan between %L::date and %L::date', p_tanggal_awal, p_tanggal_akhir
    );
    end;
    $$;

--pake ini kalau menu terlarisnya mau dibatasi tangal
create function fn_menu_terlaris(
    p_tanggal_awal text,
    p_tanggal_akhir text
)
    returns setof v_menu_terlaris
    language plpgsql
    as $$
    begin
    return query
    select
        v.id_menu,
        v.nama_menu,
        null::date,
        sum(v.total_unit_dipesan)::bigint
    from v_menu_terlaris v
    where v.tanggal_dipesan between p_tanggal_awal::date and p_tanggal_akhir::date
    group by v.id_menu, v.nama_menu
    order by sum(v.total_unit_dipesan) desc;
    end;
    $$;

create or replace function fn_grafik_total_pendapatan(
    p_tanggal_awal text,
    p_tanggal_akhir text
)

returns setof 