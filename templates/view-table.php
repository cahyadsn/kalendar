<!-- ================= VIEW 2: TABEL JADWAL SHOLAT ================= -->
<?php
$mStart = ($selectedMonth > 0) ? $selectedMonth : 1;
$mEnd = ($selectedMonth > 0) ? $selectedMonth : 12;

for ($m = $mStart; $m <= $mEnd; $m++):
    $daysInM = cal_days_in_month(CAL_GREGORIAN, $m, $selectedYear);
?>
    <div class="table-card">
        <div class="month-header">
            <span class="month-name">Jadwal Sholat Bulan <?= $namaBulanMasehi[$m] ?> <?= $selectedYear ?></span>
            <span class="month-hijri-span"><?= $activeCity['name'] ?> (GMT+<?= $activeCity['tz'] ?>)</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tgl</th>
                        <th>Hari & Pasaran</th>
                        <th>Hijriyah</th>
                        <th>Imsak</th>
                        <th>Subuh</th>
                        <th>Terbit</th>
                        <th>Dhuha</th>
                        <th>Dhuhur</th>
                        <th>Ashar</th>
                        <th>Maghrib</th>
                        <th>Isya</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($d = 1; $d <= $daysInM; $d++):
                        $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $m, $d);
                        $hp = getHariPasaran($selectedYear, $m, $d, $activeCity['tz']);
                        $hijri = getHijriDate($selectedYear, $m, $d, $hijriAdjustment);
                        $sholat = calculatePrayerTimes($selectedYear, $m, $d, $activeCity['lat'], $activeCity['lon'], $activeCity['tz'], $activeCity['elev'], [
                            'ihtiyat' => $ihtiyat,
                            'ihtiyat_dhuhur' => $ihtiyatDhuhur
                        ]);
                        $isSunday = ($hp['day_index'] === 0);
                        $isHoliday = isset($holidays[$dateStr]);
                        $holidayName = $isHoliday ? $holidays[$dateStr]['name'] : '';
                        $rowStyle = $isHoliday ? 'background: #fef2f2;' : ($isSunday ? 'background: #fffbeb;' : '');
                    ?>
                        <tr style="<?= $rowStyle ?>">
                            <td><strong><?= $d ?></strong></td>
                            <td><?= $hp['lengkap'] ?></td>
                            <td><?= $hijri['day'] ?> <?= $hijri['month_name'] ?></td>
                            <td class="time-cell imsak-cell"><?= $sholat['imsak'] ?></td>
                            <td class="time-cell"><?= $sholat['subuh'] ?></td>
                            <td class="time-cell" style="color: #64748b;"><?= $sholat['terbit'] ?></td>
                            <td class="time-cell"><?= $sholat['dhuha'] ?></td>
                            <td class="time-cell"><?= $sholat['dhuhur'] ?></td>
                            <td class="time-cell"><?= $sholat['ashar'] ?></td>
                            <td class="time-cell"><?= $sholat['maghrib'] ?></td>
                            <td class="time-cell"><?= $sholat['isya'] ?></td>
                            <td style="color: <?= $isHoliday ? '#dc2626' : '#64748b' ?>; font-size: 12px; font-weight: <?= $isHoliday ? '600' : 'normal' ?>;">
                                <?= htmlencode($holidayName ?? '') ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endfor; ?>
