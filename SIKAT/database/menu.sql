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
    kategori    VARCHAR(50)   NOT NULL,
    CONSTRAINT fk_menu_admin
        FOREIGN KEY (id_admin) REFERENCES admin (id_admin)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_menu_harga CHECK (harga_menu > 0)
);