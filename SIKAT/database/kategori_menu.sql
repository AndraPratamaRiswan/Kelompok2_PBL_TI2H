-- =====================================================================
-- KATEGORI_MENU
-- Tabel penghubung menu dan kategori. Butuh tabel menu dan kategori.
-- =====================================================================

CREATE TABLE public.kategori_menu (
    id_menu     integer,
    id_kategori integer,
    CONSTRAINT fk_id_menu     FOREIGN KEY (id_menu)
        REFERENCES public.menu (id_menu) ON DELETE CASCADE,
    CONSTRAINT fk_id_kategori FOREIGN KEY (id_kategori)
        REFERENCES public.kategori (id_kategori) ON DELETE CASCADE
);
