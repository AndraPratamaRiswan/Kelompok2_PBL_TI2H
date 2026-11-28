-- =====================================================================
-- KATEGORI
-- =====================================================================

CREATE TABLE public.kategori (
    id_kategori   serial      PRIMARY KEY,
    nama_kategori varchar(50) NOT NULL,
    created_at    timestamp   NOT NULL DEFAULT now(),
    CONSTRAINT kategori_nama_kategori_key UNIQUE (nama_kategori)
);
