# Interview Internship - Laravel App

## Ringkasan Sistem

Untuk mendemonstrasikan bagaimana sistem ini menangani kompleksitas data di balik layar, mari kita ambil contoh jenis emisi **Land Clearing**. 

Dalam kalkulasi Land Clearing, terdapat tiga variabel koefisien utama, di mana setiap koefisien memiliki aturan dependensi (*coefficient_dependency*) yang berbeda-beda terhadap suatu kategori:
* **KH (Konstanta Hutan)** bergantung pada 2 kategori: `[jenis_hutan, tipe_geografi]`
* **BIO (Rata-rata Biomass)** bergantung pada 2 kategori: `[tipe_geografi, tipe_plantation]`
* **EF (Emission Factor)** bergantung pada 1 kategori: `[tipe_plantation]`

Perbedaan dependensi inilah yang membuat data menjadi sangat beragam. Di dalam sistem, kita memecah dimensi data ini menjadi tiga kelompok kategori utama:
1. `id=1`, `code="jenis_hutan"` (Contoh nilai: Hutan Primer, Hutan Sekunder)
2. `id=2`, `code="tipe_geografi"` (Contoh nilai: Dataran Rendah, Pegunungan)
3. `id=3`, `code="tipe_plantation"` (Contoh nilai: Sawit, Karet)

#### Mekanisme Penyelesaian (The Lookup Engine)

Pendekatan yang saya ajukan adalah menempatkan kendali penuh pada master data. Admin akan menginput nilai koefisien yang spesifik untuk setiap kombinasi kategori tersebut ke dalam sistem.

Sebagai contoh, saat *user* di *frontend* memilih lahan **Hutan Primer** di **Dataran Rendah**, sistem akan melakukan *exact-match lookup* ke tabel nilai koefisien. Sistem akan mencari baris data spesifik di mana KH terikat dengan kombinasi `[Hutan Primer + Dataran Rendah]`. Anggaplah admin sudah menginput bahwa nilai untuk kombinasi tersebut adalah `0.85`. Maka, sistem secara otomatis menetapkan nilai KH = 0.85 untuk sesi perhitungan tersebut.

Setelah sistem berhasil melakukan *lookup* dan meresolusi semua nilai untuk variabel KH, BIO, dan EF berdasarkan kombinasi yang dipilih *user*, barulah kalkulasi akhir dieksekusi secara dinamis menggunakan rumus:

**Emisi** = luas_lahan &times; KH &times; BIO &times; EF

Dengan cara ini, sebanyak apa pun variasi kombinasi yang ada di lapangan, sistem akan selalu memberikan hasil perhitungan yang akurat tanpa *user* perlu memusingkan kerumitan pemetaan nilai di belakangnya.

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm

## How to Run

**1. Clone the repository**
```bash
git clone https://github.com/athallaabhi/InterviewInternship.git
cd InterviewInternship
```

**2. Install PHP dependencies**
```bash
composer install
```

**3. Install Node dependencies**
```bash
npm install
```

**4. Set up environment**
```bash
cp .env.example .env
php artisan key:generate
```

**5. Set up the database**
```bash
php artisan migrate
```

**6. Build frontend assets**
```bash
npm run build
```

**7. Start the development server**
```bash
php artisan serve
```

The app will be available at `http://localhost:8000`.

> For local development you can run `npm run dev` alongside `php artisan serve` to enable hot-reloading.
