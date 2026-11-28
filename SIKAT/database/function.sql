-- =====================================================================
-- FUNCTION
-- Termasuk dua trigger function: fn_audit_pesanan dan fn_set_updated_at.
-- =====================================================================

-- fn_audit_pesanan
CREATE OR REPLACE FUNCTION public.fn_audit_pesanan() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_data  jsonb;
    v_admin integer := NULLIF(current_setting('app.id_admin', true), '')::integer;
BEGIN
    IF TG_OP = 'INSERT' THEN
        INSERT INTO log_pesanan (id_pesanan, aksi, perubahan, id_admin)
        VALUES (NEW.id_pesanan, 'INSERT', to_jsonb(NEW), v_admin);
        RETURN NEW;

    ELSIF TG_OP = 'UPDATE' THEN
        -- bandingkan baris lama dan baru kolom demi kolom, simpan yang beda saja
        SELECT jsonb_object_agg(
                   n.key,
                   jsonb_build_object('lama', o.value, 'baru', n.value)
               )
        INTO v_data
        FROM jsonb_each(to_jsonb(NEW)) AS n
        JOIN jsonb_each(to_jsonb(OLD)) AS o ON o.key = n.key
        WHERE n.value IS DISTINCT FROM o.value
          AND n.key <> 'updated_at';

        IF v_data IS NOT NULL THEN   -- NULL = tidak ada perubahan berarti
            INSERT INTO log_pesanan (id_pesanan, aksi, perubahan, id_admin)
            VALUES (NEW.id_pesanan, 'UPDATE', v_data, v_admin);
        END IF;
        RETURN NEW;

    ELSE  -- DELETE
        v_data := jsonb_build_object(
            'pesanan', to_jsonb(OLD),
            'items', COALESCE((
                SELECT jsonb_agg(jsonb_build_object(
                           'id_menu',      dp.id_menu,
                           'nama_menu',    m.nama_menu,
                           'jumlah',       dp.jumlah,
                           'harga_satuan', dp.harga_satuan))
                FROM detail_pesanan dp
                JOIN menu m ON m.id_menu = dp.id_menu
                WHERE dp.id_pesanan = OLD.id_pesanan
            ), '[]'::jsonb)
        );
        INSERT INTO log_pesanan (id_pesanan, aksi, perubahan, id_admin)
        VALUES (OLD.id_pesanan, 'DELETE', v_data, v_admin);
        RETURN OLD;
    END IF;
END;
$$;

-- fn_cari_atau_buat_pembeli
CREATE OR REPLACE FUNCTION public.fn_cari_atau_buat_pembeli(p_no_hp character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_id integer;
BEGIN
    INSERT INTO pembeli (password_hash, no_hp)
    VALUES ('!' || md5(random()::text || clock_timestamp()::text), p_no_hp)
    ON CONFLICT (no_hp) DO NOTHING
    RETURNING id_pembeli INTO v_id;

    IF v_id IS NULL THEN
        -- nomor sudah terdaftar: ambil id yang ada
        SELECT id_pembeli INTO STRICT v_id
        FROM pembeli
        WHERE no_hp = p_no_hp;
    END IF;

    RETURN v_id;
END;
$$;

-- fn_cari_menu
CREATE OR REPLACE FUNCTION public.fn_cari_menu(p_kata text) RETURNS TABLE(menu_id integer, skor real)
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE
    v_teks  text;
    v_query tsquery;
BEGIN
    v_teks := array_to_string(ARRAY(
        SELECT w || ':*'
        FROM unnest(regexp_split_to_array(
                 trim(regexp_replace(coalesce(p_kata, ''), '[^[:alnum:]]+', ' ', 'g')),
                 '\s+')) AS w
        WHERE w <> ''
    ), ' & ');

    IF v_teks = '' THEN
        RETURN;                      -- input kosong/simbol saja: tidak ada hasil
    END IF;

    v_query := to_tsquery('simple', v_teks);

    RETURN QUERY
    SELECT m.id_menu, ts_rank(m.dokumen_cari, v_query)
    FROM menu m
    WHERE m.dokumen_cari @@ v_query
    ORDER BY 2 DESC;
END;
$$;

-- fn_cetak_laporan
CREATE OR REPLACE FUNCTION public.fn_cetak_laporan(p_tanggal_awal text, p_tanggal_akhir text) RETURNS SETOF public.v_cetak_laporan_admin
    LANGUAGE plpgsql
    AS $$
    begin
    return query execute format(
        'select * from v_cetak_laporan_admin where tanggal_dipesan between %L::date and %L::date', p_tanggal_awal, p_tanggal_akhir
    );
    end;
    $$;

-- fn_daftar_pembeli
CREATE OR REPLACE FUNCTION public.fn_daftar_pembeli(p_no_hp character varying, p_password_hash character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- 1) nomor belum ada -> buat akun baru
    INSERT INTO pembeli (password_hash, no_hp)
    VALUES (p_password_hash, p_no_hp)
    ON CONFLICT (no_hp) DO NOTHING;

    IF FOUND THEN
        RETURN 'dibuat';
    END IF;

    -- 2) nomor sudah ada tapi belum diklaim (dibuat admin) -> pasang kata sandi
    UPDATE pembeli
    SET password_hash = p_password_hash
    WHERE no_hp = p_no_hp
      AND password_hash LIKE '!%';

    IF FOUND THEN
        RETURN 'diklaim';
    END IF;

    -- 3) nomor sudah punya akun aktif
    RETURN 'sudah_terdaftar';
END;
$$;

-- fn_hitung_subtotal
CREATE OR REPLACE FUNCTION public.fn_hitung_subtotal(p_harga_satuan numeric, p_jumlah integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
    begin
        return p_harga_Satuan * p_jumlah;
    end;
    $$;

-- fn_hitung_total
CREATE OR REPLACE FUNCTION public.fn_hitung_total(p_id_pesanan integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
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

-- fn_menu_terlaris
CREATE OR REPLACE FUNCTION public.fn_menu_terlaris(p_tanggal_awal text, p_tanggal_akhir text) RETURNS SETOF public.v_menu_terlaris
    LANGUAGE plpgsql
    AS $$
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

-- fn_set_updated_at
CREATE OR REPLACE FUNCTION public.fn_set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at := now();
    RETURN NEW;
END;
$$;

-- fn_ubah_status_menu
CREATE OR REPLACE FUNCTION public.fn_ubah_status_menu(p_id_menu integer, p_tersedia boolean) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE menu
    SET is_tersedia = p_tersedia
    WHERE id_menu = p_id_menu;

    RETURN FOUND;
END;
$$;
