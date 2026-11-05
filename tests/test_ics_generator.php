<?php
/**
 * Unit Test: Generator iCalendar (.ics) RFC 5545
 * Digunakan untuk memvalidasi engine pembuatan file iCalendar jadwal sholat.
 */

// Muat fungsi pendukung
require_once __DIR__ . '/../inc/functions.php';

$testPassed = 0;
$testFailed = 0;

function assertTrue($condition, $testName) {
    global $testPassed, $testFailed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $testPassed++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $testFailed++;
    }
}

echo "=== Menjalankan Pengujian Unit iCalendar Generator (RFC 5545) ===\n\n";

// 1. Uji keberadaan fungsi generator
echo "Test 1: Memeriksa keberadaan fungsi buildIcsCalendar...\n";
assertTrue(function_exists('buildIcsCalendar'), 'Fungsi buildIcsCalendar() harus terdefinisi');

if (!function_exists('buildIcsCalendar')) {
    echo "\n[RED PHASE] Fungsi buildIcsCalendar belum diimplementasikan.\n";
    exit(1);
}

// Persiapan data uji
$testCity = [
    'name' => 'PACITAN',
    'country' => 'INDONESIA',
    'lat' => -8.2000,
    'lon' => 111.1000,
    'tz' => 7.0,
    'elev' => 10.0
];

$year = 2026;
$month = 3; // Maret 2026

$ics = buildIcsCalendar($year, $month, $testCity, [
    'ihtiyat' => 1,
    'ihtiyat_dhuhur' => 4,
    'h_adj' => 0
]);

// 2. Uji struktur dasar VCALENDAR
echo "\nTest 2: Memeriksa struktur header & footer VCALENDAR...\n";
assertTrue(strpos($ics, "BEGIN:VCALENDAR\r\n") !== false, "Dimulai dengan BEGIN:VCALENDAR\\r\\n");
assertTrue(strpos($ics, "END:VCALENDAR\r\n") !== false, "Diakhiri dengan END:VCALENDAR\\r\\n");
assertTrue(strpos($ics, "VERSION:2.0\r\n") !== false, "Mempunyai VERSION:2.0");
assertTrue(strpos($ics, "PRODID:") !== false, "Mempunyai identifier PRODID");

// 3. Uji pemisah baris CRLF
echo "\nTest 3: Memeriksa format pemisah baris (CRLF wajib RFC 5545)...\n";
$hasBareLf = preg_match('/(?<!\r)\n/', $ics);
assertTrue($hasBareLf === 0, "Semua baris harus menggunakan \\r\\n (tanpa bare LF)");

// 4. Uji VTIMEZONE
echo "\nTest 4: Memeriksa definisi VTIMEZONE...\n";
assertTrue(strpos($ics, "BEGIN:VTIMEZONE\r\n") !== false, "Terdapat blok BEGIN:VTIMEZONE");
assertTrue(strpos($ics, "TZID:Asia/Jakarta\r\n") !== false, "TZID sesuai zona waktu GMT+7 (Asia/Jakarta)");
assertTrue(strpos($ics, "END:VTIMEZONE\r\n") !== false, "Terdapat penutup END:VTIMEZONE");

// 5. Uji Keberadaan Event Sholat
echo "\nTest 5: Memeriksa VEVENT Sholat...\n";
assertTrue(strpos($ics, "BEGIN:VEVENT\r\n") !== false, "Terdapat blok BEGIN:VEVENT");
assertTrue(strpos($ics, "SUMMARY:Sholat Subuh - PACITAN") !== false, "Terdapat event Sholat Subuh");
assertTrue(strpos($ics, "SUMMARY:Sholat Dhuhur - PACITAN") !== false, "Terdapat event Sholat Dhuhur");
assertTrue(strpos($ics, "SUMMARY:Sholat Maghrib - PACITAN") !== false, "Terdapat event Sholat Maghrib");

// 6. Uji Tanggal Hijriyah dan Pasaran di Deskripsi
echo "\nTest 6: Memeriksa data Hijriyah & Pasaran di DESCRIPTION...\n";
assertTrue(strpos($ics, "DESCRIPTION:") !== false, "Terdapat properti DESCRIPTION");
assertTrue(preg_match('/DESCRIPTION:.*Ramadhan.*H/i', $ics) === 1, "Deskripsi memuat bulan Hijriyah (Ramadhan)");
assertTrue(preg_match('/DESCRIPTION:.*(Legi|Pahing|Pon|Wage|Kliwon)/i', $ics) === 1, "Deskripsi memuat Pasaran Jawa");

// 7. Uji UID dan DTSTAMP
echo "\nTest 7: Memeriksa UID dan DTSTAMP pada VEVENT...\n";
assertTrue(strpos($ics, "UID:") !== false, "Setiap event memiliki UID unik");
assertTrue(strpos($ics, "DTSTAMP:") !== false, "Setiap event memiliki DTSTAMP");
assertTrue(strpos($ics, "DTSTART;TZID=Asia/Jakarta:") !== false, "DTSTART memiliki parameter TZID yang tepat");

// 8. Uji Line Folding (panjang baris maksimal 75 karakter)
echo "\nTest 8: Memeriksa kepatuhan Line Folding RFC 5545 (<= 75 oktet per baris)...\n";
$lines = explode("\r\n", $ics);
$maxLen = 0;
foreach ($lines as $line) {
    $len = strlen($line);
    if ($len > $maxLen) $maxLen = $len;
}
assertTrue($maxLen <= 75, "Tidak ada baris yang melebihi 75 karakter (panjang terpanjang: {$maxLen})");

// Ringkasan hasil
echo "\n=======================================================\n";
echo "Hasil Pengujian: {$testPassed} Lulus, {$testFailed} Gagal\n";
echo "=======================================================\n";

if ($testFailed > 0) {
    exit(1);
}
exit(0);
