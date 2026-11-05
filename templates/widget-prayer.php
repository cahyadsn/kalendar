<!-- Widget Waktu Sholat Real-Time & Countdown -->
<div id="prayer-widget" class="prayer-widget-card no-print" 
     data-tz="<?= (float)$activeCity['tz'] ?>" 
     data-city="<?= htmlencode($activeCity['name'] ?? '') ?>"
     data-prayers-today="<?= htmlspecialchars(json_encode($todayPrayers), ENT_QUOTES, 'UTF-8') ?>"
     data-prayers-tomorrow="<?= htmlspecialchars(json_encode($tomorrowPrayers), ENT_QUOTES, 'UTF-8') ?>">
    <div class="widget-col widget-time-box">
        <span class="widget-label">🕒 Waktu Lokal (<?= htmlencode($activeCity['name'] ?? '') ?>)</span>
        <div class="widget-clock" id="widget-clock"><?= date('H:i:s') ?></div>
        <div class="widget-sub" id="widget-date"><?= date('d M Y') ?> (GMT+<?= (float)$activeCity['tz'] ?>)</div>
    </div>
    <div class="widget-col widget-current-box">
        <span class="widget-label">🕌 Sholat Aktif Saat Ini</span>
        <div class="widget-status-badge" id="widget-current-prayer"><?= htmlencode($initialPrayerStatus['current_name'] ?? '') ?></div>
        <div class="widget-sub">Waktu ibadah sedang berlangsung</div>
    </div>
    <div class="widget-col widget-countdown-box">
        <span class="widget-label">⏳ Menuju <strong id="widget-next-prayer"><?= htmlencode($initialPrayerStatus['next_name'] ?? '') ?></strong></span>
        <div class="widget-countdown" id="widget-countdown"><?= htmlencode($initialPrayerStatus['countdown_formatted'] ?? '') ?></div>
        <div class="widget-sub">Hitung mundur ke waktu adzan berikutnya</div>
    </div>
</div>
