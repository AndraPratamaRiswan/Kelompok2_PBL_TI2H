create table admin (
    id_admin serial primary key,
    username varchar(50) not null unique,
    password_hash varchar(255) not null,
    nama_lengkap varchar(100) not null,
    created_at timestamp not null default now()
);

create table kategori (
    id_kategori Serial primary key,
    nama_kategori varchar(50) not null unique,
    created_at timestamp not null default now()
);

CREATE TABLE pembeli (
    id_pembeli     SERIAL       PRIMARY KEY,
    password_hash  VARCHAR(255) NOT NULL,
    no_hp          VARCHAR(20)  NOT NULL UNIQUE,
    created_at     TIMESTAMP    NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_pembeli_no_hp CHECK (no_hp ~ '^[0-9]{9,15}$')
);

CREATE TABLE menu (
    id_menu     SERIAL        PRIMARY KEY,
    id_admin    INTEGER       NOT NULL,
    nama_menu   VARCHAR(100)  NOT NULL,
    deskripsi   VARCHAR(255),
    harga_menu  NUMERIC(12,2) NOT NULL,  
    gambar_url  VARCHAR(255),                  
    is_tersedia BOOLEAN       NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP     NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_menu_admin
        FOREIGN KEY (id_admin) REFERENCES admin (id_admin)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_menu_harga CHECK (harga_menu > 0)
);

CREATE TABLE pesanan (
    id_pesanan         SERIAL        PRIMARY KEY,
    id_admin           INTEGER       NOT NULL,    -- admin yang memproses/mencatat
    id_pembeli         INTEGER       NOT NULL,
    tanggal_dipesan    DATE          NOT NULL DEFAULT NOW(),
    tanggal_pengiriman DATE          NOT NULL,
    jam_pengiriman     TIME          NOT NULL,
    alamat_pengiriman  VARCHAR(255)  NOT NULL,
    nama_pembeli       VARCHAR(50)   NOT NULL,
    catatan            VARCHAR(255),
    status_pesanan     VARCHAR(15)   NOT NULL DEFAULT 'Belum Dikonfirmasi',
    status_pembayaran  VARCHAR(15)   NOT NULL DEFAULT 'Belum Bayar',
    total_harga        NUMERIC(12,2) NOT NULL DEFAULT 0,
    biaya_ongkir       NUMERIC(12,2) NOT NULL DEFAULT 0,
    created_at         TIMESTAMP     NOT NULL DEFAULT NOW(),
    updated_at         TIMESTAMP     NOT NULL DEFAULT NOW(),
    jumlah_dp          NUMERIC(12,2) NOT NULL DEFAULT 0,  -- diisi manual oleh admin
    CONSTRAINT fk_pesanan_admin
        FOREIGN KEY (id_admin) REFERENCES admin (id_admin)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pesanan_pembeli
        FOREIGN KEY (id_pembeli) REFERENCES pembeli (id_pembeli)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_pesanan_status
        CHECK (status_pesanan IN ('Belum Dikonfirmasi', 'Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai')),
    CONSTRAINT chk_pesanan_pembayaran
        CHECK (status_pembayaran IN ('Belum Bayar', 'DP', 'Lunas')),
    CONSTRAINT chk_pesanan_dp CHECK (jumlah_dp >= 0),
    CONSTRAINT chk_pesanan_tanggal
        CHECK (tanggal_pengiriman >= tanggal_dipesan::date)
);

create table kategori_menu (
    id_menu integer,
    id_kategori integer,

    constraint fk_id_menu
    foreign key (id_menu) references menu (id_menu)
    on delete cascade,
    constraint fk_id_kategori
    foreign key (id_kategori) references kategori (id_kategori)
    on delete cascade
);

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
    sum(dp.jumlah) as total_unit_dipesan
from detail_pesanan dp
join menu m
on dp.id_menu = m.id_menu
join pesanan p
on dp.id_pesanan = p.id_pesanan
group by dp.id_menu, m.nama_menu

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

create or replace procedure sp_tambah_menu(
    p_id_admin integer,
    p_id_kategori integer[],
    p_nama_menu varchar(100),
    p_harga numeric(12,2),
    p_deskripsi varchar(255),
    p_gambar_menu varchar(255)
)

    language plpgsql
    as $$
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

    create or replace procedure sp_edit_menu(
        p_id_menu integer,
        p_id_kategori integer[],
        p_nama_menu varchar(100),
        p_harga numeric(12,2),
        p_deskripsi varchar(255),
        p_gambar_menu varchar(255)
    )
    
    language plpgsql
    as $$
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
    



    


