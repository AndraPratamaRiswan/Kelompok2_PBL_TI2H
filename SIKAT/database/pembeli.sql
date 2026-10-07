CREATE TABLE pembeli (
    id_pembeli     SERIAL       PRIMARY KEY,
    username       VARCHAR(50)  NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    nama_pembeli   VARCHAR(100) NOT NULL,
    no_hp          VARCHAR(20)  NOT NULL UNIQUE,
    alamat_pembeli VARCHAR(255) NOT NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_pembeli_no_hp CHECK (no_hp ~ '^[0-9]{9,15}$')
);