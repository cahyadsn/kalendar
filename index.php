<?php
/**
 * =====================================================================================
 * KALENDER TAHUNAN & JADWAL IMSAKIYAH / SHOLAT HISAB FALAKIAH
 * Berdasarkan Algoritma File: sumber/jadwal_imsakiyah_romadlon.xlsx
 * (Hisab Falakiah Ephemeris / Jean Meeus / Kitab Tsamarotul Fikar - Ibnu Zahid Abdo el-Moeid)
 * =====================================================================================
 */

date_default_timezone_set('Asia/Jakarta');

// Muat fungsi perhitungan falakiah & kalender
$incFile = __DIR__ . '/inc/functions.php';
if (!file_exists($incFile) && file_exists(__DIR__ . '/../inc/functions.php')) {
    $incFile = __DIR__ . '/../inc/functions.php';
}
require_once $incFile;

// --- 1. DATA KOTA & MARKAS ---
$citiesFile = __DIR__ . '/assets/data/cities.json';
if (!file_exists($citiesFile) && file_exists(__DIR__ . '/../assets/data/cities.json')) {
    $citiesFile = __DIR__ . '/../assets/data/cities.json';
} elseif (!file_exists($citiesFile) && file_exists(__DIR__ . '/sumber/cities.json')) {
    $citiesFile = __DIR__ . '/sumber/cities.json';
} elseif (!file_exists($citiesFile) && file_exists(__DIR__ . '/cities.json')) {
    $citiesFile = __DIR__ . '/cities.json';
}

$allCities = [];
$indonesiaCities = [];

if (file_exists($citiesFile)) {
    $jsonContent = json_decode(file_get_contents($citiesFile), true);
    if ($jsonContent) {
        $indonesiaCities = $jsonContent['indonesia'] ?? [];
        $allCities = $jsonContent['all'] ?? [];
    }
}

// Fallback jika file json tidak ditemukan
if (empty($indonesiaCities)) {
    $indonesiaCities = [
        ['name' => 'PACITAN', 'country' => 'INDONESIA', 'lat' => -8.2000, 'lon' => 111.1000, 'tz' => 7.0, 'elev' => 10.0],
        ['name' => 'JAKARTA', 'country' => 'INDONESIA', 'lat' => -6.1754, 'lon' => 106.8272, 'tz' => 7.0, 'elev' => 10.0],
        ['name' => 'SURABAYA', 'country' => 'INDONESIA', 'lat' => -7.2575, 'lon' => 112.7521, 'tz' => 7.0, 'elev' => 5.0],
        ['name' => 'BANDUNG', 'country' => 'INDONESIA', 'lat' => -6.9175, 'lon' => 107.6191, 'tz' => 7.0, 'elev' => 768.0],
        ['name' => 'SEMARANG', 'country' => 'INDONESIA', 'lat' => -6.9667, 'lon' => 110.4167, 'tz' => 7.0, 'elev' => 10.0],
        ['name' => 'YOGYAKARTA', 'country' => 'INDONESIA', 'lat' => -7.7956, 'lon' => 110.3695, 'tz' => 7.0, 'elev' => 113.0],
        ['name' => 'MALANG', 'country' => 'INDONESIA', 'lat' => -7.9797, 'lon' => 112.6304, 'tz' => 7.0, 'elev' => 444.0],
        ['name' => 'SURAKARTA', 'country' => 'INDONESIA', 'lat' => -7.5667, 'lon' => 110.8167, 'tz' => 7.0, 'elev' => 93.0],
        ['name' => 'MEDAN', 'country' => 'INDONESIA', 'lat' => 3.5952, 'lon' => 98.6722, 'tz' => 7.0, 'elev' => 26.0],
        ['name' => 'MAKASSAR', 'country' => 'INDONESIA', 'lat' => -5.1477, 'lon' => 119.4327, 'tz' => 8.0, 'elev' => 12.0],
        ['name' => 'BANDA ACEH', 'country' => 'INDONESIA', 'lat' => 5.5483, 'lon' => 95.3238, 'tz' => 7.0, 'elev' => 10.0],
        ['name' => 'DENPASAR', 'country' => 'INDONESIA', 'lat' => -8.6705, 'lon' => 115.2126, 'tz' => 8.0, 'elev' => 15.0],
        ['name' => 'BANJARMASIN', 'country' => 'INDONESIA', 'lat' => -3.3167, 'lon' => 114.5833, 'tz' => 8.0, 'elev' => 5.0],
        ['name' => 'PADANG', 'country' => 'INDONESIA', 'lat' => -0.9492, 'lon' => 100.3543, 'tz' => 7.0, 'elev' => 10.0],
        ['name' => 'PALEMBANG', 'country' => 'INDONESIA', 'lat' => -2.9761, 'lon' => 104.7754, 'tz' => 7.0, 'elev' => 15.0],
        ['name' => 'JAYAPURA', 'country' => 'INDONESIA', 'lat' => -2.5337, 'lon' => 140.7181, 'tz' => 9.0, 'elev' => 28.0]
    ];
}

// Urutkan daftar kota Indonesia berdasarkan nama
usort($indonesiaCities, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// --- 2. PARAMETER REQUEST ---
$currentYear = (int)date('Y');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;
if ($selectedYear < 1900 || $selectedYear > 2100) $selectedYear = $currentYear;

$selectedCityName = isset($_GET['city']) ? trim($_GET['city']) : 'PACITAN';
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : 0; // 0 = setahun penuh
$viewMode = isset($_GET['view']) ? trim($_GET['view']) : 'calendar'; // 'calendar', 'table', 'ramadhan'
$hijriAdjustment = isset($_GET['h_adj']) ? (int)$_GET['h_adj'] : 0; // -2 s/d +2 hari

// Pengaturan Ihtiyat (koreksi waktu hisab)
$ihtiyat = isset($_GET['ihtiyat']) ? (int)$_GET['ihtiyat'] : 1; // menit
$ihtiyatDhuhur = isset($_GET['ihtiyat_dhuhur']) ? (int)$_GET['ihtiyat_dhuhur'] : 4; // menit

// Cari data kota terpilih
$activeCity = null;
foreach ($indonesiaCities as $c) {
    if (strcasecmp($c['name'], $selectedCityName) === 0) {
        $activeCity = $c;
        break;
    }
}
if (!$activeCity) {
    // Coba di all cities
    foreach ($allCities as $c) {
        if (strcasecmp($c['name'], $selectedCityName) === 0) {
            $activeCity = $c;
            break;
        }
    }
}
if (!$activeCity) {
    // Default Pacitan (sesuai template Excel)
    $activeCity = [
        'name' => 'PACITAN',
        'country' => 'INDONESIA',
        'lat' => -8.2000,
        'lon' => 111.1000,
        'tz' => 7.0,
        'elev' => 10.0
    ];
}

// Custom koordinat jika diisi manual
if (isset($_GET['custom_lat']) && $_GET['custom_lat'] !== '') {
    $activeCity['lat'] = (float)$_GET['custom_lat'];
}
if (isset($_GET['custom_lon']) && $_GET['custom_lon'] !== '') {
    $activeCity['lon'] = (float)$_GET['custom_lon'];
}
if (isset($_GET['custom_tz']) && $_GET['custom_tz'] !== '') {
    $activeCity['tz'] = (float)$_GET['custom_tz'];
}
if (isset($_GET['custom_elev']) && $_GET['custom_elev'] !== '') {
    $activeCity['elev'] = (float)$_GET['custom_elev'];
}

// --- 3. PERHITUNGAN HARI LIBUR & ARAH KIBLAT (FUNGSI DI INC/FUNCTIONS.PHP) ---
$holidays = getHolidays($selectedYear, $hijriAdjustment);
$qibla = getQiblaDirection($activeCity['lat'], $activeCity['lon']);

// --- 4. PERHITUNGAN JADWAL SHOLAT HARI INI & BESOK (UNTUK WIDGET REAL-TIME) ---
$todayY = (int)date('Y');
$todayM = (int)date('n');
$todayD = (int)date('j');
$todayPrayers = calculatePrayerTimes($todayY, $todayM, $todayD, $activeCity['lat'], $activeCity['lon'], $activeCity['tz'], $activeCity['elev'], [
    'ihtiyat' => $ihtiyat,
    'ihtiyat_dhuhur' => $ihtiyatDhuhur
]);

$tomorrowDt = new DateTime('+1 day');
$tomY = (int)$tomorrowDt->format('Y');
$tomM = (int)$tomorrowDt->format('n');
$tomD = (int)$tomorrowDt->format('j');
$tomorrowPrayers = calculatePrayerTimes($tomY, $tomM, $tomD, $activeCity['lat'], $activeCity['lon'], $activeCity['tz'], $activeCity['elev'], [
    'ihtiyat' => $ihtiyat,
    'ihtiyat_dhuhur' => $ihtiyatDhuhur
]);

$initialPrayerStatus = getPrayerStatusInfo(date('H:i:s'), $todayPrayers, $tomorrowPrayers);

// --- 5. EKSPOR DATA JIKA DIMINTA (CSV / JSON / ICS) ---
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'export_ics') {
        $icsContent = buildIcsCalendar($selectedYear, $selectedMonth, $activeCity, [
            'ihtiyat' => $ihtiyat,
            'ihtiyat_dhuhur' => $ihtiyatDhuhur,
            'h_adj' => $hijriAdjustment
        ]);
        $suffix = ($selectedMonth > 0) ? '_' . sprintf('%02d', $selectedMonth) : '';
        $cleanCity = preg_replace('/[^A-Za-z0-9_]/', '_', strtolower($selectedCityName));
        $filename = 'jadwal_sholat_' . $cleanCity . '_' . $selectedYear . $suffix . '.ics';

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($icsContent));
        header('Connection: close');
        echo $icsContent;
        exit;
    }
    if ($_GET['action'] === 'export_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=kalender_jadwal_sholat_' . $selectedCityName . '_' . $selectedYear . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Tanggal Masehi', 'Tanggal Hijriyah', 'Hari & Pasaran', 'Imsak', 'Subuh', 'Terbit', 'Dhuha', 'Dhuhur', 'Ashar', 'Maghrib', 'Isya', 'Catatan / Hari Libur']);

        $mStart = ($selectedMonth > 0) ? $selectedMonth : 1;
        $mEnd = ($selectedMonth > 0) ? $selectedMonth : 12;

        for ($m = $mStart; $m <= $mEnd; $m++) {
            $daysInM = cal_days_in_month(CAL_GREGORIAN, $m, $selectedYear);
            for ($d = 1; $d <= $daysInM; $d++) {
                $dtStr = sprintf('%04d-%02d-%02d', $selectedYear, $m, $d);
                $hp = getHariPasaran($selectedYear, $m, $d, $activeCity['tz']);
                $hijri = getHijriDate($selectedYear, $m, $d, $hijriAdjustment);
                $sholat = calculatePrayerTimes($selectedYear, $m, $d, $activeCity['lat'], $activeCity['lon'], $activeCity['tz'], $activeCity['elev'], [
                    'ihtiyat' => $ihtiyat,
                    'ihtiyat_dhuhur' => $ihtiyatDhuhur
                ]);
                $note = isset($holidays[$dtStr]) ? $holidays[$dtStr]['name'] : '';

                fputcsv($out, [
                    $dtStr,
                    $hijri['formatted'],
                    $hp['lengkap'],
                    $sholat['imsak'],
                    $sholat['subuh'],
                    $sholat['terbit'],
                    $sholat['dhuha'],
                    $sholat['dhuhur'],
                    $sholat['ashar'],
                    $sholat['maghrib'],
                    $sholat['isya'],
                    $note
                ]);
            }
        }
        fclose($out);
        exit;
    }

    if ($_GET['action'] === 'api') {
        header('Content-Type: application/json; charset=utf-8');
        $outputData = [
            'year' => $selectedYear,
            'city' => $activeCity,
            'qibla' => $qibla,
            'schedule' => []
        ];
        $mStart = ($selectedMonth > 0) ? $selectedMonth : 1;
        $mEnd = ($selectedMonth > 0) ? $selectedMonth : 12;

        for ($m = $mStart; $m <= $mEnd; $m++) {
            $daysInM = cal_days_in_month(CAL_GREGORIAN, $m, $selectedYear);
            for ($d = 1; $d <= $daysInM; $d++) {
                $dtStr = sprintf('%04d-%02d-%02d', $selectedYear, $m, $d);
                $hp = getHariPasaran($selectedYear, $m, $d, $activeCity['tz']);
                $hijri = getHijriDate($selectedYear, $m, $d, $hijriAdjustment);
                $sholat = calculatePrayerTimes($selectedYear, $m, $d, $activeCity['lat'], $activeCity['lon'], $activeCity['tz'], $activeCity['elev']);
                $note = $holidays[$dtStr]['name'] ?? null;

                $outputData['schedule'][] = [
                    'date_masehi' => $dtStr,
                    'date_hijri' => $hijri,
                    'hari' => $hp['hari'],
                    'pasaran' => $hp['pasaran'],
                    'sholat' => $sholat,
                    'event' => $note
                ];
            }
        }
        echo json_encode($outputData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Nama-nama bulan Masehi
$namaBulanMasehi = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$namaBulanPendek = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Tahunan & Jadwal Imsakiyah <?= htmlencode($selectedYear ?? '') ?> M - <?= htmlencode($activeCity['name'] ?? '') ?></title>
    <link rel="stylesheet" href="<?= file_exists(__DIR__ . '/assets/css/style.css') ? 'assets/css/style.css' : '../assets/css/style.css' ?>">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h1>Kalender Tahunan & Jadwal Imsakiyah <?= htmlencode($selectedYear ?? '') ?> M</h1>
                    <p>Hisab Astronomis Falakiyah (Ephemeris / Jean Meeus / Kitab Tsamarotul Fikar)</p>
                </div>
                <div class="no-print" style="display: flex; gap: 8px;">
                    <button onclick="window.print()" class="btn btn-secondary" title="Cetak Kalender">
                        🖨️ Cetak
                    </button>
                    <a href="?<?= http_build_query(array_merge($_GET, ['action' => 'export_ics'])) ?>" class="btn btn-primary" title="Ekspor ke iCalendar (.ics) untuk Google Calendar / Apple Calendar">📅 Unduh .ICS</a> <a href="?<?= http_build_query(array_merge($_GET, ['action' => 'export_csv'])) ?>" class="btn btn-success" title="Ekspor ke Excel / CSV">
                        📥 Unduh CSV
                    </a>
                </div>
            </div>

            <div class="markas-info">
                <div class="badge-info">
                    📍 <strong>Markas:</strong> <?= htmlencode($activeCity['name'] ?? '') ?> (<?= htmlencode($activeCity['country'] ?? '') ?>)
                </div>
                <div class="badge-info">
                    🧭 <strong>Koordinat:</strong> <?= number_format($activeCity['lat'], 4) ?>° LU/LS, <?= number_format($activeCity['lon'], 4) ?>° BT
                </div>
                <div class="badge-info">
                    🏔️ <strong>Tinggi:</strong> <?= htmlencode($activeCity['elev'] ?? '') ?> m dpl
                </div>
                <div class="badge-info">
                    ⏰ <strong>Zona Waktu:</strong> GMT+<?= htmlencode($activeCity['tz'] ?? '') ?>
                </div>
                <div class="badge-info">
                    🕋 <strong>Arah Kiblat:</strong> <?= htmlencode($qibla['dms'] ?? '') ?> (<?= htmlencode($qibla['barat_ke_utara'] ?? '') ?>)
                </div>
            </div>
        </div>

        <!-- Widget Waktu Sholat Real-Time & Countdown -->
        <?php include __DIR__ . '/templates/widget-prayer.php'; ?>

        <!-- Filter & Control Panel -->
        <div class="controls-card no-print">
            <form method="GET" action="" class="controls-form">
                <input type="hidden" name="view" value="<?= htmlencode($viewMode ?? '') ?>">

                <div class="form-group">
                    <label for="year">Tahun Masehi</label>
                    <input type="number" id="year" name="year" class="form-control" style="width: 100px;" value="<?= $selectedYear ?>" min="1900" max="2100">
                </div>

                <div class="form-group">
                    <label for="city">Kota / Markas</label>
                    <select id="city" name="city" class="form-control" style="width: 220px;" onchange="this.form.submit()">
                        <optgroup label="Kota di Indonesia (321 Markas)">
                            <?php foreach ($indonesiaCities as $c): ?>
                                <option value="<?= htmlencode($c['name'] ?? '') ?>" <?= (strcasecmp($c['name'], $selectedCityName) === 0) ? 'selected' : '' ?>>
                                    <?= htmlencode($c['name'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <div class="form-group">
                    <label for="month">Filter Bulan</label>
                    <select id="month" name="month" class="form-control" style="width: 140px;">
                        <option value="0" <?= ($selectedMonth == 0) ? 'selected' : '' ?>>Semua (12 Bulan)</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>><?= $namaBulanMasehi[$m] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="h_adj">Koreksi Hijriyah</label>
                    <select id="h_adj" name="h_adj" class="form-control" style="width: 120px;">
                        <option value="-2" <?= ($hijriAdjustment == -2) ? 'selected' : '' ?>>-2 Hari</option>
                        <option value="-1" <?= ($hijriAdjustment == -1) ? 'selected' : '' ?>>-1 Hari</option>
                        <option value="0" <?= ($hijriAdjustment == 0) ? 'selected' : '' ?>>Standar (0)</option>
                        <option value="1" <?= ($hijriAdjustment == 1) ? 'selected' : '' ?>>+1 Hari</option>
                        <option value="2" <?= ($hijriAdjustment == 2) ? 'selected' : '' ?>>+2 Hari</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        🔍 Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Navigation Tabs -->
        <div class="tabs no-print">
            <a href="?<?= http_build_query(array_merge($_GET, ['view' => 'calendar'])) ?>" class="tab-item <?= ($viewMode === 'calendar') ? 'active' : '' ?>">
                📅 Kalender 12 Bulan
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['view' => 'table'])) ?>" class="tab-item <?= ($viewMode === 'table') ? 'active' : '' ?>">
                🕌 Jadwal Sholat Tahunan
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['view' => 'ramadhan'])) ?>" class="tab-item <?= ($viewMode === 'ramadhan') ? 'active' : '' ?>">
                🌙 Imsakiyah Ramadhan
            </a>
        </div>

        <!-- Dynamic View Template Rendering -->
        <?php
        $viewTemplates = [
            'calendar' => __DIR__ . '/templates/view-calendar.php',
            'table'    => __DIR__ . '/templates/view-table.php',
            'ramadhan' => __DIR__ . '/templates/view-ramadhan.php',
        ];
        $templateFile = $viewTemplates[$viewMode] ?? $viewTemplates['calendar'];
        include $templateFile;
        ?>

        <!-- Footer -->
        <footer style="margin-top: 40px; text-align: center; font-size: 13px; color: var(--text-muted); border-top: 1px solid var(--border); padding-top: 20px;" class="no-print">
            <p><strong>Aplikasi Generator Kalender Tahunan & Waktu Sholat Falakiyah</strong></p>
            <p>Dihasilkan berdasarkan algoritma hisab falak file <a href="file:///d:/mts4/sumber/jadwal_imsakiyah_romadlon.xlsx" style="color: var(--primary);">jadwal_imsakiyah_romadlon.xlsx</a></p>
        </footer>
    </div>
    <script src="assets/js/prayer-widget.js"></script>
</body>
</html>
