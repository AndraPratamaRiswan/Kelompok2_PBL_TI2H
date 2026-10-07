create table admin (
    id_admin serial primary key,
    username varchar(50) not null unique,
    password_hash varchar(255) not null,
    nama_lengkap varchar(100) not null,
    created_at timestamp not null default now()
);