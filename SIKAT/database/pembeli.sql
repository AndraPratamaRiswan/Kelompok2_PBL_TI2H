-- =====================================================================
-- PEMBELI
-- =====================================================================

CREATE TABLE public.pembeli (
    id_pembeli    serial       PRIMARY KEY,
    password_hash varchar(255) NOT NULL,
    no_hp         varchar(20)  NOT NULL,
    created_at    timestamp    NOT NULL DEFAULT now(),
    CONSTRAINT pembeli_no_hp_key UNIQUE (no_hp),
    CONSTRAINT chk_pembeli_no_hp CHECK (no_hp ~ '^[0-9]{9,15}$')
);
