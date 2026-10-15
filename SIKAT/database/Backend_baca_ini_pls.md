validasi - validasi yang dibutuhkan :
1. fitur tambah pesanan
   - karena pesanan bisa dilakukan dengan 2 cara, yaitu orang yang udah punya akun lewat nomor hp, dan orang yang belum punya akun tapi langsung pesan, (admin harus bisa input 22nya), maka dibagian backend fitur tersebut, perlu validasi, 
    apakah hp sudah terdaftar pada database pembeli atau belum?
    kalo udah terdaftar : cukup panggil stored procedure sp_tambah_pesanan
    kalo belum : query membuat akun dulu (ntar aku ada), baru panggil sp_tambah_pesanan
2. fitur update pesanan