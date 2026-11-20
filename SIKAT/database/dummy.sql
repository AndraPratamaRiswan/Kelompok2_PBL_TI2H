INSERT INTO admin (username, password_hash, nama_lengkap) VALUES
('admin_rina',  '$2b$10$abcdefghijklmnopqrstuuK8vZ1xYwQ2mN3pL4oR5sT6uV7wX8yZ9', 'Rina Wulandari'),
('admin_budi',  '$2b$10$bcdefghijklmnopqrstuvL9wA2yZxR3nO4qM5pS6tU7vW8xY9zA0B', 'Budi Santoso'),
('admin_sari',  '$2b$10$cdefghijklmnopqrstuvwM0xB3zAyS4oP5rN6qT7uV8wX9yZ0aB1C', 'Sari Anggraini');

-- 2. pembeli (no_hp harus 9-15 digit angka saja)
INSERT INTO pembeli (password_hash, no_hp) VALUES
('$2b$10$dKfghijklmnopqrstuvwxN1yC4aBzT5pQ6sO7rU8vW9xY0zA1bC2D','6281234567890'),
('$2b$10$eLghijklmnopqrstuvwxyO2zD5bCaU6qR7tP8sV9wX0yZ1aB2cD3E','6282345678901'),
('$2b$10$fMhijklmnopqrstuvwxyzP3aE6cDbV7rS8uQ9tW0xY1zA2bC3dE4F','6283456789012');

-- 3. menu
INSERT INTO menu (id_admin, nama_menu, deskripsi, harga_menu, gambar_url, is_tersedia) VALUES
(1, 'Nasi Box Ayam Bakar', 'Nasi, ayam bakar, lalapan, sambal, dan kerupuk', 28000.00, 'https://example.com/img/nasi-box-ayam.jpg', TRUE),
(2, 'Snack Box Premium',   'Risoles, lemper, kue lapis, dan air mineral',    22000.00, 'https://example.com/img/snack-box.jpg',    TRUE),
(3, 'Tumpeng Mini',        'Tumpeng nasi kuning dengan lauk pauk lengkap',  150000.00, NULL,                                       FALSE);

select * from admin
-- liat id adminnya, somehow kadang ga mulai dari 1
truncate table admin restart identity cascade;
-- kalo id nya gak 1, 2, 3, execute query ini terus insert into admin lagi

