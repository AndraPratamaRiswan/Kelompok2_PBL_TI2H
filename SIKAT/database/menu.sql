-- =====================================================================
-- MENU
-- Butuh tabel admin.
-- =====================================================================

CREATE TABLE public.menu (
    id_menu      serial        PRIMARY KEY,
    id_admin     integer       NOT NULL,
    nama_menu    varchar(100)  NOT NULL,
    deskripsi    varchar(255),
    harga_menu   numeric(12,2) NOT NULL,
    gambar_url   varchar(255),
    is_tersedia  boolean       NOT NULL DEFAULT true,
    created_at   timestamp     NOT NULL DEFAULT now(),
    updated_at   timestamp     NOT NULL DEFAULT now(),
    -- kolom pencarian teks, diisi otomatis dari nama_menu (bobot A) dan deskripsi (bobot B)
    dokumen_cari tsvector GENERATED ALWAYS AS (
        setweight(to_tsvector('simple', COALESCE(nama_menu, '')), 'A') ||
        setweight(to_tsvector('simple', COALESCE(deskripsi, '')), 'B')
    ) STORED,
    CONSTRAINT chk_menu_harga CHECK (harga_menu > 0),
    CONSTRAINT fk_menu_admin FOREIGN KEY (id_admin)
        REFERENCES public.admin (id_admin) ON UPDATE CASCADE ON DELETE RESTRICT
);
