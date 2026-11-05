<!-- ================= VIEW 1: KALENDER TAHUNAN 12 BULAN ================= -->
<div class="calendar-grid">
    <?php
    $mStart = ($selectedMonth > 0) ? $selectedMonth : 1;
    $mEnd = ($selectedMonth > 0) ? $selectedMonth : 12;

    for ($m = $mStart; $m <= $mEnd; $m++):
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $m, $selectedYear);
        // Indeks hari pertama (0 = Ahad, 1 = Senin, ..., 6 = Sabtu)
        $firstDayJd = getJulianDay($selectedYear, $m, 1, $activeCity['tz']);
        $firstDayInfo = getHariPasaran($selectedYear, $m, 1, $activeCity['tz']);
        $startDayOfWeek = $firstDayInfo['day_index'];

        // Rentang Hijriyah dalam bulan ini
        $hFirst = getHijriDate($selectedYear, $m, 1, $hijriAdjustment);
        $hLast = getHijriDate($selectedYear, $m, $daysInMonth, $hijriAdjustment);
        $hijriRangeStr = ($hFirst['month_name'] === $hLast['month_name'])
            ? $hFirst['month_name'] . ' ' . $hFirst['year'] . ' H'
            : $hFirst['month_name'] . ' - ' . $hLast['month_name'] . ' ' . $hLast['year'] . ' H';

        // Informasi Imlek dalam bulan ini
        $cFirst = getChineseLunisolarDate($selectedYear, $m, 1);
        $cLast = getChineseLunisolarDate($selectedYear, $m, $daysInMonth);
        $chineseYearStr = '';
        $chineseTooltip = '';
        if ($cFirst && $cLast) {
            if ($cFirst['year'] === $cLast['year']) {
                $chineseYearStr = $cFirst['year'] . ' 🏮 Shio ' . $cFirst['shio'] . ' (' . $cFirst['elemen'] . ')';
                $chineseTooltip = 'Tahun Imlek ' . $cFirst['year'] . ' - ' . $cFirst['nama_tahun'];
            } else {
                $chineseYearStr = $cFirst['year'] . '/' . $cLast['year'] . ' 🏮 Shio ' . $cLast['shio'] . ' (' . $cLast['elemen'] . ')';
                $chineseTooltip = 'Pergantian Tahun Imlek: ' . $cFirst['year'] . ' (' . $cFirst['shio'] . ') ke ' . $cLast['year'] . ' (' . $cLast['nama_tahun'] . ')';
            }
        }
    ?>
        <div class="month-card">
            <div class="month-header">
                <div>
                    <span class="month-name"><?= $namaBulanMasehi[$m] ?> <?= $selectedYear ?></span>
                    <span class="month-hijri-span"><?= $hijriRangeStr ?></span>
                </div>
                <?php if ($chineseYearStr): ?>
                    <span class="month-chinese-span" title="<?= htmlencode($chineseTooltip) ?>">
                        <?= $chineseYearStr ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="days-header">
                <div>AHD</div>
                <div>SEN</div>
                <div>SEL</div>
                <div>RAB</div>
                <div>KAM</div>
                <div>JUM</div>
                <div>SAB</div>
            </div>
            <div class="month-days">
                <?php
                // Padding hari sebelum tanggal 1
                for ($p = 0; $p < $startDayOfWeek; $p++) {
                    echo '<div class="day-cell empty"></div>';
                }

                // Tanggal 1 s/d akhir bulan
                $todayStr = date('Y-m-d');
                for ($d = 1; $d <= $daysInMonth; $d++):
                    $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $m, $d);
                    $hp = getHariPasaran($selectedYear, $m, $d, $activeCity['tz']);
                    $hijri = getHijriDate($selectedYear, $m, $d, $hijriAdjustment);
                    $chinese = getChineseLunisolarDate($selectedYear, $m, $d);
                    $isSunday = ($hp['day_index'] === 0);
                    $isToday = ($dateStr === $todayStr);
                    $isHoliday = isset($holidays[$dateStr]);
                    $holidayInfo = $isHoliday ? $holidays[$dateStr] : null;

                    $cellClasses = ['day-cell'];
                    if ($isSunday) $cellClasses[] = 'sunday';
                    if ($isHoliday) $cellClasses[] = 'holiday';
                    if ($isToday) $cellClasses[] = 'today';
                    if ($chinese && $chinese['day'] === 1) $cellClasses[] = 'lunar-first-day';

                    // Tooltip interaktif yang komprehensif
                    $tooltipParts = [
                        $hp['lengkap'],
                        $d . ' ' . $namaBulanMasehi[$m] . ' ' . $selectedYear . ' M',
                        $hijri['formatted']
                    ];
                    if ($chinese) {
                        $tooltipParts[] = 'Imlek: ' . $chinese['formatted'] . ' (' . $chinese['day_name'] . ', Shio ' . $chinese['shio'] . ' ' . $chinese['elemen'] . ')';
                    }
                    if ($isHoliday) {
                        $tooltipParts[] = '(' . $holidayInfo['name'] . ')';
                    }
                    $cellTooltip = implode(' | ', $tooltipParts);
                ?>
                    <div class="<?= implode(' ', $cellClasses) ?>" title="<?= htmlencode($cellTooltip) ?>">
                        <div class="day-top">
                            <span class="masehi-num"><?= $d ?></span>
                            <div class="secondary-badges">
                                <span class="hijri-num" title="Hijriyah: <?= $hijri['day'] ?> <?= $hijri['month_name'] ?>"><?= $hijri['day'] ?></span>
                                <?php if ($chinese): ?>
                                    <span class="lunar-num <?= ($chinese['day'] === 1) ? 'lunar-first' : '' ?>" title="Imlek: <?= $chinese['formatted'] ?>"><?= $chinese['day'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="day-bottom">
                            <span class="pasaran-txt"><?= $hp['pasaran'] ?></span>
                            <?php if ($chinese && $chinese['day'] === 1): ?>
                                <span class="lunar-month-badge" title="Awal Bulan Imlek: <?= $chinese['month_name'] ?>">
                                    <?= ($chinese['is_leap'] ? 'R.' : '') . $chinese['month'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($isHoliday): ?>
                            <div class="holiday-dot" title="<?= htmlencode($holidayInfo['name'] ?? '') ?>"></div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    <?php endfor; ?>
</div>

<!-- Legenda Hari Libur & Peristiwa Penting -->
<div class="controls-card" style="margin-top: 24px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 12px; color: var(--primary);">
        📌 Hari Libur Nasional & Hari Besar Islam Tahun <?= $selectedYear ?>
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px; font-size: 13px;">
        <?php
        ksort($holidays);
        foreach ($holidays as $dt => $ev):
            $ts = strtotime($dt);
            $formattedDate = date('d', $ts) . ' ' . $namaBulanPendek[(int)date('n', $ts)] . ' ' . date('Y', $ts);
            $badgeColor = ($ev['type'] === 'nasional') ? '#dc2626' : (($ev['type'] === 'islam') ? '#059669' : '#0284c7');
        ?>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?= $badgeColor ?>;"></span>
                <strong><?= $formattedDate ?>:</strong> <?= htmlencode($ev['name'] ?? '') ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
