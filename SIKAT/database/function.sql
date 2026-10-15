fungsi hitung_subtotal buat ngehitung subtotal pesanan * jumlah per row di detail_pesanan
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