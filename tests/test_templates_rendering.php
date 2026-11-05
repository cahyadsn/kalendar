<?php
/**
 * Test Integrasi: Rendering Template dan Ketiadaan PHP Notice/Warning
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

$capturedErrors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    global $capturedErrors;
    $capturedErrors[] = [
        'errno' => $errno,
        'errstr' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ];
    return true; // Don't execute PHP internal error handler
});

echo "=== Menjalankan Pengujian Rendering Template & Ketiadaan Notice/Warning ===\n\n";

function renderView($params) {
    global $capturedErrors;
    $_GET = $params;
    
    ob_start();
    include __DIR__ . '/../index.php';
    $html = ob_get_clean();
    
    return $html;
}

// 1. Uji Render View: Calendar (12 Bulan)
echo "Test 1: Render View 'calendar' (Pacitan 2026)...\n";
$htmlCalendar = renderView(['view' => 'calendar', 'city' => 'PACITAN', 'year' => 2026]);
assertTrue(strpos($htmlCalendar, 'id="prayer-widget"') !== false, 'Widget real-time ter-render pada view calendar');
assertTrue(strpos($htmlCalendar, 'calendar-grid') !== false, 'Elemen calendar-grid ter-render');
assertTrue(strpos($htmlCalendar, 'month-card') !== false, 'Elemen month-card ter-render');
assertTrue(strpos($htmlCalendar, 'Hari Libur Nasional & Hari Besar Islam') !== false, 'Legenda hari libur ter-render');
assertTrue(strpos($htmlCalendar, 'month-chinese-span') !== false, 'Badge Shio & Imlek ter-render di header bulan kalender');
assertTrue(strpos($htmlCalendar, 'lunar-num') !== false, 'Angka penanggalan Imlek ter-render pada sel tanggal');
assertTrue(strpos($htmlCalendar, 'Shio Kuda') !== false, 'Shio tahun 2026 (Kuda) tertera pada tampilan kalender');
assertTrue(strpos($htmlCalendar, 'secondary-badges') !== false, 'Kontainer secondary-badges ter-render');

// 2. Uji Render View: Table (Jadwal Sholat Tahunan)
echo "\nTest 2: Render View 'table' (Jakarta 2026)...\n";
$htmlTable = renderView(['view' => 'table', 'city' => 'JAKARTA', 'year' => 2026]);
assertTrue(strpos($htmlTable, 'id="prayer-widget"') !== false, 'Widget real-time ter-render pada view table');
assertTrue(strpos($htmlTable, 'table-card') !== false, 'Elemen table-card ter-render');
assertTrue(strpos($htmlTable, 'Jadwal Sholat Bulan') !== false, 'Tabel bulanan ter-render');

// 3. Uji Render View: Ramadhan (Imsakiyah 30 Hari)
echo "\nTest 3: Render View 'ramadhan' (Surabaya 2026)...\n";
$htmlRamadhan = renderView(['view' => 'ramadhan', 'city' => 'SURABAYA', 'year' => 2026]);
assertTrue(strpos($htmlRamadhan, 'id="prayer-widget"') !== false, 'Widget real-time ter-render pada view ramadhan');
assertTrue(strpos($htmlRamadhan, 'ramadhan-banner') !== false, 'Banner imsakiyah Ramadhan ter-render');
assertTrue(strpos($htmlRamadhan, 'JADWAL IMSAKIYAH RAMADHAN') !== false, 'Judul imsakiyah Ramadhan ter-render');
assertTrue(strpos($htmlRamadhan, 'Catatan Falakiyah & Imsakiyah:') !== false, 'Catatan imsakiyah ter-render');

// 4. Uji Ketiadaan PHP Notice / Warning / Error
echo "\nTest 4: Memeriksa ketiadaan PHP error/notice/warning saat rendering...\n";
if (empty($capturedErrors)) {
    assertTrue(true, 'Tidak ada error/notice/warning selama rendering');
} else {
    foreach ($capturedErrors as $err) {
        echo "  [ERROR CAPTURED] {$err['errstr']} in {$err['file']} line {$err['line']}\n";
    }
    assertTrue(false, 'Ditemukan ' . count($capturedErrors) . ' notice/warning selama rendering');
}

echo "\n=======================================================\n";
echo "Hasil Pengujian: {$testPassed} Lulus, {$testFailed} Gagal\n";
echo "=======================================================\n";

if ($testFailed > 0) {
    exit(1);
}
exit(0);
