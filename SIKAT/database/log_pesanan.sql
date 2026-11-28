-- =====================================================================
-- LOG_PESANAN (tambahan)
-- Riwayat perubahan pesanan, diisi trigger fn_audit_pesanan.
-- =====================================================================

CREATE TABLE public.log_pesanan (
    id_log     bigserial   PRIMARY KEY,
    id_pesanan integer     NOT NULL,
    aksi       varchar(10) NOT NULL,
    perubahan  jsonb       NOT NULL,
    id_admin   integer,
    waktu      timestamp   NOT NULL DEFAULT now(),
    CONSTRAINT chk_log_pesanan_aksi CHECK (aksi IN ('INSERT', 'UPDATE', 'DELETE'))
);
