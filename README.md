# Fio Trans Cargo Website
Website jasa pengiriman barang dengan backend PHP 7.4+ dan frontend Tailwind CSS CDN.

## Fitur Utama

- **Homepage dengan Hero Section** - Tampilan menarik dengan form cek resi
- **Sistem Tracking** - Pelacakan paket secara real-time
- **Form Booking** - Pemesanan pengiriman online
- **Blog System** - Manajemen artikel dan konten
- **Responsive Design** - Optimal di semua perangkat
- **RESTful API** - Backend API yang fleksibel
- **Easy Installation** - Installer otomatis

## Teknologi yang Digunakan

### Backend
- PHP 7.4+
- MySQL Database
- RESTful API Architecture
- PDO untuk database connection

### Frontend
- HTML5 & CSS3
- Tailwind CSS CDN
- Vanilla JavaScript
- Font Awesome Icons
- Responsive Design

## Struktur Proyek

```
fio_trans_cargo_website/
├── backend/
│   ├── api/
│   │   ├── pages.php
│   │   ├── posts.php
│   │   └── bookings.php
│   ├── config/
│   │   └── database.php
│   ├── models/
│   │   ├── Page.php
│   │   ├── Post.php
│   │   ├── Category.php
│   │   └── Booking.php
│   ├── database/
│   │   └── schema.sql
│   ├── .htaccess
│   └── index.php
├── frontend/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   ├── js/
│   │   │   └── main.js
│   │   └── images/
│   └── index.html
├── install.php
└── README.md
```

## Instalasi

### Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- mod_rewrite enabled (untuk Apache)

### Langkah Instalasi

1. **Upload Files**
   ```bash
   # Upload semua file ke direktori web server
   # Contoh: public_html/calista_express/
   ```

2. **Jalankan Installer**
   ```
   http://yourdomain.com/calista_express/install.php
   ```

3. **Konfigurasi Database**
   - Masukkan informasi database MySQL
   - Installer akan otomatis membuat database dan tabel

4. **Konfigurasi Website**
   - Atur judul dan deskripsi website
   - Buat akun administrator

5. **Selesai**
   - Website siap digunakan
   - Hapus file `install.php` untuk keamanan

## API Endpoints

### Pages API
- `GET /api/pages` - Mendapatkan semua halaman
- `GET /api/pages?slug=home` - Mendapatkan halaman berdasarkan slug
- `POST /api/pages` - Membuat halaman baru
- `PUT /api/pages` - Mengupdate halaman
- `DELETE /api/pages` - Menghapus halaman

### Posts API
- `GET /api/posts` - Mendapatkan semua artikel
- `GET /api/posts?slug=article-slug` - Mendapatkan artikel berdasarkan slug
- `POST /api/posts` - Membuat artikel baru
- `PUT /api/posts` - Mengupdate artikel
- `DELETE /api/posts` - Menghapus artikel

### Bookings API
- `GET /api/bookings` - Mendapatkan semua booking (admin)
- `GET /api/bookings?tracking_number=CE250109001` - Tracking paket
- `POST /api/bookings` - Membuat booking baru
- `PUT /api/bookings` - Update status booking
- `DELETE /api/bookings` - Menghapus booking

## Konfigurasi

### Database Configuration
File: `backend/config/database.php`
```php
private $host = 'localhost';
private $db_name = 'calista_express';
private $username = 'your_username';
private $password = 'your_password';
```

### Frontend Configuration
File: `frontend/assets/js/main.js`
```javascript
// Sesuaikan dengan lokasi backend Anda
const API_BASE_URL = '../backend';
```

## Fitur Utama Website

### 1. Homepage
- Hero section dengan tagline menarik
- Form cek resi terintegrasi
- Informasi layanan dan keunggulan
- Testimoni pelanggan

### 2. Sistem Tracking
- Input nomor resi
- Tampilan status pengiriman
- Informasi detail paket
- Real-time updates

### 3. Form Booking
- Data pengirim dan penerima
- Pilihan jenis layanan
- Deskripsi barang
- Generate nomor resi otomatis

### 4. Blog System
- Manajemen artikel
- Kategori artikel
- Featured images
- SEO-friendly URLs

## Keamanan

- Input validation dan sanitization
- SQL injection protection dengan PDO
- XSS protection
- CORS headers configuration
- Secure file permissions

## Kompatibilitas Shared Hosting

Website ini dirancang khusus untuk kompatibilitas dengan shared hosting:
- Tidak memerlukan akses shell
- Menggunakan .htaccess untuk URL rewriting
- Database setup otomatis
- Installer yang user-friendly

## Dukungan dan Pengembangan

### Pengembangan Selanjutnya
- Dashboard admin
- User management
- Media management
- SEO optimization tools
- Multi-language support

### Troubleshooting
1. **Error 500**: Periksa file .htaccess dan permission
2. **Database connection error**: Verifikasi kredensial database
3. **API tidak berfungsi**: Pastikan mod_rewrite aktif

## Lisensi

Proyek ini dibuat untuk keperluan demonstrasi dan pembelajaran.

## Kontak

Untuk pertanyaan atau dukungan, silakan hubungi tim pengembang.

---

**Calista Express Website** - Solusi website jasa pengiriman yang modern dan fleksibel.

