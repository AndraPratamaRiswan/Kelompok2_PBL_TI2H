-- =====================================================================
-- LANGKAH 1: hapus pesanan dan ubah status menu
-- Jalankan di database sikat_db (psql atau pgAdmin Query Tool).
-- Aman dijalankan berulang karena memakai CREATE OR REPLACE.
--
-- Catatan: menu TIDAK dihapus, hanya dinonaktifkan (soft delete).
-- Ini sengaja: menu yang sudah pernah dipesan harus tetap ada demi
-- riwayat pesanan. FK fk_detail_menu (ON DELETE RESTRICT) tetap
-- menjaga hal itu di level database.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1a. Hapus pesanan (PROSEDUR)
-- detail_pesanan ikut terhapus otomatis karena ON DELETE CASCADE.
-- Kalau id tidak ada, prosedur melempar error dengan kode P0002
-- (no_data_found) supaya PHP bisa membedakannya dari error lain.
-- ---------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE sp_hapus_pesanan(p_id_pesanan integer)
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

-- ---------------------------------------------------------------------
-- 1b. Aktifkan / nonaktifkan menu (FUNCTION)
-- Hasil: true kalau menu ada dan berhasil diubah, false kalau tidak ada.
-- Catatan: updated_at masih diisi manual di sini. Di langkah trigger
-- nanti baris updated_at ini kita hapus karena diurus trigger.
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_ubah_status_menu(
    p_id_menu  integer,
    p_tersedia boolean
)
RETURNS boolean
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE menu
    SET is_tersedia = p_tersedia,
        updated_at  = now()
    WHERE id_menu = p_id_menu;

    RETURN FOUND;
END;
$$;

-- =====================================================================
-- TES (jalankan satu per satu setelah skrip di atas berhasil)
-- Pakai BEGIN ... ROLLBACK supaya data aslimu tidak berubah.
-- =====================================================================
-- BEGIN;
--   SELECT fn_ubah_status_menu(999999, false); -- harapannya: f (false)
--   SELECT fn_ubah_status_menu(1, false);      -- harapannya: t (kalau menu id 1 ada)
--   SELECT is_tersedia FROM menu WHERE id_menu = 1;  -- harapannya: f
--   CALL sp_hapus_pesanan(999999);             -- harapannya: ERROR ... tidak ditemukan
-- ROLLBACK;
--
-- Setelah error di atas, transaksi di psql berstatus "aborted".
-- Jalankan ROLLBACK; lalu mulai lagi dengan BEGIN; kalau mau tes lanjutan.
--
-- Tes hapus pesanan sungguhan (ganti 1 dengan id pesanan yang ada):
-- BEGIN;
--   CALL sp_hapus_pesanan(1);
--   SELECT count(*) FROM detail_pesanan WHERE id_pesanan = 1;  -- harapannya: 0
-- ROLLBACK;
-- =====================================================================
-- LANGKAH 2: tambah pesanan dari Admin (cari/buat pembeli + simpan pesanan)
-- Jalankan di database sikat_db. Aman diulang (CREATE OR REPLACE).
--
-- Yang berubah: logika "cari pembeli berdasarkan no HP, kalau belum ada
-- buatkan akunnya" pindah dari PHP (psnCariAtauBuatPembeli + beginTransaction)
-- ke database. sp_tambah_pesanan yang lama TIDAK diubah, jadi sisi Pembeli
-- (Pembeli/includes/queries/pesanan.php) tetap aman.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 2a. fn_cari_atau_buat_pembeli (FUNCTION)
-- Mengembalikan id_pembeli untuk sebuah no HP. Kalau belum ada, akun
-- dibuat dengan password_hash yang tidak mungkin cocok dengan password
-- apa pun ('!' + teks acak), jadi akun itu belum bisa dipakai login.
--
-- Pola INSERT ... ON CONFLICT DO NOTHING lalu SELECT aman untuk dua
-- proses yang membuat nomor yang sama bersamaan: yang kedua menunggu
-- yang pertama selesai, lalu mengambil baris yang sudah ada.
-- Format no HP dijaga oleh CHECK chk_pembeli_no_hp (9-15 digit).
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_cari_atau_buat_pembeli(p_no_hp varchar)
RETURNS integer
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

-- ---------------------------------------------------------------------
-- 2b. sp_tambah_pesanan_admin (PROSEDUR)
-- Urutannya: cari/buat pembeli -> panggil sp_tambah_pesanan.
-- Semua terjadi dalam SATU transaksi (satu CALL = satu transaksi).
-- Kalau pesanan gagal (status ngawur, FK, CHECK, dst.), akun pembeli yang
-- baru dibuat ikut dibatalkan. Tidak perlu beginTransaction() di PHP.
-- ---------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE sp_tambah_pesanan_admin(
    p_id_admin          integer,
    p_no_hp             varchar(20),
    p_nama_pembeli      varchar(50),
    p_id_menu           integer[],
    p_jumlah            integer[],
    p_alamat_pengiriman varchar(255),
    p_status_pesanan    varchar(15),
    p_status_pembayaran varchar(15),
    p_tanggal_pengiriman date,
    p_jam_pengiriman    time,
    p_catatan           varchar(255),
    p_jumlah_dp         numeric(12,2),
    p_biaya_ongkir      numeric(12,2)
)
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

-- =====================================================================
-- TES. Ganti id admin (1), id menu (1), dan nomor HP sesuai datamu.
-- =====================================================================

-- Tes 1: nomor HP BARU -> pembeli dibuat + pesanan tersimpan.
-- BEGIN;
--   CALL sp_tambah_pesanan_admin(
--       1, '081200000001', 'Tes Baru', ARRAY[1], ARRAY[15],
--       'Jl. Tes No. 1', 'Dikonfirmasi', 'Belum Bayar',
--       current_date + 7, '10:00', NULL, 0, 0);
--   SELECT id_pembeli, no_hp, left(password_hash, 1) AS awal_hash
--     FROM pembeli WHERE no_hp = '081200000001';        -- 1 baris, awal_hash = !
--   SELECT id_pesanan, nama_pembeli, total_harga
--     FROM pesanan ORDER BY id_pesanan DESC LIMIT 1;     -- total_harga > 0
-- ROLLBACK;

-- Tes 2: nomor HP SUDAH ADA -> tidak membuat pembeli ganda.
-- (ganti '08xxxxxxxxxx' dengan nomor dari: SELECT no_hp FROM pembeli LIMIT 1;)
-- BEGIN;
--   CALL sp_tambah_pesanan_admin(
--       1, '08xxxxxxxxxx', 'Tes Lama', ARRAY[1], ARRAY[15],
--       'Jl. Tes No. 2', 'Dikonfirmasi', 'Belum Bayar',
--       current_date + 7, '10:00', NULL, 0, 0);
--   SELECT count(*) FROM pembeli WHERE no_hp = '08xxxxxxxxxx';   -- tetap 1
-- ROLLBACK;

-- Tes 3 (bukti ATOMISITAS): pesanan gagal -> pembeli baru juga tidak tersimpan.
-- Status 'NGAWUR' melanggar chk_pesanan_status, jadi CALL ini harus ERROR.
-- Tidak perlu BEGIN/ROLLBACK karena memang tidak boleh ada yang tersimpan.
--   CALL sp_tambah_pesanan_admin(
--       1, '081200000002', 'Tes Gagal', ARRAY[1], ARRAY[15],
--       'Jl. Tes No. 3', 'NGAWUR', 'Belum Bayar',
--       current_date + 7, '10:00', NULL, 0, 0);
--   SELECT count(*) FROM pembeli WHERE no_hp = '081200000002';   -- harapannya 0


-- =====================================================================
-- B. KLAIM AKUN PEMBELI
-- Pelanggan yang pernah memesan lewat admin (telepon/WhatsApp) sudah punya
-- baris di tabel pembeli dengan password_hash berawalan '!' (lihat
-- fn_cari_atau_buat_pembeli di langkah 2). Hash seperti itu tidak akan
-- pernah cocok dengan kata sandi apa pun, jadi akun itu "belum diklaim".
-- Saat pelanggan mendaftar dengan nomor yang sama, kata sandinya dipasang
-- ke baris yang sama, sehingga riwayat pesanannya ikut.
-- =====================================================================

-- ---------------------------------------------------------------------
-- fn_daftar_pembeli (FUNCTION)
-- p_password_hash = hash bcrypt dari PHP (bukan kata sandi asli).
-- Hasil: 'dibuat' | 'diklaim' | 'sudah_terdaftar'
--
-- Konkurensi: dua orang mendaftar dengan nomor yang sama bersamaan.
--  - INSERT ... ON CONFLICT DO NOTHING: hanya satu yang berhasil membuat.
--  - UPDATE ... WHERE password_hash LIKE '!%': hanya satu yang berhasil
--    mengklaim, karena yang kedua mengevaluasi ulang kondisinya setelah
--    yang pertama commit dan sudah tidak cocok lagi.
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_daftar_pembeli(
    p_no_hp         varchar,
    p_password_hash varchar
)
RETURNS text
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

-- =====================================================================
-- LANGKAH 3: TRIGGER
-- Jalankan di database sikat_db. Aman diulang (DROP TRIGGER IF EXISTS,
-- CREATE OR REPLACE, CREATE TABLE IF NOT EXISTS).
-- Butuh PostgreSQL 11 atau lebih baru (sintaks EXECUTE FUNCTION).
--
-- Isi:
--   3a. updated_at otomatis (pesanan dan menu)
--   3b. status pesanan tidak boleh mundur
--   3c. validasi DP di akhir transaksi (constraint trigger, DEFERRED)
--   3d. audit log pesanan dalam JSONB
--
-- Setelah teruji, salin isi file ini ke SIKAT/database/trigger.sql
-- (yang sekarang kosong) dan ke akhir complete.sql.
-- =====================================================================


-- =====================================================================
-- 3a. updated_at otomatis
-- Sebelumnya updated_at diisi manual di sp_edit_pesanan, sp_edit_menu,
-- dan fn_ubah_status_menu. Sekarang cukup satu trigger untuk semua
-- UPDATE, dari jalur mana pun (PHP, prosedur, atau SQL langsung).
-- Baris "updated_at = current_timestamp" di sp_edit_pesanan dan
-- sp_edit_menu tidak berbahaya kalau dibiarkan, dan boleh dihapus nanti.
-- =====================================================================
CREATE OR REPLACE FUNCTION fn_set_updated_at()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.updated_at := now();
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_pesanan_updated_at ON pesanan;
CREATE TRIGGER trg_pesanan_updated_at
    BEFORE UPDATE ON pesanan
    FOR EACH ROW
    EXECUTE FUNCTION fn_set_updated_at();

DROP TRIGGER IF EXISTS trg_menu_updated_at ON menu;
CREATE TRIGGER trg_menu_updated_at
    BEFORE UPDATE ON menu
    FOR EACH ROW
    EXECUTE FUNCTION fn_set_updated_at();

-- fn_ubah_status_menu (dari langkah 1) tidak perlu mengisi updated_at lagi
CREATE OR REPLACE FUNCTION fn_ubah_status_menu(
    p_id_menu  integer,
    p_tersedia boolean
)
RETURNS boolean
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE menu
    SET is_tersedia = p_tersedia
    WHERE id_menu = p_id_menu;

    RETURN FOUND;
END;
$$;



-- =====================================================================
-- 3d. Audit log pesanan (JSONB)
-- Setiap INSERT, UPDATE, DELETE pada pesanan tercatat di log_pesanan.
--   INSERT : seluruh baris baru sebagai JSONB
--   UPDATE : hanya kolom yang berubah, bentuk {"kolom": {"lama":..,"baru":..}}
--   DELETE : baris pesanan + daftar item-nya (disalin SEBELUM detail_pesanan
--            ikut terhapus oleh ON DELETE CASCADE)
-- Tanpa FOREIGN KEY ke pesanan: log harus tetap ada setelah pesanan dihapus.
--
-- Catatan: satu pesanan baru menghasilkan 2 baris log (INSERT, lalu UPDATE
-- total_harga), karena prosedur mengisi total setelah detail masuk.
--
-- Kolom id_admin dibaca dari setting sesi 'app.id_admin' kalau ada. Selama
-- PHP belum mengisinya, kolom ini NULL (aman). Cara mengisinya dari PHP
-- bisa kita kerjakan di langkah lanjutan.
-- =====================================================================
CREATE TABLE IF NOT EXISTS log_pesanan (
    id_log      BIGSERIAL    PRIMARY KEY,
    id_pesanan  INTEGER      NOT NULL,
    aksi        VARCHAR(10)  NOT NULL,
    perubahan   JSONB        NOT NULL,
    id_admin    INTEGER,
    waktu       TIMESTAMP    NOT NULL DEFAULT now(),
    CONSTRAINT chk_log_pesanan_aksi CHECK (aksi IN ('INSERT', 'UPDATE', 'DELETE'))
);

CREATE INDEX IF NOT EXISTS idx_log_pesanan_pesanan_waktu
    ON log_pesanan (id_pesanan, waktu DESC);

CREATE OR REPLACE FUNCTION tg_audit_pesanan()
RETURNS trigger
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

DROP TRIGGER IF EXISTS trg_pesanan_audit_iu ON pesanan;
CREATE TRIGGER trg_pesanan_audit_iu
    AFTER INSERT OR UPDATE ON pesanan
    FOR EACH ROW
    EXECUTE FUNCTION fn_audit_pesanan();

-- BEFORE DELETE supaya detail_pesanan masih ada saat item disalin
DROP TRIGGER IF EXISTS trg_pesanan_audit_d ON pesanan;
CREATE TRIGGER trg_pesanan_audit_d
    BEFORE DELETE ON pesanan
    FOR EACH ROW
    EXECUTE FUNCTION fn_audit_pesanan();


-- =====================================================================
-- TES. Ganti id admin (1), id menu (1), dan id pesanan (X) sesuai datamu.
-- Pilih pesanan X yang status_pesanan-nya 'Belum Dikonfirmasi'.
-- =====================================================================

-- Tes 3a: updated_at berubah otomatis
-- BEGIN;
--   SELECT updated_at FROM menu WHERE id_menu = 1;
--   UPDATE menu SET deskripsi = deskripsi WHERE id_menu = 1;
--   SELECT updated_at FROM menu WHERE id_menu = 1;        -- harus lebih baru
-- ROLLBACK;

-- Tes 3b: status maju boleh, mundur ditolak
-- BEGIN;
--   UPDATE pesanan SET status_pesanan = 'Dikirim'  WHERE id_pesanan = X;   -- OK
--   UPDATE pesanan SET status_pesanan = 'Diproses' WHERE id_pesanan = X;   -- ERROR: tidak boleh mundur
-- ROLLBACK;

-- Tes 3c: DP melebihi total -> seluruh pesanan dibatalkan di akhir transaksi
--   SELECT count(*) FROM pesanan;                                         -- catat angkanya
--   CALL sp_tambah_pesanan_admin(1, '081200000001', 'Tes DP', ARRAY[1], ARRAY[15],
--        'Jl. Tes', 'Dikonfirmasi', 'DP', current_date + 7, '10:00', NULL, 999999999, 0);
--                                                                         -- ERROR: DP melebihi total
--   SELECT count(*) FROM pesanan;                                         -- harus sama
--
-- Tes 3c (DP wajar, harus lolos). SET CONSTRAINTS memaksa pengecekan dilakukan
-- sekarang, karena di dalam BEGIN ... ROLLBACK tidak ada COMMIT yang memicunya:
-- BEGIN;
--   CALL sp_tambah_pesanan_admin(1, '081200000001', 'Tes DP', ARRAY[1], ARRAY[15],
--        'Jl. Tes', 'Dikonfirmasi', 'DP', current_date + 7, '10:00', NULL, 1000, 0);
--   SET CONSTRAINTS trg_pesanan_cek_dp IMMEDIATE;                         -- tidak error
-- ROLLBACK;

-- Tes 3d: audit log
-- BEGIN;
--   UPDATE pesanan SET status_pesanan = 'Dikonfirmasi', catatan = 'tes audit' WHERE id_pesanan = X;
--   SELECT aksi, perubahan FROM log_pesanan WHERE id_pesanan = X ORDER BY id_log DESC LIMIT 1;
--   -- membaca isi JSONB:
--   SELECT perubahan -> 'status_pesanan' ->> 'lama' AS dari,
--          perubahan -> 'status_pesanan' ->> 'baru' AS ke
--   FROM log_pesanan WHERE id_pesanan = X AND aksi = 'UPDATE' ORDER BY id_log DESC LIMIT 1;
--   -- hapus pesanan, lalu lihat salinan item-nya di log:
--   CALL sp_hapus_pesanan(X);
--   SELECT jsonb_pretty(perubahan) FROM log_pesanan
--   WHERE id_pesanan = X AND aksi = 'DELETE';
-- ROLLBACK;

-- =====================================================================
-- LANGKAH 4: INDEX DAN FULL-TEXT SEARCH (FTS)
-- Jalankan di database sikat_db. Aman diulang (IF NOT EXISTS, OR REPLACE).
-- Butuh PostgreSQL 12+ (kolom GENERATED, yang sudah kamu pakai di subtotal).
--
-- Catatan pgAdmin: satu kali Run = satu transaksi. Kalau ada satu error,
-- SEMUA perubahan di file ini dibatalkan, jadi aman diulang setelah diperbaiki.
-- =====================================================================


-- =====================================================================
-- 4a. INDEX pada tabel pesanan
-- Setiap index dibuat karena ada query di PHP yang memakainya.
-- =====================================================================

-- Riwayat pesanan satu pembeli, terbaru dulu.
-- Dipakai: Pembeli/includes/queries/pesanan.php (pbDaftarPesanan)
--   WHERE id_pembeli = :id ORDER BY created_at DESC, id_pesanan DESC
-- Urutan dan arah kolom di index SAMA dengan ORDER BY, jadi PostgreSQL
-- tidak perlu mengurutkan lagi. id_pembeli juga kolom foreign key, yang
-- tidak otomatis di-index di PostgreSQL.
CREATE INDEX IF NOT EXISTS idx_pesanan_pembeli_created
    ON pesanan (id_pembeli, created_at DESC, id_pesanan DESC);

-- Pesanan pada rentang tanggal.
-- Dipakai: Admin/includes/queries/dashboard.php (dashTrenMingguan)
--   WHERE tanggal_dipesan BETWEEN :awal AND :akhir
CREATE INDEX IF NOT EXISTS idx_pesanan_tanggal_dipesan
    ON pesanan (tanggal_dipesan);

-- Laporan hanya membaca pesanan berstatus 'Selesai'.
-- Dipakai: Admin/includes/queries/laporan.php (lapTransaksi, lapPeriodeTersedia)
--   WHERE status_pesanan = 'Selesai' AND tanggal_dipesan >= .. AND < ..
-- PARTIAL INDEX: hanya baris 'Selesai' yang dimasukkan, jadi index lebih
-- kecil dan lebih cepat dibaca daripada index penuh.
CREATE INDEX IF NOT EXISTS idx_pesanan_selesai_tanggal
    ON pesanan (tanggal_dipesan)
    WHERE status_pesanan = 'Selesai';

-- Kandidat yang SENGAJA tidak dibuat (bahan penjelasan di laporan):
--  - index status_pesanan saja: hanya 5 nilai, tiap nilai mencakup sebagian
--    besar tabel, jadi PostgreSQL tetap memilih Seq Scan.
--  - index id_admin di pesanan/menu: tidak ada query yang mencari lewat admin.
--  - index id_menu di detail_pesanan: pemakaiannya hanya agregasi seluruh
--    tabel (menu terlaris), yang tidak terbantu index.
-- Index menambah biaya setiap INSERT/UPDATE, jadi dibuat seperlunya.


-- =====================================================================
-- 4b. FULL-TEXT SEARCH untuk menu
-- Sebelumnya pencarian memakai ILIKE '%kata%' (Seq Scan, tanpa urutan
-- relevansi). Sekarang: kolom tsvector + index GIN + fungsi pencarian.
-- =====================================================================

-- Dokumen pencarian: nama menu (bobot A, paling penting) + deskripsi (bobot B).
-- GENERATED ... STORED: dihitung dan diperbarui otomatis oleh PostgreSQL setiap
-- nama/deskripsi berubah, jadi tidak perlu trigger atau perubahan di PHP.
-- Config 'simple' = pecah per kata dan huruf kecil, tanpa stemming. Dipakai
-- karena aman di semua versi dan cocok untuk nama makanan.
ALTER TABLE menu
    ADD COLUMN IF NOT EXISTS dokumen_cari tsvector
    GENERATED ALWAYS AS (
        setweight(to_tsvector('simple', coalesce(nama_menu, '')), 'A') ||
        setweight(to_tsvector('simple', coalesce(deskripsi,  '')), 'B')
    ) STORED;

-- GIN = jenis index untuk tsvector (memetakan kata -> daftar baris)
CREATE INDEX IF NOT EXISTS idx_menu_dokumen_cari
    ON menu USING GIN (dokumen_cari);

-- fn_cari_menu(teks): mengembalikan id menu yang cocok beserta skor relevansi.
-- Setiap kata dari input menjadi pencarian AWALAN: "nasi ay" -> 'nasi:* & ay:*'
-- (cocok dengan "Nasi Ayam Geprek"). Karakter selain huruf/angka dibuang dulu,
-- jadi input pengguna seperti tanda kutip atau & tidak bisa merusak query.
-- Kolom hasil sengaja diberi nama menu_id (bukan id_menu) supaya tidak
-- bentrok dengan kolom id_menu di dalam fungsi.
CREATE OR REPLACE FUNCTION fn_cari_menu(p_kata text)
RETURNS TABLE (menu_id integer, skor real)
LANGUAGE plpgsql
STABLE
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

ANALYZE pesanan;
ANALYZE menu;


-- =====================================================================
-- TES FTS (jalankan satu per satu)
-- =====================================================================
-- Isi dokumen pencarian:
--   SELECT nama_menu, dokumen_cari FROM menu LIMIT 3;
--
-- Pencarian dengan skor:
--   SELECT m.nama_menu, f.skor
--   FROM fn_cari_menu('nasi') f JOIN menu m ON m.id_menu = f.menu_id
--   ORDER BY f.skor DESC;
--
-- Awalan dan beberapa kata:
--   SELECT m.nama_menu FROM fn_cari_menu('ayam bak') f JOIN menu m ON m.id_menu = f.menu_id;
--
-- Input aneh tidak error, hasilnya kosong:
--   SELECT * FROM fn_cari_menu('!!! & |');
--
-- Bukti index GIN bisa dipakai. Tabel menu hanya puluhan baris, jadi PostgreSQL
-- biasanya memilih Seq Scan. Kita paksa untuk membuktikan index-nya ada dan
-- berfungsi (cari "Bitmap Index Scan on idx_menu_dokumen_cari" di hasilnya):
--   SET enable_seqscan = off;
--   EXPLAIN SELECT id_menu FROM menu WHERE dokumen_cari @@ to_tsquery('simple', 'nasi:*');
--   RESET enable_seqscan;


