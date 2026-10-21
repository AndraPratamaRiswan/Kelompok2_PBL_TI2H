create table kategori (
    id_kategori Serial primary key,
    nama_kategori varchar(50) not null unique,
    created_at timestamp not null default now,
)