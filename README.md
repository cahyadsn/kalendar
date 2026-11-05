# 🗓️ Kalender Tahunan & Jadwal Imsakiyah Falakiah

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%207.4-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Falak: Ephemeris Jean Meeus](https://img.shields.io/badge/Falak-Ephemeris%20Jean%20Meeus-emerald.svg)](#metode-hisab--algoritma-falak)
[![Print Ready](https://img.shields.io/badge/UI-Print%20Ready%20%26%20Responsive-blue.svg)](#fitur-utama)

Aplikasi web mandiri (*standalone*) berbasis PHP murni untuk mengenerate **Kalender Tahunan Masehi, Kalender Hijriyah, Weton / Pasaran Jawa, Penanggalan Tradisional Cina (Imlek, Shio & Siklus 60-Tahun Elemen)**, serta **Jadwal Waktu Sholat dan Imsakiyah Ramadhan** berakurasi tinggi. 

Perhitungan astronomis mengadopsi algoritma hisab falak **Ephemeris Jean Meeus** dan rujukan kaidah **Kitab Tsamarotul Fikar** (karya KH. Ibnu Zahid Abdo el-Moeid) yang diadaptasi dari lembar kerja hisab falakiyah madrasah/pesantren, serta algoritma penanggalan Lunisolar Cina mandiri berbasis lookup table bitwise astronomis 1900–2100.

---

## 📑 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Tampilan Antarmuka](#-tampilan-antarmuka-view-modes)
- [Struktur Direktori](#-struktur-direktori)
- [Kebutuhan Sistem](#-kebutuhan-sistem)
- [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
- [Parameter URL & Opsi Konfigurasi](#-parameter-url--opsi-konfigurasi)
- [Penggunaan REST API & Ekspor Data](#-penggunaan-rest-api--ekspor-data)
- [Metode Hisab & Algoritma Falak](#-metode-hisab--algoritma-falak)
- [Pengaturan Ihtiyat (Pengaman Waktu)](#-pengaturan-ihtiyat-pengaman-waktu)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

1. **Integrasi Empat Sistem Penanggalan Sekaligus**:
   - **Kalender Masehi (Gregorian)**: 12 Bulan lengkap dengan jumlah hari kabisat otomatis.
   - **Kalender Hijriyah Astronomis**: Konversi berbasis Julian Day Number siklus 30 tahun (kabisaah urfi) dengan opsi koreksi hisab/ru'yah (-2 s/d +2 hari).
   - **Siklus Pasaran Jawa (Weton Pancawara)**: Perhitungan presisi untuk 5 pasaran: *Legi, Pahing, Pon, Wage, Kliwon*.
   - **Penanggalan Tradisional Cina (Lunisolar Imlek & Shio)**: Hisab kalender Tionghoa mandiri rentang tahun 1900–2100 tanpa library eksternal, penentuan tanggal & bulan (lengkap dengan deteksi bulan kabisat *run yue*), 12 Shio hewan zodiak, dan kombinasi 5 elemen siklus 60-tahunan *Jiazi* (Kayu, Api, Tanah, Logam, Air).

2. **Jadwal Sholat Harian Lengkap & Akurat**:
   - Menghitung 8 waktu penting: **Imsak, Subuh, Terbit, Dhuha, Dhuhur, Ashar, Maghrib, dan Isya**.
   - Dilengkapi perhitungan waktu perata waktu (*Equation of Time* / $e$), deklinasi matahari ($\delta$), tinggi ufuk (*dip horizon* sesuai elevasi mdpl), serta koreksi refraksi dan semidiameter matahari.

3. **Perhitungan Arah Kiblat**:
   - Menghitung nilai Azimuth Kiblat dari Utara sejati searah jarum jam ($^\circ, ', ''$) dan sudut deviasi praktis Barat-ke-Utara ($^\circ\text{ B-U}$) berdasarkan koordinat Ka'bah ($21.4225^\circ\text{ LU}, 39.8262^\circ\text{ BT}$).

4. **Database Ratusan Kota & Markas di Indonesia**:
   - Dilengkapi file database `assets/data/cities.json` yang memuat ratusan kota/kabupaten di seluruh Indonesia beserta data koordinat lintang (*latitude*), bujur (*longitude*), ketinggian di atas permukaan laut (*elevation*), dan zona waktu (*GMT offset*).
   - Mendukung masukan koordinat kustom (*custom latitude/longitude*) secara manual.

5. **Deteksi Hari Libur & Hari Besar Islam Otomatis**:
   - Libur Nasional tetap (Tahun Baru Masehi, Hari Buruh, Hari Lahir Pancasila, HUT RI, Hari Raya Natal).
   - Hari Besar & Puasa Islam: 1 Muharram, Asyura (10 Muharram), Maulid Nabi (12 Rabi'ul Awal), Isra Mi'raj (27 Rajab), Nisfu Sya'ban, Awal Ramadhan, Nuzulul Qur'an (17 Ramadhan), Idul Fitri (1 & 2 Syawwal), Hari Arafah (9 Dzulhijjah), Idul Adha (10 Dzulhijjah), dan Hari Tasyrik (11, 12, 13 Dzulhijjah).

6. **Ekspor CSV, iCalendar (.ics) & RESTful JSON API**:
   - Sekali klik untuk mengunduh seluruh jadwal satu tahun atau satu bulan ke format file `.csv` (kompatibel penuh dengan Microsoft Excel / Google Sheets).
   - Ekspor kalender ke format **iCalendar (`.ics`) RFC 5545** siap impor ke **Google Calendar, Apple Calendar, dan Microsoft Outlook**, lengkap dengan pengenalan zona waktu (`VTIMEZONE` WIB/WITA/WIT) serta penanggalan Hijriyah & Pasaran Jawa pada deskripsi event.
   - Endpoint JSON API siap integrasi untuk aplikasi mobile, bot Telegram/WhatsApp, running text masjid, atau dashboard IoT.

7. **Desain Modern & Siap Cetak (Print-Friendly)**:
   - Tampilan bersih berbasis CSS Variables, modern card layout, dan tipografi rapi.
   - Dilengkapi *Print Stylesheet* (`@media print`): otomatis menyembunyikan panel kontrol dan tombol aksi saat dicetak via browser (Ctrl+P / Command+P) untuk menghasilkan cetakan kalender fisik di kertas A4 / Legal / F4.

8. **Widget Waktu Sholat Real-Time & Countdown**:
   - Jam digital presisi tinggi (`HH:mm:ss`) yang berdetik setiap detik sesuai zona waktu markas aktif (WIB/WITA/WIT).
   - Menampilkan badge status waktu sholat aktif serta penghitung mundur (*countdown timer*) otomatis menuju waktu sholat berikutnya.
   - Penanganan transisi tengah malam (*midnight rollover*) dan otomatis tersembunyi saat mencetak dokumen (`.no-print`).

---

## 🖥️ Tampilan Antarmuka (View Modes)

Aplikasi memiliki 3 mode tampilan utama yang dapat diakses melalui tab navigasi atau parameter `?view=`:

| Mode | Parameter | Deskripsi |
| :--- | :--- | :--- |
| **Kalender 12 Bulan** | `?view=calendar` | Grid kalender 12 bulan elegan menampilkan tanggal Masehi, Hijriyah, Pasaran Jawa, penanggalan Imlek & Shio, tanda hari libur nasional (merah), serta hari besar Islam (hijau). |
| **Jadwal Sholat Tahunan** | `?view=table` | Tabel jadwal sholat harian per bulan berisi waktu Imsak, Subuh, Terbit, Dhuha, Dhuhur, Ashar, Maghrib, Isya, dan catatan agenda hari libur. |
| **Imsakiyah Ramadhan** | `?view=ramadhan` | Format khusus lembar Jadwal Imsakiyah 30 hari penuh bulan Ramadhan dengan penyorotan waktu Imsak dan waktu Maghrib (buka puasa). |

---

## 📂 Struktur Direktori

```text
kalendar/
├── assets/
│   ├── css/
│   │   └── style.css            # Styling responsif, modern theme & aturan @media print
│   ├── js/
│   │   └── prayer-widget.js     # Real-time clock & dynamic countdown timer (Vanilla JS)
│   └── data/
│       └── cities.json          # Database koordinat kota (Lintang, Bujur, Elevasi, TZ)
├── inc/
│   └── functions.php            # Engine hisab falakiyah (Meeus, Weton, Lunisolar Cina & Shio)
├── templates/
│   ├── widget-prayer.php        # Template kartu widget real-time clock & countdown sholat
│   ├── view-calendar.php        # Template antarmuka kalender grid 12 bulan (Masehi, Hijriyah & Imlek)
│   ├── view-table.php           # Template antarmuka tabel jadwal waktu sholat
│   └── view-ramadhan.php        # Template antarmuka jadwal imsakiyah 30 hari Ramadhan
├── tests/
│   ├── test_chinese_calendar.php    # Unit test engine penanggalan Cina & Shio 60 tahun
│   ├── test_ics_generator.php       # Unit test generator iCalendar RFC 5545
│   ├── test_prayer_widget.php       # Unit test logika waktu sholat real-time widget
│   └── test_templates_rendering.php # Integration test rendering template & zero warnings
├── index.php                    # Controller, router tampilan & endpoint API
├── LICENSE                      # Lisensi open-source MIT
└── README.md                    # Dokumentasi lengkap proyek
```

---

## ⚙️ Kebutuhan Sistem

- **PHP**: Versi `7.4` atau yang lebih baru (`PHP 8.0`, `8.1`, `8.2`, `8.3`, `8.4+`).
- **Ekstensi PHP Standar**:
  - `calendar` (untuk fungsi `cal_days_in_month`)
  - `json` (untuk membaca `cities.json`)
  - `date` (zona waktu dan manipulasi DateTime)
- **Web Server**: Apache, Nginx, LiteSpeed, Caddy, atau **PHP Built-in Web Server**.
- **Tanpa Composer**: Zero-dependency, dapat langsung dijalankan tanpa perlu instalasi `vendor`.

---

## 🚀 Panduan Instalasi & Menjalankan

### Cara 1: Menggunakan PHP Built-in Server (Paling Cepat)

1. Buka terminal atau PowerShell di direktori proyek:
   ```bash
   cd d:/mts4/kalendar
   ```
2. Jalankan PHP Built-in Server:
   ```bash
   php -S localhost:8000
   ```
3. Buka peramban (browser) Anda dan akses:
   ```text
   http://localhost:8000
   ```

### Cara 2: Menggunakan XAMPP / Laragon / WampServer

1. Salin atau buat symlink folder `kalendar` ke dalam folder web root web server Anda:
   - XAMPP: `C:/xampp/htdocs/kalendar`
   - Laragon: `C:/laragon/www/kalendar`
2. Pastikan service Apache/Nginx telah berjalan.
3. Buka browser dan arahkan ke:
   ```text
   http://localhost/kalendar/
   ```

---

## 🎛️ Parameter URL & Opsi Konfigurasi

Anda dapat memfilter tampilan atau mengintegrasikan kalender via URL Query Parameters:

| Parameter | Tipe | Default | Contoh | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `city` | string | `PACITAN` | `?city=JAKARTA` | Kode ID kota sesuai daftar di `cities.json`. |
| `year` | integer | Tahun saat ini | `?year=2026` | Tahun Masehi yang ingin ditampilkan. |
| `month` | integer | `0` (Semua) | `?month=3` | Filter bulan spesifik (1–12), atau `0` untuk seluruh tahun. |
| `view` | string | `calendar` | `?view=table` | Mode tampilan: `calendar`, `table`, atau `ramadhan`. |
| `h_adjust` | integer | `0` | `?h_adjust=-1` | Koreksi hisab tanggal Hijriyah (-2 s/d +2 hari). |
| `ihtiyat` | integer | `1` | `?ihtiyat=2` | Menit pengaman waktu sholat umum (Subuh, Ashar, Maghrib, Isya). |
| `ihtiyat_dhuhur` | integer | `4` | `?ihtiyat_dhuhur=5` | Menit pengaman waktu Dhuhur setelah istiwa/transit. |
| `custom_lat` | float | - | `?custom_lat=-8.15` | Koordinat Lintang kustom (jika markas tidak ada di database). |
| `custom_lon` | float | - | `?custom_lon=111.08`| Koordinat Bujur kustom. |
| `custom_elev` | float | `0` | `?custom_elev=50` | Ketinggian markas dari permukaan laut dalam satuan meter (mdpl). |
| `custom_tz` | float | `7` | `?custom_tz=7` | Zona Waktu GMT kustom (7=WIB, 8=WITA, 9=WIT). |

---

## 📡 Penggunaan REST API & Ekspor Data

### 1. Unduh File CSV
Untuk mengunduh tabel jadwal dalam format `.csv`:
```http
GET /index.php?action=export_csv&city=SURABAYA&year=2026&month=0
```

### 2. Konsumsi RESTful JSON API
Untuk mengintegrasikan data hisab ke aplikasi pihak ketiga:
```http
GET /index.php?action=api&city=BANDUNG&year=2026&month=3
```
Contoh respon JSON:
```json
{
  "city": {
    "name": "KAB. BANDUNG",
    "lat": -7.025,
    "lon": 107.519722,
    "elev": 700,
    "tz": 7
  },
  "year": 2026,
  "month": 3,
  "qibla": {
    "azimuth_deg": 295.12,
    "azimuth_dms": "295° 7' 12\"",
    "deviation_bu": "25° 7' 12\" B-U"
  },
  "data": [
    {
      "date": "2026-03-01",
      "day_name": "Ahad",
      "pasaran": "Wage",
      "hijri": "11 Ramadhan 1447 H",
      "prayer_times": {
        "imsak": "04:28",
        "subuh": "04:38",
        "terbit": "05:51",
        "dhuha": "06:14",
        "dhuhur": "12:05",
        "ashar": "15:08",
        "maghrib": "18:13",
        "isya": "19:22"
      },
      "event": null
    }
  ]
}
```

---

### 3. Unduh File iCalendar (.ics)
Untuk menyinkronkan jadwal waktu sholat dan pengingat ibadah langsung ke kalender smartphone atau komputer (Google Calendar, Apple Calendar, Outlook):
```http
GET /index.php?action=export_ics&city=PACITAN&year=2026&month=3
```
- **Karakteristik Format:** Standar resmi iCalendar RFC 5545, pembatas baris CRLF, pembungkusan baris (*line folding*) $\le 75$ oktet, dan blok `VTIMEZONE` otomatis (`Asia/Jakarta`, `Asia/Makassar`, atau `Asia/Jayapura`).
- **Isi Event (`VEVENT`):**
  - Event sholat: *Subuh, Dhuhur, Ashar, Maghrib, Isya* serta waktu *Imsak, Terbit,* dan *Dhuha*.
  - `SUMMARY`: Nama sholat beserta nama kota markas.
  - `DESCRIPTION`: Rincian waktu ibadah, penanggalan Hijriyah, Pasaran Jawa (Weton), dan arah kiblat.
- **Panduan Singkat Impor:**
  - **Google Calendar (Web):** Buka Google Calendar $\rightarrow$ Ikon Pengaturan (*Settings*) $\rightarrow$ *Import & Export* $\rightarrow$ Pilih berkas `.ics` $\rightarrow$ Klik *Import*.
  - **Apple Calendar (iOS / macOS):** Buka atau ketuk berkas `.ics` yang telah diunduh, lalu pilih *Add to Calendar* / *Tambahkan Semua*.
  - **Microsoft Outlook:** Buka menu File $\rightarrow$ *Open & Export* $\rightarrow$ *Open Calendar (.ics)*.

---

## 📐 Metode Hisab & Algoritma Falak

Perhitungan dalam berkas [`inc/functions.php`](inc/functions.php) dirancang dengan ketelitian astronomis mengikuti tahapan baku ilmu falak modern:

1. **Julian Day Number (JDN)**:
   Dihitung untuk tanggal Masehi pada tengah hari (*local noon*):
   $$\text{JD} = \lfloor 365.25 \times (Y + 4716) \rfloor + \lfloor 30.6001 \times (M + 1) \rfloor + D + \frac{12 - \text{TZ}}{24} + B - 1524.5$$

2. **Perhitungan Pasaran Jawa (Pancawara)**:
   Ditentukan melalui sisa pembagian JDN terhadap bilangan 5:
   $$\text{Index Pasaran} = \lfloor \text{JD} \rfloor \pmod 5$$
   *(0 = Pahing, 1 = Pon, 2 = Wage, 3 = Kliwon, 4 = Legi)*.

3. **Koordinat Matahari Ephemeris (Jean Meeus)**:
   - Bujur Rata-rata Matahari ($L_0$) & Anomali Rata-rata Matahari ($M$).
   - Koreksi Nutasi & Kemiringan Ekliptika ($\varepsilon$).
   - Persamaan Pusat (*Equation of Center*) untuk mendapatkan Bujur Sejati ($\lambda$).
   - **Deklinasi Matahari** ($\delta$):
     $$\delta = \arcsin(\sin\lambda \times \sin\varepsilon)$$
   - **Perata Waktu (*Equation of Time* / $e$)**:
     Digunakan untuk mengoreksi posisi tengah hari hakiki (*Transit / Zawal*).

4. **Ketinggian Matahari (*Altitude* / $h$) untuk Waktu Sholat**:
   - **Shubuh**: $h = -20^\circ$ (Standar Kementerian Agama RI).
   - **Terbit (*Sunrise*)**: $h = -(\text{Semidiameter} + \text{Refraksi} + \text{Dip Horizon})$.
   - **Dhuha**: $h = +4.5^\circ$.
   - **Dhuhur**: Saat Matahari melintasi meridian langit (*Transit* + Ihtiyat).
   - **Ashar**: Ditentukan saat panjang bayangan benda = panjang benda + bayangan waktu tengah hari:
     $$h_{\text{Ashar}} = \text{arccot}(1 + \tan|\phi - \delta|)$$
   - **Maghrib**: $h = -(\text{Semidiameter} + \text{Refraksi} + \text{Dip Horizon})$.
   - **Isya**: $h = -18^\circ$ (Standar Kementerian Agama RI).

5. **Sudut Waktu (*Hour Angle* / $t$)**:
   $$\cos t = \frac{\sin h - \sin\phi \sin\delta}{\cos\phi \cos\delta}$$

6. **Penanggalan Tradisional Lunisolar Cina (Imlek & Siklus Jiazi)**:
   - **Lookup Table Bitwise 1900–2100**: Memetakan struktur 12 bulan reguler (29/30 hari) dan keberadaan bulan kabisat (*run yue* / *leap month*) dalam format bitmask 16-20 bit per tahun secara mandiri tanpa dependensi eksternal.
   - **Siklus 60-Tahunan (*Sexagenary Jiazi*)**: Menghitung kombinasi 10 Batang Surgawi (*Heavenly Stems*) dan 12 Cabang Bumi (*Earthly Branches*) untuk menentukan Shio hewan (Tikus s/d Babi) dan 5 Elemen (Kayu, Api, Tanah, Logam, Air) secara deterministik.
   - **Integrasi Antarmuka**: Seluruh tanggal kalender grid dilengkapi penanda angka Imlek, badge awal bulan (*Ce It*), serta tooltip interaktif yang memuat informasi lengkap tanggal Imlek, Shio, dan elemen tahun.

---

## ⏱️ Pengaturan Ihtiyat (Pengaman Waktu)

Dalam tradisi hisab falakiyah, **ihtiyat** adalah penambahan waktu beberapa menit sebagai langkah kehati-hatian (*ihtiyath*) guna memastikan waktu sholat telah benar-benar masuk di seluruh penjuru wilayah markas:
- **Waktu Imsak**: Ditetapkan 10 menit sebelum waktu Shubuh.
- **Waktu Shubuh, Dhuha, Ashar, Maghrib, Isya**: Ditambah `+1 menit` (pembulatan ke atas / *ceil*).
- **Waktu Dhuhur**: Ditambah `+4 menit` dari saat *istiwa/transit* agar matahari dipastikan telah tergelincir (*zawal*) ke arah barat.
- **Waktu Terbit**: Dikurangi `1 menit` (pembulatan ke bawah / *floor*) untuk memastikan matahari telah benar-benar muncul di ufuk timur.

---

## 📜 Lisensi

Aplikasi ini dilisensikan di bawah [MIT License](LICENSE). Anda bebas menggunakan, memodifikasi, dan mendistribusikan perangkat lunak ini untuk keperluan personal, madrasah, masjid, maupun komersial dengan tetap menyertakan atribusi hak cipta.

**Copyright (c) 2026 Cahya DSN**
