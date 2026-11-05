<!-- ================= VIEW 3: KHUSUS JADWAL IMSAKIYAH RAMADHAN ================= -->
<?php
// Cari tanggal awal Ramadhan di tahun terpilih
$ramadhanStart = null;
$startDate = new DateTime("$selectedYear-01-01");
$endDate = new DateTime("$selectedYear-12-31");
$interval = new DateInterval('P1D');
$period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

foreach ($period as $dt) {
    $y = (int)$dt->format('Y');
    $m = (int)$dt->format('m');
    $d = (int)$dt->format('d');
    $h = getHijriDate($y, $m, $d, $hijriAdjustment);
    if ($h['month'] == 9 && $h['day'] == 1) {
        $ramadhanStart = clone $dt;
        $ramadhanYear = $h['year'];
        break;
    }
}

if (!$ramadhanStart) {
    // Default perkiraan jika tidak ditemukan di range
    $ramadhanStart = new DateTime("$selectedYear-02-18");
    $ramadhanYear = 1447;
}
?>
<div class="ramadhan-banner">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div>
            <h2>JADWAL IMSAKIYAH RAMADHAN <?= $ramadhanYear ?> H. / <?= $selectedYear ?> M.</h2>
            <p>Markas: <?= htmlencode($activeCity['name'] ?? '') ?> | Lintang: <?= number_format($activeCity['lat'], 4) ?>° | Bujur: <?= number_format($activeCity['lon'], 4) ?>° | Arah Kiblat: <?= htmlencode($qibla['dms'] ?? '') ?></p>
        </div>
        <div style="font-size: 13px; background: rgba(0,0,0,0.2); padding: 8px 14px; border-radius: 8px; margin-top: 8px;">
            Sudut Irtifak: Shubuh 20° | Isya' 18° | Dhuha 4.5° | Ihtiyat: <?= $ihtiyat ?> mnt (Dhuhur <?= $ihtiyatDhuhur ?> mnt)
        </div>
    </div>
</div>

<div class="table-card">
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr style="background: #065f46; color: white;">
                    <th style="color: white;">Ramadhan</th>
                    <th style="color: white;">Tanggal Miladi</th>
                    <th style="color: white;">Hari & Pasaran</th>
                    <th style="color: #fef08a;">Imsak</th>
                    <th style="color: white;">Shubuh</th>
                    <th style="color: white;">Terbit</th>
                    <th style="color: white;">Dhuha</th>
                    <th style="color: white;">Dhuhur</th>
                    <th style="color: white;">Ashar</th>
                    <th style="color: white;">Maghrib</th>
                    <th style="color: white;">Isya'</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $currDt = clone $ramadhanStart;
                for ($ramDay = 1; $ramDay <= 30; $ramDay++):
                    $y = (int)$currDt->format('Y');
                    $m = (int)$currDt->format('m');
                    $d = (int)$currDt->format('d');
                    $miladiStr = $currDt->format('d') . ' ' . $namaBulanPendek[$m] . ' ' . $y;
                    $hp = getHariPasaran($y, $m, $d, $activeCity['tz']);
                    $sholat = calculatePrayerTimes($y, $m, $d, $activeCity['lat'], $activeCity['lon'], $activeCity['tz'], $activeCity['elev'], [
                        'ihtiyat' => $ihtiyat,
                        'ihtiyat_dhuhur' => $ihtiyatDhuhur
                    ]);
                ?>
                    <tr <?= ($ramDay % 2 === 0) ? 'style="background: #f8fafc;"' : '' ?>>
                        <td><strong><?= $ramDay ?></strong></td>
                        <td><?= $miladiStr ?></td>
                        <td><?= $hp['lengkap'] ?></td>
                        <td class="time-cell imsak-cell" style="background: #fef2f2;"><?= $sholat['imsak'] ?></td>
                        <td class="time-cell"><?= $sholat['subuh'] ?></td>
                        <td class="time-cell" style="color: #64748b;"><?= $sholat['terbit'] ?></td>
                        <td class="time-cell"><?= $sholat['dhuha'] ?></td>
                        <td class="time-cell"><?= $sholat['dhuhur'] ?></td>
                        <td class="time-cell"><?= $sholat['ashar'] ?></td>
                        <td class="time-cell" style="background: #f0fdf4; font-weight: 700; color: #15803d;"><?= $sholat['maghrib'] ?></td>
                        <td class="time-cell"><?= $sholat['isya'] ?></td>
                    </tr>
                <?php
                    $currDt->modify('+1 day');
                endfor;
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Catatan Hisab Awal Bulan -->
<div class="controls-card" style="font-size: 13px; color: #475569;">
    <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">
        📝 Catatan Falakiyah & Imsakiyah:
    </h4>
    <ul style="padding-left: 20px; line-height: 1.6;">
        <li>Waktu Imsak ditetapkan 10 menit sebelum waktu Shubuh sebagai langkah kehati-hatian (ihtiyath).</li>
        <li>Waktu sholat telah ditambahkan faktor ihtiyath: Shubuh, Dhuha, Ashar, Maghrib, Isya (+<?= $ihtiyat ?> menit), Dhuhur (+<?= $ihtiyatDhuhur ?> menit).</li>
        <li>Penetapan awal Ramadhan dan 1 Syawwal tetap mengacu pada keputusan resmi Sidang Isbat Pemerintah R.I.</li>
    </ul>
</div>
