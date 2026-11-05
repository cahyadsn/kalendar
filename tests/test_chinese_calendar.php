<?php
/**
 * Test Suite: Validasi Engine Penanggalan Cina (Lunisolar Imlek, Shio & Elemen)
 */

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

echo "=== Menjalankan Pengujian Unit Engine Penanggalan Cina (Lunisolar Imlek) ===\n\n";

require_once __DIR__ . '/../inc/functions.php';

// Test 1: Memeriksa keberadaan fungsi
echo "Test 1: Memeriksa keberadaan fungsi penanggalan Cina di inc/functions.php...\n";
assertTrue(function_exists('getChineseLunisolarDate'), 'Fungsi getChineseLunisolarDate() harus terdefinisi');
assertTrue(function_exists('getChineseZodiacYear'), 'Fungsi getChineseZodiacYear() harus terdefinisi');

if (!function_exists('getChineseLunisolarDate') || !function_exists('getChineseZodiacYear')) {
    echo "\n[INFO] Fungsi belum diimplementasikan. Menghentikan pengujian untuk TDD Red Stage.\n";
    echo "\n=======================================================\n";
    echo "Hasil Pengujian: {$testPassed} Lulus, {$testFailed} Gagal\n";
    echo "=======================================================\n";
    exit(1);
}

// Test 2: Memeriksa Siklus Shio dan Elemen 60 Tahunan (Sexagenary Jiazi)
echo "\nTest 2: Memeriksa Shio dan Elemen Zodiak pada tahun-tahun acuan...\n";
$z2024 = getChineseZodiacYear(2575); // Gregorian 2024
assertTrue($z2024['shio'] === 'Naga', 'Tahun 2575 (2024) adalah Shio Naga (Dragon)');
assertTrue($z2024['elemen'] === 'Kayu', 'Tahun 2575 (2024) berelemen Kayu (Wood)');
assertTrue(strpos($z2024['nama_tahun'], 'Naga Kayu') !== false, 'Nama tahun memuat Naga Kayu');

$z2025 = getChineseZodiacYear(2576); // Gregorian 2025
assertTrue($z2025['shio'] === 'Ular', 'Tahun 2576 (2025) adalah Shio Ular (Snake)');
assertTrue($z2025['elemen'] === 'Kayu', 'Tahun 2576 (2025) berelemen Kayu (Wood)');
assertTrue(strpos($z2025['nama_tahun'], 'Ular Kayu') !== false, 'Nama tahun memuat Ular Kayu');

$z2026 = getChineseZodiacYear(2577); // Gregorian 2026
assertTrue($z2026['shio'] === 'Kuda', 'Tahun 2577 (2026) adalah Shio Kuda (Horse)');
assertTrue($z2026['elemen'] === 'Api', 'Tahun 2577 (2026) berelemen Api (Fire)');
assertTrue(strpos($z2026['nama_tahun'], 'Kuda Api') !== false, 'Nama tahun memuat Kuda Api');

// Test 3: Konversi Tanggal Tahun Baru Imlek 2024 (10 Februari 2024)
echo "\nTest 3: Konversi Tahun Baru Imlek 2024 (10 Februari 2024)...\n";
$cny2024 = getChineseLunisolarDate(2024, 2, 10);
assertTrue($cny2024 !== null, 'Hasil konversi 10 Feb 2024 tidak boleh null');
assertTrue($cny2024['day'] === 1, 'Tanggal Imlek harus 1 (Ce It)');
assertTrue($cny2024['month'] === 1, 'Bulan Imlek harus 1 (Cia Gwe)');
assertTrue($cny2024['year'] === 2575, 'Tahun Imlek harus 2575');
assertTrue($cny2024['is_leap'] === false, 'Bukan bulan kabisat');
assertTrue($cny2024['shio'] === 'Naga', 'Shio 10 Feb 2024 adalah Naga');

// Test 4: Konversi Sehari Sebelum Tahun Baru Imlek 2024 (9 Februari 2024)
echo "\nTest 4: Konversi Malam Tahun Baru Imlek 2024 (9 Februari 2024)...\n";
$eve2024 = getChineseLunisolarDate(2024, 2, 9);
assertTrue($eve2024['year'] === 2574, 'Tahun Imlek masih 2574 (sebelum pergantian tahun)');
assertTrue($eve2024['month'] === 12, 'Bulan Imlek adalah 12 (Cap Ji Gwe)');
assertTrue($eve2024['shio'] === 'Kelinci', 'Shio masih Kelinci');

// Test 5: Konversi Tahun Baru Imlek 2025 (29 Januari 2025)
echo "\nTest 5: Konversi Tahun Baru Imlek 2025 (29 Januari 2025)...\n";
$cny2025 = getChineseLunisolarDate(2025, 1, 29);
assertTrue($cny2025['day'] === 1, 'Tanggal Imlek 29 Jan 2025 adalah 1');
assertTrue($cny2025['month'] === 1, 'Bulan Imlek 29 Jan 2025 adalah 1 (Cia Gwe)');
assertTrue($cny2025['year'] === 2576, 'Tahun Imlek 2576');
assertTrue($cny2025['shio'] === 'Ular', 'Shio 29 Jan 2025 adalah Ular');

// Test 6: Deteksi Bulan Kabisat (Leap Month) pada tahun 2025 (Bulan 6 Kabisat / Run Liu Yue)
echo "\nTest 6: Memeriksa deteksi bulan kabisat (leap month) pada 2025...\n";
// Pada penanggalan Cina 2025, terdapat kabisat bulan ke-6 (sekitar Agustus 2025)
$leapDay2025 = getChineseLunisolarDate(2025, 8, 1);
assertTrue(isset($leapDay2025['is_leap']), 'Field is_leap harus tersedia');
assertTrue($leapDay2025['month'] === 6 && $leapDay2025['is_leap'] === true, 'Tanggal 1 Agustus 2025 berada pada Bulan 6 Kabisat (Leap Month 6)');

// Test 7: Konversi Tahun Baru Imlek 2026 (17 Februari 2026)
echo "\nTest 7: Konversi Tahun Baru Imlek 2026 (17 Februari 2026)...\n";
$cny2026 = getChineseLunisolarDate(2026, 2, 17);
assertTrue($cny2026['day'] === 1, 'Tanggal Imlek 17 Feb 2026 adalah 1 (Ce It)');
assertTrue($cny2026['month'] === 1, 'Bulan Imlek 17 Feb 2026 adalah 1 (Cia Gwe)');
assertTrue($cny2026['year'] === 2577, 'Tahun Imlek 2577');
assertTrue($cny2026['shio'] === 'Kuda', 'Shio adalah Kuda');
assertTrue($cny2026['elemen'] === 'Api', 'Elemen adalah Api');

// Test 8: Konversi Cap Go Meh 2026 (15 Cia Gwe = 3 Maret 2026)
echo "\nTest 8: Konversi Cap Go Meh 2026 (3 Maret 2026)...\n";
$capGoMeh2026 = getChineseLunisolarDate(2026, 3, 3);
assertTrue($capGoMeh2026['day'] === 15, 'Tanggal Imlek harus 15 (Cap Go Meh)');
assertTrue($capGoMeh2026['month'] === 1, 'Bulan Imlek harus 1');

// Test 9: Kelengkapan Struktur Data Hasil Konversi
echo "\nTest 9: Memeriksa integritas field array hasil konversi...\n";
$fields = ['year', 'month', 'day', 'month_name', 'day_name', 'is_leap', 'shio', 'elemen', 'nama_tahun', 'formatted'];
$sample = getChineseLunisolarDate(2026, 5, 20);
foreach ($fields as $f) {
    assertTrue(isset($sample[$f]), "Field '{$f}' harus ada dalam array hasil");
}

echo "\n=======================================================\n";
echo "Hasil Pengujian: {$testPassed} Lulus, {$testFailed} Gagal\n";
echo "=======================================================\n";

if ($testFailed > 0) {
    exit(1);
}
exit(0);
