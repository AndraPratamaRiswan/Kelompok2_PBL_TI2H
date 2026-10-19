-- buat semua filter bisa pake ini @backend
execute format('select * from %I where %I = %L', nama_tabel(atau view), nama_kolom, nilai)

-- buat query menambahkan kategori
insert into kategori
values();