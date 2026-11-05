<?php
/**
 * Unit Test: Logika Real-Time Prayer Widget & Countdown
 */

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

echo "=== Menjalankan Pengujian Unit Logika Real-Time Prayer Widget ===\n\n";

echo "Test 1: Memeriksa keberadaan fungsi getPrayerStatusInfo...\n";
assertTrue(function_exists('getPrayerStatusInfo'), 'Fungsi getPrayerStatusInfo() harus terdefinisi');

if (!function_exists('getPrayerStatusInfo')) {
    echo "\n[RED PHASE] Fungsi getPrayerStatusInfo belum diimplementasikan.\n";
    exit(1);
}

// Data jadwal sholat uji
$todaySchedule = [
    'imsak'   => '04:15',
    'subuh'   => '04:25',
    'terbit'  => '05:40',
    'dhuha'   => '06:05',
    'dhuhur'  => '11:45',
    'ashar'   => '14:55',
    'maghrib' => '17:50',
    'isya'    => '19:00'
];

$tomorrowSchedule = [
    'imsak' => '04:14',
    'subuh' => '04:24'
];

// 2. Uji status waktu siang (Dhuhur aktif, menunggu Ashar)
echo "\nTest 2: Evaluasi waktu siang (12:15:00 - Waktu Dhuhur aktif)...\n";
$statusNoon = getPrayerStatusInfo('12:15:00', $todaySchedule, $tomorrowSchedule);
assertTrue($statusNoon['current_key'] === 'dhuhur', 'Sholat aktif saat ini adalah Dhuhur');
assertTrue($statusNoon['next_key'] === 'ashar', 'Sholat berikutnya adalah Ashar');
assertTrue($statusNoon['countdown_seconds'] === 9600, 'Sisa waktu countdown 9600 detik (2 jam 40 menit)');
assertTrue($statusNoon['countdown_formatted'] === '02:40:00', 'Format countdown adalah 02:40:00');

// 3. Uji status waktu Imsak (04:20:00 - Menunggu Subuh)
echo "\nTest 3: Evaluasi waktu Imsak (04:20:00 - Menunggu Subuh)...\n";
$statusImsak = getPrayerStatusInfo('04:20:00', $todaySchedule, $tomorrowSchedule);
assertTrue($statusImsak['current_key'] === 'imsak', 'Periode aktif saat ini adalah Imsak');
assertTrue($statusImsak['next_key'] === 'subuh', 'Sholat berikutnya adalah Subuh');
assertTrue($statusImsak['countdown_seconds'] === 300, 'Sisa waktu countdown 300 detik (5 menit)');
assertTrue($statusImsak['countdown_formatted'] === '00:05:00', 'Format countdown adalah 00:05:00');

// 4. Uji pergantian tengah malam / malam hari (21:30:00 - Menuju Imsak esok hari)
echo "\nTest 4: Evaluasi pergantian malam hari (21:30:00 - Menuju Imsak esok hari)...\n";
$statusNight = getPrayerStatusInfo('21:30:00', $todaySchedule, $tomorrowSchedule);
assertTrue($statusNight['current_key'] === 'isya', 'Sholat aktif saat ini adalah Isya');
assertTrue($statusNight['next_key'] === 'imsak', 'Sholat berikutnya adalah Imsak esok hari');
// 21:30 ke 24:00 = 150 menit = 9000s; 00:00 ke 04:14 = 254 menit = 15240s. Total = 24240s
assertTrue($statusNight['countdown_seconds'] === 24240, 'Sisa waktu countdown 24240 detik');
assertTrue($statusNight['countdown_formatted'] === '06:44:00', 'Format countdown adalah 06:44:00');

// 5. Uji dini hari sebelum Imsak (03:30:00 - Menuju Imsak hari ini)
echo "\nTest 5: Evaluasi dini hari (03:30:00 - Menuju Imsak hari ini)...\n";
$statusDawn = getPrayerStatusInfo('03:30:00', $todaySchedule, $tomorrowSchedule);
assertTrue($statusDawn['next_key'] === 'imsak', 'Sholat berikutnya adalah Imsak hari ini');
assertTrue($statusDawn['countdown_seconds'] === 2700, 'Sisa waktu countdown 2700 detik (45 menit)');
assertTrue($statusDawn['countdown_formatted'] === '00:45:00', 'Format countdown adalah 00:45:00');

echo "\n=======================================================\n";
echo "Hasil Pengujian: {$testPassed} Lulus, {$testFailed} Gagal\n";
echo "=======================================================\n";

if ($testFailed > 0) {
    exit(1);
}
exit(0);
