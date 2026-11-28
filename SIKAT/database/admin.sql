CREATE TABLE public.admin (
    id_admin      serial       PRIMARY KEY,
    username      varchar(50)  NOT NULL,
    password_hash varchar(255) NOT NULL,
    nama_lengkap  varchar(100) NOT NULL,
    created_at    timestamp    NOT NULL DEFAULT now(),
    CONSTRAINT admin_username_key UNIQUE (username)
);