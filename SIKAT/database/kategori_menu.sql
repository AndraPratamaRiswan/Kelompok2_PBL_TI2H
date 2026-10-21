create table kategori_menu (
    id_menu integer,
    id_kategori integer,

    constraint fk_id_menu
    foreign key (id_menu) references menu (id_menu)
    on delete cascade,
    constraint fk_id_kategori
    foreign key (id_kategori) references kategori (id_kategori)
    on delete cascade
)