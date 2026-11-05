<?php
/**
 * =====================================================================================
 * FUNGSI PERHITUNGAN FALAKIAH & ASTRONOMI
 * Mengadopsi algoritma hisab falak dari file Excel: sumber/jadwal_imsakiyah_romadlon.xlsx
 * Ephemeris Jean Meeus / Kitab Tsamarotul Fikar - Ibnu Zahid Abdo el-Moeid
 * =====================================================================================
 */

if (!function_exists('htmlencode')) {
    function htmlencode($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Menghitung Julian Day Number (JDN) untuk tanggal tertentu pada waktu dzuhur lokal
 * Formula di Excel Jadwal: Col S
 */
if (!function_exists('getJulianDay')) {
    function getJulianDay($year, $month, $day, $tz = 7.0) {
        $q11 = 12.0 - $tz;
        $y = $year;
        $m = $month;
        if ($m < 3) {
            $y -= 1;
            $m += 12;
        }
        $a = floor($y / 100);
        $b = 2 - $a + floor($a / 4);
        $jd = floor(365.25 * ($y + 4716)) + floor(30.6001 * ($m + 1)) + $day + ($q11 / 24.0) + $b - 1524.5;
        return $jd;
    }
}

/**
 * Mendapatkan Hari dan Pasaran Jawa (Weton)
 * Formula di Excel Jadwal: Col C (HrPs)
 */
if (!function_exists('getHariPasaran')) {
    function getHariPasaran($year, $month, $day, $tz = 7.0) {
        $days = [
            0 => 'Selasa',
            1 => 'Rabu',
            2 => 'Kamis',
            3 => "Jum'at",
            4 => 'Sabtu',
            5 => 'Ahad',
            6 => 'Senin'
        ];
        $pasarans = [
            0 => 'Pahing',
            1 => 'Pon',
            2 => 'Wage',
            3 => 'Kliwon',
            4 => 'Legi'
        ];
        $jd = getJulianDay($year, $month, $day, $tz);
        $intJd = (int)floor($jd);
        $dIdx = $intJd % 7;
        if ($dIdx < 0) $dIdx += 7;
        $pIdx = $intJd % 5;
        if ($pIdx < 0) $pIdx += 5;

        return [
            'hari' => $days[$dIdx],
            'pasaran' => $pasarans[$pIdx],
            'lengkap' => $days[$dIdx] . ' ' . $pasarans[$pIdx],
            'day_index' => ($dIdx + 5) % 7 // 0 = Ahad, 1 = Senin, ..., 6 = Sabtu
        ];
    }
}

/**
 * Konversi Penanggalan Masehi ke Hijriyah
 * Algoritma Kalender Islam Astronomis / Falakiyah (Kemenag & Hisab Urfi)
 */
if (!function_exists('getHijriDate')) {
    function getHijriDate($year, $month, $day, $adjustment = 0) {
        $jd = getJulianDay($year, $month, $day, 7.0) + $adjustment;
        $epoch = 1948439.5;
        $days = floor($jd - $epoch);

        $cycle = floor(($days - 1) / 10631);
        $rem = ($days - 1) - $cycle * 10631;

        $leap_years = [2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29];
        $yearInCycle = 1;
        while ($yearInCycle <= 30) {
            $isLeap = in_array($yearInCycle, $leap_years);
            $daysInYear = $isLeap ? 355 : 354;
            if ($rem < $daysInYear) break;
            $rem -= $daysInYear;
            $yearInCycle++;
        }

        $hYear = $cycle * 30 + $yearInCycle;
        $monthLengths = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, in_array($yearInCycle, $leap_years) ? 30 : 29];

        $hMonth = 1;
        for ($i = 0; $i < 12; $i++) {
            if ($rem < $monthLengths[$i]) {
                $hMonth = $i + 1;
                break;
            }
            $rem -= $monthLengths[$i];
        }
        $hDay = (int)$rem + 1;

        $monthNames = [
            1 => 'Muharram',
            2 => 'Safar',
            3 => "Rabi'ul Awal",
            4 => "Rabi'ul Akhir",
            5 => 'Jumadil Awal',
            6 => 'Jumadil Akhir',
            7 => 'Rajab',
            8 => "Sya'ban",
            9 => 'Ramadhan',
            10 => 'Syawwal',
            11 => "Dzulqa'dah",
            12 => 'Dzulhijjah'
        ];

        return [
            'day' => $hDay,
            'month' => $hMonth,
            'year' => $hYear,
            'month_name' => $monthNames[$hMonth] ?? '',
            'formatted' => $hDay . ' ' . ($monthNames[$hMonth] ?? '') . ' ' . $hYear . ' H'
        ];
    }
}

/**
 * Menghitung Arah Kiblat (Azimuth dari Utara sejati searah jarum jam)
 * dan sudut deviasi dari Barat ke Utara
 */
if (!function_exists('getQiblaDirection')) {
    function getQiblaDirection($lat, $lon) {
        $latM = 21.4225; // Ka'bah
        $lonM = 39.8262;
        $dr = M_PI / 180.0;

        $dLon = ($lonM - $lon) * $dr;
        $y = sin($dLon);
        $x = cos($lat * $dr) * tan($latM * $dr) - sin($lat * $dr) * cos($dLon);
        $qibla = atan2($y, $x) * 180.0 / M_PI;
        if ($qibla < 0) $qibla += 360.0;

        $deg = floor($qibla);
        $min = floor(($qibla - $deg) * 60.0);
        $sec = round((($qibla - $deg) * 60.0 - $min) * 60.0, 1);

        return [
            'azimuth' => round($qibla, 2),
            'dms' => sprintf("%d° %02d' %02d\"", $deg, $min, $sec),
            'barat_ke_utara' => sprintf("%.1f° B-U", abs($qibla - 270.0))
        ];
    }
}

/**
 * Menghitung Waktu Sholat Harian Lengkap (Jean Meeus / Ephemeris Hisab Falakiah)
 * Mengikuti formula baris demi baris pada sheet Jadwal Excel
 */
if (!function_exists('calculatePrayerTimes')) {
    function calculatePrayerTimes($year, $month, $day, $lat, $lon, $tz = 7.0, $elev = 10.0, $settings = []) {
        $ihtiyat = $settings['ihtiyat'] ?? 1;
        $ihtiyatDhuhur = $settings['ihtiyat_dhuhur'] ?? 4;
        $sudutShubuh = $settings['shubuh'] ?? 20.0;
        $sudutIsya = $settings['isya'] ?? 18.0;
        $sudutDhuha = $settings['dhuha'] ?? 4.5;
        $imsakInterval = $settings['imsak_interval'] ?? 10.0;
        $shadowRatio = $settings['shadow_ratio'] ?? 1.0;

        $dr = M_PI / 180.0;
        $jd = getJulianDay($year, $month, $day, $tz);
        $t = ($jd - 2451545.0) / 36525.0;

        // L0 (Bujur rata-rata matahari)
        $l0_raw = (280.46645 + 36000.76983 * $t) / 360.0;
        $l0 = ($l0_raw - floor($l0_raw)) * 360.0;

        // M (Anomali rata-rata matahari)
        $m_raw = (357.5291 + 35999.0503 * $t) / 360.0;
        $m = ($m_raw - floor($m_raw)) * 360.0;

        // Omega
        $omega_raw = (125.04 - 1934.136 * $t) / 360.0;
        $omega = ($omega_raw - floor($omega_raw)) * 360.0;

        // Nutasi & Kemiringan Ekliptika
        $aa = (17.264 / 3600.0) * sin($omega * $dr) + (0.206 / 3600.0) * sin(2 * $omega * $dr);
        $ab = (-1.264 / 3600.0) * sin(2 * $l0 * $dr);
        $ac = (9.23 / 3600.0) * cos($omega * $dr) - (0.09 / 3600.0) * cos(2 * $omega * $dr);
        $ad = (0.548 / 3600.0) * cos(2 * $l0 * $dr);
        $eps = 23.43929111 + $ac + $ad - (46.815 / 3600.0) * $t;

        // Persamaan Pusat (Equation of Center)
        $c_center = (6898.06 / 3600.0) * sin($m * $dr) + (72.095 / 3600.0) * sin(2 * $m * $dr) + (0.966 / 3600.0) * sin(3 * $m * $dr);
        $lambda = fmod($l0 + $c_center + $aa + $ab - (20.47 / 3600.0), 360.0);
        if ($lambda < 0) $lambda += 360.0;

        // Deklinasi Matahari
        $delta = asin(sin($lambda * $dr) * sin($eps * $dr)) * 180.0 / M_PI;

        // Equation of Time (Perata Waktu)
        $eps_half = 0.5 * $eps;
        $y = pow(tan($eps_half * $dr), 2);
        $e = 0.01675104 - 0.0000418 * $t + 0.000000126 * $t * $t;

        $an = $y * sin(2 * $l0 * $dr);
        $ao = 2 * $e * sin($m * $dr);
        $ap = 4 * $e * $y * sin($m * $dr) * cos(2 * $l0 * $dr);
        $aq = 0.5 * $y * $y * sin(4 * $l0 * $dr);
        $ar = 1.25 * $e * $e * sin(2 * $m * $dr);
        $as = $an - $ao + $ap - $aq - $ar;
        $eot = ($as * 57.29577951308232) / 15.0; // jam

        // Waktu Zawal / Transit (Tengah Hari)
        $transit = 12.0 - $eot + (($tz * 15.0) - $lon) / 15.0;

        // Semi-diameter Matahari & Dip Horizon
        $sd = 0.267 / (1.0 - 0.017 * cos($m * $dr));
        $dip = (1.76 / 60.0) * sqrt($elev);
        $bi = -($sd + (34.5 / 60.0) + $dip) - 0.0024;

        $bc = -tan($lat * $dr) * tan($delta * $dr);
        $bd = cos($lat * $dr) * cos($delta * $dr);

        $getHourAngle = function($alt) use ($bc, $bd, $dr) {
            $val = $bc + sin($alt * $dr) / $bd;
            if ($val < -1.0) $val = -1.0;
            if ($val > 1.0) $val = 1.0;
            return acos($val) * 180.0 / M_PI / 15.0;
        };

        // Waktu tanpa Ihtiyat
        $t_ashar_alt = atan(1.0 / (tan(abs($lat - $delta) * $dr) + $shadowRatio)) * 180.0 / M_PI;
        $ashar_raw = $transit + $getHourAngle($t_ashar_alt);
        $maghrib_raw = $transit + $getHourAngle($bi);
        $isya_raw = $transit + $getHourAngle(-$sudutIsya);
        $subuh_raw = $transit - $getHourAngle(-$sudutShubuh);
        $imsak_raw = $subuh_raw - ($imsakInterval / 60.0);
        $terbit_raw = $transit - $getHourAngle($bi);
        $dhuha_raw = $transit - $getHourAngle($sudutDhuha);

        // Format waktu dengan pembulatan dan ihtiyat sesuai kaidah Excel Falak
        $formatTime = function($hoursRaw, $ihtiyatMinutes, $isTerbit = false) {
            $totalMinutes = $hoursRaw * 60.0;
            if ($isTerbit) {
                $totalMinutes -= $ihtiyatMinutes;
                $m = floor($totalMinutes);
            } else {
                $m = ceil($totalMinutes) + $ihtiyatMinutes;
            }
            $h = floor($m / 60.0);
            $min = (int)($m % 60);
            return sprintf('%02d:%02d', (int)$h, (int)$min);
        };

        return [
            'imsak' => $formatTime($imsak_raw, $ihtiyat),
            'subuh' => $formatTime($subuh_raw, $ihtiyat),
            'terbit' => $formatTime($terbit_raw, $ihtiyat, true),
            'dhuha' => $formatTime($dhuha_raw, $ihtiyat),
            'dhuhur' => $formatTime($transit, $ihtiyatDhuhur),
            'ashar' => $formatTime($ashar_raw, $ihtiyat),
            'maghrib' => $formatTime($maghrib_raw, $ihtiyat),
            'isya' => $formatTime($isya_raw, $ihtiyat),
            'delta' => round($delta, 3),
            'eot' => round($eot * 60.0, 1) // perata waktu dlm menit
        ];
    }
}

/**
 * Menghitung Hari Libur Nasional & Hari Besar Islam
 */
if (!function_exists('getHolidays')) {
    function getHolidays($year, $hijriAdjustment = 0) {
        $holidays = [
            // Libur Nasional Masehi Tetap
            "$year-01-01" => ['name' => 'Tahun Baru Masehi', 'type' => 'nasional'],
            "$year-05-01" => ['name' => 'Hari Buruh Internasional', 'type' => 'nasional'],
            "$year-06-01" => ['name' => 'Hari Lahir Pancasila', 'type' => 'nasional'],
            "$year-08-17" => ['name' => 'Hari Proklamasi Kemerdekaan RI', 'type' => 'nasional'],
            "$year-12-25" => ['name' => 'Hari Raya Natal', 'type' => 'nasional']
        ];

        // Cek seluruh hari dalam setahun untuk Hari Besar Islam berdasarkan kalender Hijriyah
        $startDate = new DateTime("$year-01-01");
        $endDate = new DateTime("$year-12-31");
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            $y = (int)$dt->format('Y');
            $m = (int)$dt->format('m');
            $d = (int)$dt->format('d');

            $h = getHijriDate($y, $m, $d, $hijriAdjustment);

            // Peristiwa Islam Penting
            if ($h['month'] == 1 && $h['day'] == 1) {
                $holidays[$dateStr] = ['name' => 'Tahun Baru Islam (1 Muharram ' . $h['year'] . ' H)', 'type' => 'islam'];
            } elseif ($h['month'] == 1 && $h['day'] == 10) {
                $holidays[$dateStr] = ['name' => 'Hari Asyura (10 Muharram)', 'type' => 'sunnah'];
            } elseif ($h['month'] == 3 && $h['day'] == 12) {
                $holidays[$dateStr] = ['name' => 'Maulid Nabi Muhammad SAW (12 Rabi\'ul Awal)', 'type' => 'islam'];
            } elseif ($h['month'] == 7 && $h['day'] == 27) {
                $holidays[$dateStr] = ['name' => 'Isra Mi\'raj Nabi Muhammad SAW (27 Rajab)', 'type' => 'islam'];
            } elseif ($h['month'] == 8 && $h['day'] == 15) {
                $holidays[$dateStr] = ['name' => 'Nisfu Sya\'ban', 'type' => 'sunnah'];
            } elseif ($h['month'] == 9 && $h['day'] == 1) {
                $holidays[$dateStr] = ['name' => 'Awal Puasa Ramadhan 1447/1448 H', 'type' => 'islam'];
            } elseif ($h['month'] == 9 && $h['day'] == 17) {
                $holidays[$dateStr] = ['name' => 'Nuzulul Qur\'an (17 Ramadhan)', 'type' => 'islam'];
            } elseif ($h['month'] == 10 && $h['day'] == 1) {
                $holidays[$dateStr] = ['name' => 'Hari Raya Idul Fitri 1 Syawwal', 'type' => 'islam'];
            } elseif ($h['month'] == 10 && $h['day'] == 2) {
                $holidays[$dateStr] = ['name' => 'Hari Raya Idul Fitri Hari ke-2', 'type' => 'islam'];
            } elseif ($h['month'] == 12 && $h['day'] == 9) {
                $holidays[$dateStr] = ['name' => 'Hari Arafah (Puasa Sunnah Arafah)', 'type' => 'sunnah'];
            } elseif ($h['month'] == 12 && $h['day'] == 10) {
                $holidays[$dateStr] = ['name' => 'Hari Raya Idul Adha (10 Dzulhijjah)', 'type' => 'islam'];
            } elseif ($h['month'] == 12 && in_array($h['day'], [11, 12, 13])) {
                $holidays[$dateStr] = ['name' => 'Hari Tasyrik (' . $h['day'] . ' Dzulhijjah)', 'type' => 'sunnah'];
            }
        }

        return $holidays;
    }
}

/**
 * =====================================================================================
 * GENERATOR iCALENDAR (.ICS) RFC 5545
 * Digunakan untuk ekspor jadwal sholat, imsakiyah, dan penanggalan Islam ke Google/Apple Cal
 * =====================================================================================
 */

if (!function_exists('escapeIcsText')) {
    /**
     * Melakukan sanitasi karakter khusus pada teks iCalendar RFC 5545
     */
    function escapeIcsText($text) {
        $text = str_replace('\\', '\\\\', (string)$text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace(["\r\n", "\n", "\r"], '\n', $text);
        return $text;
    }
}

if (!function_exists('foldIcsLine')) {
    /**
     * Melipat baris teks yang lebih dari 75 karakter sesuai spesifikasi RFC 5545
     */
    function foldIcsLine($line) {
        if (strlen($line) <= 75) {
            return $line;
        }

        $folded = '';
        $first = true;
        while (strlen($line) > 0) {
            $maxLen = $first ? 75 : 74; // Sisa 74 oktet untuk continuation space
            $chunk = mb_strcut($line, 0, $maxLen, 'UTF-8');
            if ($chunk === '') {
                $chunk = substr($line, 0, $maxLen);
            }
            $line = substr($line, strlen($chunk));
            if ($first) {
                $folded .= $chunk;
                $first = false;
            } else {
                $folded .= "\r\n " . $chunk;
            }
        }
        return $folded;
    }
}

if (!function_exists('buildIcsCalendar')) {
    /**
     * Membangun berkas iCalendar lengkap (.ics) untuk jadwal sholat tahunan atau bulanan
     */
    function buildIcsCalendar($year, $month, $city, $settings = []) {
        $cityName = $city['name'] ?? 'KOTA';
        $tz = (float)($city['tz'] ?? 7.0);
        $lat = (float)($city['lat'] ?? 0.0);
        $lon = (float)($city['lon'] ?? 0.0);
        $elev = (float)($city['elev'] ?? 10.0);
        $hAdj = (int)($settings['h_adj'] ?? 0);

        // Penentuan identifier zona waktu (TZID) standar Indonesia
        if ($tz >= 8.5) {
            $tzid = 'Asia/Jayapura';
            $tzOffset = '+0900';
            $tzName = 'WIT';
        } elseif ($tz >= 7.5) {
            $tzid = 'Asia/Makassar';
            $tzOffset = '+0800';
            $tzName = 'WITA';
        } else {
            $tzid = 'Asia/Jakarta';
            $tzOffset = '+0700';
            $tzName = 'WIB';
        }

        $qibla = function_exists('getQiblaDirection') ? getQiblaDirection($lat, $lon) : ['dms' => '', 'barat_ke_utara' => ''];
        $dtstamp = gmdate('Ymd\THis\Z');

        $out = [];
        $out[] = 'BEGIN:VCALENDAR';
        $out[] = 'VERSION:2.0';
        $out[] = 'PRODID:-//Falak Kalender//Jadwal Sholat & Imsakiyah//ID';
        $out[] = 'CALSCALE:GREGORIAN';
        $out[] = 'METHOD:PUBLISH';
        $out[] = 'X-WR-CALNAME:' . escapeIcsText("Jadwal Sholat {$cityName} {$year}");
        $out[] = 'X-WR-TIMEZONE:' . $tzid;

        // Blok VTIMEZONE
        $out[] = 'BEGIN:VTIMEZONE';
        $out[] = 'TZID:' . $tzid;
        $out[] = 'X-LIC-LOCATION:' . $tzid;
        $out[] = 'BEGIN:STANDARD';
        $out[] = 'TZOFFSETFROM:' . $tzOffset;
        $out[] = 'TZOFFSETTO:' . $tzOffset;
        $out[] = 'TZNAME:' . $tzName;
        $out[] = 'DTSTART:19700101T000000';
        $out[] = 'END:STANDARD';
        $out[] = 'END:VTIMEZONE';

        // Rentang bulan
        $mStart = ($month > 0) ? (int)$month : 1;
        $mEnd = ($month > 0) ? (int)$month : 12;

        $prayers = [
            'imsak'   => ['label' => 'Waktu Imsak', 'summary' => "Waktu Imsak - {$cityName}", 'duration' => 10],
            'subuh'   => ['label' => 'Sholat Subuh', 'summary' => "Sholat Subuh - {$cityName}", 'duration' => 30],
            'terbit'  => ['label' => 'Terbit Matahari', 'summary' => "Waktu Terbit Matahari - {$cityName}", 'duration' => 15],
            'dhuha'   => ['label' => 'Waktu Dhuha', 'summary' => "Waktu Dhuha - {$cityName}", 'duration' => 30],
            'dhuhur'  => ['label' => 'Sholat Dhuhur', 'summary' => "Sholat Dhuhur - {$cityName}", 'duration' => 45],
            'ashar'   => ['label' => 'Sholat Ashar', 'summary' => "Sholat Ashar - {$cityName}", 'duration' => 45],
            'maghrib' => ['label' => 'Sholat Maghrib', 'summary' => "Sholat Maghrib - {$cityName}", 'duration' => 30],
            'isya'    => ['label' => 'Sholat Isya', 'summary' => "Sholat Isya - {$cityName}", 'duration' => 45]
        ];

        for ($m = $mStart; $m <= $mEnd; $m++) {
            $daysInM = cal_days_in_month(CAL_GREGORIAN, $m, $year);
            for ($d = 1; $d <= $daysInM; $d++) {
                $dateStr = sprintf('%04d%02d%02d', $year, $m, $d);
                $hp = getHariPasaran($year, $m, $d, $tz);
                $hijri = getHijriDate($year, $m, $d, $hAdj);
                $sholat = calculatePrayerTimes($year, $m, $d, $lat, $lon, $tz, $elev, $settings);

                foreach ($prayers as $key => $pInfo) {
                    if (empty($sholat[$key])) {
                        continue;
                    }

                    $parts = explode(':', $sholat[$key]);
                    if (count($parts) < 2) {
                        continue;
                    }
                    $pHour = (int)$parts[0];
                    $pMin = (int)$parts[1];

                    $dtStart = sprintf('%sT%02d%02d00', $dateStr, $pHour, $pMin);

                    // Hitung DTEND berdasarkan durasi
                    $endTotalMin = $pHour * 60 + $pMin + $pInfo['duration'];
                    $eHour = floor($endTotalMin / 60) % 24;
                    $eMin = $endTotalMin % 60;
                    $dtEnd = sprintf('%sT%02d%02d00', $dateStr, (int)$eHour, (int)$eMin);

                    $uid = sprintf('prayer-%s-%s-%s@falak-kalender', $key, preg_replace('/[^A-Za-z0-9]/', '', $cityName), $dateStr);

                    $descText = "Penanggalan: {$hijri['formatted']} ({$hp['lengkap']})\n"
                              . "{$pInfo['label']} untuk wilayah {$cityName} dan sekitarnya.\n"
                              . "Arah Kiblat: {$qibla['dms']} ({$qibla['barat_ke_utara']})";

                    $out[] = 'BEGIN:VEVENT';
                    $out[] = 'UID:' . $uid;
                    $out[] = 'DTSTAMP:' . $dtstamp;
                    $out[] = "DTSTART;TZID={$tzid}:" . $dtStart;
                    $out[] = "DTEND;TZID={$tzid}:" . $dtEnd;
                    $out[] = 'SUMMARY:' . escapeIcsText($pInfo['summary']);
                    $out[] = 'DESCRIPTION:' . escapeIcsText($descText);
                    $out[] = 'LOCATION:' . escapeIcsText("{$cityName}, Indonesia");
                    $out[] = 'STATUS:CONFIRMED';
                    $out[] = 'TRANSP:OPAQUE';
                    $out[] = 'END:VEVENT';
                }
            }
        }

        $out[] = 'END:VCALENDAR';

        // Terapkan line folding dan CRLF
        $icsContent = '';
        foreach ($out as $rawLine) {
            $icsContent .= foldIcsLine($rawLine) . "\r\n";
        }

        return $icsContent;
    }
}

/**
 * =====================================================================================
 * LOGIKA STATUS WAKTU SHOLAT AKTIF & COUNTDOWN WIDGET
 * =====================================================================================
 */

if (!function_exists('getPrayerStatusInfo')) {
    /**
     * Menentukan sholat yang aktif saat ini, sholat berikutnya, serta sisa detik countdown
     */
    function getPrayerStatusInfo($currentTimeStr, $prayerTimesToday, $prayerTimesTomorrow = []) {
        $toSeconds = function($timeStr) {
            $parts = explode(':', (string)$timeStr);
            $h = isset($parts[0]) ? (int)$parts[0] : 0;
            $m = isset($parts[1]) ? (int)$parts[1] : 0;
            $s = isset($parts[2]) ? (int)$parts[2] : 0;
            return $h * 3600 + $m * 60 + $s;
        };

        $curSec = $toSeconds($currentTimeStr);

        $labels = [
            'imsak'   => 'Waktu Imsak',
            'subuh'   => 'Sholat Subuh',
            'terbit'  => 'Terbit Matahari',
            'dhuha'   => 'Waktu Dhuha',
            'dhuhur'  => 'Sholat Dhuhur',
            'ashar'   => 'Sholat Ashar',
            'maghrib' => 'Sholat Maghrib',
            'isya'    => 'Sholat Isya'
        ];

        $today = [];
        foreach ($labels as $k => $label) {
            $today[$k] = isset($prayerTimesToday[$k]) ? $toSeconds($prayerTimesToday[$k]) : 0;
        }

        $nextImsakTomorrow = isset($prayerTimesTomorrow['imsak']) ? $toSeconds($prayerTimesTomorrow['imsak']) : $today['imsak'];

        $currentKey = '';
        $nextKey = '';
        $diffSec = 0;

        if ($curSec < $today['imsak']) {
            $currentKey = 'isya';
            $nextKey = 'imsak';
            $diffSec = $today['imsak'] - $curSec;
        } elseif ($curSec < $today['subuh']) {
            $currentKey = 'imsak';
            $nextKey = 'subuh';
            $diffSec = $today['subuh'] - $curSec;
        } elseif ($curSec < $today['terbit']) {
            $currentKey = 'subuh';
            $nextKey = 'terbit';
            $diffSec = $today['terbit'] - $curSec;
        } elseif ($curSec < $today['dhuha']) {
            $currentKey = 'terbit';
            $nextKey = 'dhuha';
            $diffSec = $today['dhuha'] - $curSec;
        } elseif ($curSec < $today['dhuhur']) {
            $currentKey = 'dhuha';
            $nextKey = 'dhuhur';
            $diffSec = $today['dhuhur'] - $curSec;
        } elseif ($curSec < $today['ashar']) {
            $currentKey = 'dhuhur';
            $nextKey = 'ashar';
            $diffSec = $today['ashar'] - $curSec;
        } elseif ($curSec < $today['maghrib']) {
            $currentKey = 'ashar';
            $nextKey = 'maghrib';
            $diffSec = $today['maghrib'] - $curSec;
        } elseif ($curSec < $today['isya']) {
            $currentKey = 'maghrib';
            $nextKey = 'isya';
            $diffSec = $today['isya'] - $curSec;
        } else {
            // Setelah Isya hingga tengah malam (rollover ke Imsak esok hari)
            $currentKey = 'isya';
            $nextKey = 'imsak';
            $diffSec = (86400 - $curSec) + $nextImsakTomorrow;
        }

        $cH = floor($diffSec / 3600);
        $cM = floor(($diffSec % 3600) / 60);
        $cS = $diffSec % 60;
        $formatted = sprintf('%02d:%02d:%02d', $cH, $cM, $cS);

        return [
            'current_key'        => $currentKey,
            'current_name'       => $labels[$currentKey] ?? $currentKey,
            'next_key'           => $nextKey,
            'next_name'          => $labels[$nextKey] ?? $nextKey,
            'countdown_seconds'  => (int)$diffSec,
            'countdown_formatted'=> $formatted
        ];
    }
}


/**
 * =====================================================================================
 * FUNGSI PERHITUNGAN PENANGGALAN CINA (LUNISOLAR IMLEK, SHIO & ELEMEN)
 * Menggunakan lookup table bitmask mandiri 1900–2100 tanpa library eksternal.
 * =====================================================================================
 */

if (!function_exists('getChineseLunarInfo')) {
    /**
     * Data bitmask astronomis penanggalan lunisolar Cina tahun 1900–2100 (201 entri).
     * Format per entri (16-20 bit):
     * - Bit 0–3: Index bulan kabisat (1–12), 0 jika tanpa bulan kabisat
     * - Bit 4–15: Jumlah hari 12 bulan reguler (1 = 30 hari, 0 = 29 hari)
     * - Bit 16: Jumlah hari bulan kabisat (1 = 30 hari, 0 = 29 hari)
     */
    function getChineseLunarInfo() {
        static $lunarInfo = [
            // 1900 - 1909
            0x04bd8, 0x04ae0, 0x0a570, 0x054d5, 0x0d260, 0x0d950, 0x16554, 0x056a0, 0x09ad0, 0x055d2,
            // 1910 - 1919
            0x04ae0, 0x0a5b6, 0x0a4d0, 0x0d250, 0x1d255, 0x0b540, 0x0d6a0, 0x0ada2, 0x095b0, 0x14977,
            // 1920 - 1929
            0x04970, 0x0a4b0, 0x0b4b5, 0x06a50, 0x06d40, 0x1ab54, 0x02b60, 0x09570, 0x052f2, 0x04970,
            // 1930 - 1939
            0x06566, 0x0d4a0, 0x0ea50, 0x16a95, 0x05ad0, 0x02b60, 0x186e3, 0x092e0, 0x1c8d7, 0x0c950,
            // 1940 - 1949
            0x0d4a0, 0x1d8a6, 0x0b550, 0x056a0, 0x1a5b4, 0x025d0, 0x092d0, 0x0d2b2, 0x0a950, 0x0b557,
            // 1950 - 1959
            0x06ca0, 0x0b550, 0x15355, 0x04da0, 0x0a5b0, 0x14573, 0x052b0, 0x0a9a8, 0x0e950, 0x06aa0,
            // 1960 - 1969
            0x0aea6, 0x0ab50, 0x04b60, 0x0aae4, 0x0a570, 0x05260, 0x0f263, 0x0d950, 0x05b57, 0x056a0,
            // 1970 - 1979
            0x096d0, 0x04dd5, 0x04ad0, 0x0a4d0, 0x0d4d4, 0x0d250, 0x0d558, 0x0b540, 0x0b6a0, 0x195a6,
            // 1980 - 1989
            0x095b0, 0x049b0, 0x0a974, 0x0a4b0, 0x0b27a, 0x06a50, 0x06d40, 0x0af46, 0x0ab60, 0x09570,
            // 1990 - 1999
            0x04af5, 0x04970, 0x064b0, 0x074a3, 0x0ea50, 0x06b58, 0x05ac0, 0x0ab60, 0x096d5, 0x092e0,
            // 2000 - 2009
            0x0c960, 0x0d954, 0x0d4a0, 0x0da50, 0x07552, 0x056a0, 0x0abb7, 0x025d0, 0x092d0, 0x0cab5,
            // 2010 - 2019
            0x0a950, 0x0b4a0, 0x0baa4, 0x0ad50, 0x055d9, 0x04ba0, 0x0a5b0, 0x15176, 0x052b0, 0x0a930,
            // 2020 - 2029
            0x07954, 0x06aa0, 0x0ad50, 0x05b52, 0x04b60, 0x0a6e6, 0x0a4e0, 0x0d260, 0x0ea65, 0x0d530,
            // 2030 - 2039
            0x05aa0, 0x076a3, 0x096d0, 0x04afb, 0x04ad0, 0x0a4d0, 0x1d0b6, 0x0d250, 0x0d520, 0x0dd45,
            // 2040 - 2049
            0x0b5a0, 0x056d0, 0x055b2, 0x049b0, 0x0a577, 0x0a4b0, 0x0aa50, 0x1b255, 0x06d20, 0x0ada0,
            // 2050 - 2059
            0x14b63, 0x09370, 0x049f8, 0x04970, 0x064b0, 0x168a6, 0x0ea50, 0x06aa0, 0x1a6c4, 0x0aae0,
            // 2060 - 2069
            0x092e0, 0x0d2e3, 0x0c960, 0x0d557, 0x0d4a0, 0x0da50, 0x05d55, 0x056a0, 0x0a6d0, 0x055d4,
            // 2070 - 2079
            0x052d0, 0x0a9b8, 0x0a950, 0x0b4a0, 0x0b6a6, 0x0ad50, 0x055a0, 0x0aba4, 0x0a5b0, 0x052b0,
            // 2080 - 2089
            0x0b273, 0x06930, 0x07337, 0x06aa0, 0x0ad50, 0x14b55, 0x04b60, 0x0a570, 0x054e4, 0x0d160,
            // 2090 - 2099
            0x0e968, 0x0d520, 0x0daa0, 0x16aa6, 0x056d0, 0x04ae0, 0x0a9d4, 0x0a2d0, 0x0d150, 0x0f252,
            // 2100
            0x0d520
        ];
        return $lunarInfo;
    }
}

/**
 * Menghitung Shio, Elemen, dan Nama Siklus Jiazi 60-tahunan.
 *
 * @param int $lunarYear Tahun Imlek (Kongzi era, misal 2577, atau ekuivalen Masehi misal 2026)
 * @return array
 */
if (!function_exists('getChineseZodiacYear')) {
    function getChineseZodiacYear($lunarYear) {
        $solarYear = ($lunarYear > 2200) ? ($lunarYear - 551) : $lunarYear;
        $offset = $solarYear - 4;
        $stemIdx = ($offset % 10 + 10) % 10;
        $branchIdx = ($offset % 12 + 12) % 12;

        $stems = ['Jia', 'Yi', 'Bing', 'Ding', 'Wu', 'Ji', 'Geng', 'Xin', 'Ren', 'Gui'];
        $branches = ['Tikus', 'Kerbau', 'Macan', 'Kelinci', 'Naga', 'Ular', 'Kuda', 'Kambing', 'Monyet', 'Ayam', 'Anjing', 'Babi'];
        $branchNames = ['Zi', 'Chou', 'Yin', 'Mao', 'Chen', 'Si', 'Wu', 'Wei', 'Shen', 'You', 'Xu', 'Hai'];
        $elements = ['Kayu', 'Kayu', 'Api', 'Api', 'Tanah', 'Tanah', 'Logam', 'Logam', 'Air', 'Air'];

        $shio = $branches[$branchIdx];
        $elemen = $elements[$stemIdx];
        $jiazi = $stems[$stemIdx] . ' ' . $branchNames[$branchIdx];
        $namaTahun = "{$shio} {$elemen} ({$jiazi})";

        return [
            'shio'        => $shio,
            'elemen'      => $elemen,
            'nama_tahun'  => $namaTahun,
            'stem'        => $stems[$stemIdx],
            'branch'      => $branches[$branchIdx],
            'stem_index'  => $stemIdx,
            'branch_index'=> $branchIdx
        ];
    }
}

/**
 * Konversi Penanggalan Masehi ke Lunisolar Cina (Imlek).
 * Mendukung rentang tahun 1900 hingga 2100.
 *
 * @param int $year Tahun Masehi (1900-2100)
 * @param int $month Bulan Masehi (1-12)
 * @param int $day Tanggal Masehi (1-31)
 * @return array|null
 */
if (!function_exists('getChineseLunisolarDate')) {
    function getChineseLunisolarDate($year, $month, $day) {
        if ($year < 1900 || $year > 2100) {
            return null;
        }

        $lunarInfo = getChineseLunarInfo();

        try {
            $base = new DateTime('1900-01-31', new DateTimeZone('UTC'));
            $target = new DateTime(sprintf('%04d-%02d-%02d', $year, $month, $day), new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return null;
        }

        $diff = $base->diff($target);
        $offset = (int)$diff->format('%r%a');

        if ($offset < 0) {
            return null;
        }

        $lYearDays = function($info) {
            $sum = 348;
            for ($i = 0x8000; $i > 0x8; $i >>= 1) {
                $sum += ($info & $i) ? 1 : 0;
            }
            if ($info & 0xf) {
                $sum += ($info & 0x10000) ? 30 : 29;
            }
            return $sum;
        };

        $lunarYear = 1900;
        $daysInYear = 0;
        for ($i = 1900; $i <= 2100 && $offset > 0; $i++) {
            $daysInYear = $lYearDays($lunarInfo[$i - 1900]);
            $offset -= $daysInYear;
            $lunarYear++;
        }

        if ($offset < 0) {
            $offset += $daysInYear;
            $lunarYear--;
        }

        if ($lunarYear > 2100) {
            return null;
        }

        $info = $lunarInfo[$lunarYear - 1900];
        $leap = $info & 0xf;
        $isLeap = false;
        $lunarMonth = 1;

        for ($m = 1; $m <= 12 && $offset >= 0; $m++) {
            // Jumlah hari bulan reguler $m
            $daysInMonth = ($info & (0x10000 >> $m)) ? 30 : 29;
            if ($offset < $daysInMonth) {
                $lunarMonth = $m;
                $isLeap = false;
                break;
            }
            $offset -= $daysInMonth;

            // Jika bulan $m memiliki bulan kabisat setelahnya
            if ($leap === $m) {
                $daysInLeap = ($info & 0x10000) ? 30 : 29;
                if ($offset < $daysInLeap) {
                    $lunarMonth = $m;
                    $isLeap = true;
                    break;
                }
                $offset -= $daysInLeap;
            }
        }

        $lunarDay = $offset + 1;
        $kongziYear = $lunarYear + 551;

        $monthNames = [
            1 => 'Cia Gwe', 2 => 'Ji Gwe', 3 => 'Sa Gwe', 4 => 'Si Gwe',
            5 => 'Go Gwe', 6 => 'Lak Gwe', 7 => 'Tjit Gwe', 8 => 'Peh Gwe',
            9 => 'Kauw Gwe', 10 => 'Cap Gwe', 11 => 'Cap It Gwe', 12 => 'Cap Ji Gwe'
        ];

        $dayNames = [
            1 => 'Ce It', 2 => 'Ce Ji', 3 => 'Ce Sa', 4 => 'Ce Si', 5 => 'Ce Go',
            6 => 'Ce Lak', 7 => 'Ce Tjit', 8 => 'Ce Peh', 9 => 'Ce Kauw', 10 => 'Ce Cap',
            11 => 'Cap It', 12 => 'Cap Ji', 13 => 'Cap Sa', 14 => 'Cap Si', 15 => 'Cap Go',
            16 => 'Cap Lak', 17 => 'Cap Tjit', 18 => 'Cap Peh', 19 => 'Cap Kauw', 20 => 'Ji Cap',
            21 => 'Ji It', 22 => 'Ji Ji', 23 => 'Ji Sa', 24 => 'Ji Si', 25 => 'Ji Go',
            26 => 'Ji Lak', 27 => 'Ji Tjit', 28 => 'Ji Peh', 29 => 'Ji Kauw', 30 => 'Sa Cap'
        ];

        $zodiac = getChineseZodiacYear($kongziYear);
        $mName = ($isLeap ? 'Kabisat ' : '') . ($monthNames[$lunarMonth] ?? "Bulan {$lunarMonth}");
        $dName = $dayNames[$lunarDay] ?? (string)$lunarDay;
        $formatted = "{$lunarDay} {$mName} {$kongziYear}";

        return [
            'year'             => $kongziYear,
            'solar_lunar_year' => $lunarYear,
            'month'            => $lunarMonth,
            'day'              => $lunarDay,
            'month_name'       => $mName,
            'day_name'         => $dName,
            'is_leap'          => $isLeap,
            'shio'             => $zodiac['shio'],
            'elemen'           => $zodiac['elemen'],
            'nama_tahun'       => $zodiac['nama_tahun'],
            'formatted'        => $formatted
        ];
    }
}
