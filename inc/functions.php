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
