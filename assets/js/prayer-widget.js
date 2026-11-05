/**
 * Prayer Widget - Real-Time Digital Clock & Countdown Timer
 * Pure Vanilla JavaScript (Zero Dependencies)
 */

(function () {
    'use strict';

    function padZero(num) {
        return (num < 10 ? '0' : '') + num;
    }

    function timeToSeconds(timeStr) {
        if (!timeStr) return 0;
        const parts = timeStr.split(':');
        const h = parseInt(parts[0], 10) || 0;
        const m = parseInt(parts[1], 10) || 0;
        const s = parseInt(parts[2], 10) || 0;
        return h * 3600 + m * 60 + s;
    }

    function secondsToTime(totalSec) {
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        const s = totalSec % 60;
        return padZero(h) + ':' + padZero(m) + ':' + padZero(s);
    }

    const PRAYER_LABELS = {
        imsak: 'Waktu Imsak',
        subuh: 'Sholat Subuh',
        terbit: 'Terbit Matahari',
        dhuha: 'Waktu Dhuha',
        dhuhur: 'Sholat Dhuhur',
        ashar: 'Sholat Ashar',
        maghrib: 'Sholat Maghrib',
        isya: 'Sholat Isya'
    };

    function initPrayerWidget() {
        const widget = document.getElementById('prayer-widget');
        if (!widget) return;

        const tzOffset = parseFloat(widget.getAttribute('data-tz')) || 7.0;
        let todayPrayers = {};
        let tomorrowPrayers = {};

        try {
            todayPrayers = JSON.parse(widget.getAttribute('data-prayers-today') || '{}');
            tomorrowPrayers = JSON.parse(widget.getAttribute('data-prayers-tomorrow') || '{}');
        } catch (e) {
            console.error('Prayer widget data parse error:', e);
            return;
        }

        const clockEl = document.getElementById('widget-clock');
        const tzNameEl = document.getElementById('widget-tzname');
        const currentPrayerEl = document.getElementById('widget-current-prayer');
        const nextPrayerEl = document.getElementById('widget-next-prayer');
        const countdownEl = document.getElementById('widget-countdown');
        const dateEl = document.getElementById('widget-date');

        function updateWidget() {
            // Dapatkan waktu saat ini disesuaikan ke timezone target
            const now = new Date();
            const utcTime = now.getTime() + (now.getTimezoneOffset() * 60000);
            const targetDate = new Date(utcTime + (3600000 * tzOffset));

            const curHours = targetDate.getHours();
            const curMinutes = targetDate.getMinutes();
            const curSeconds = targetDate.getSeconds();
            const curTotalSec = curHours * 3600 + curMinutes * 60 + curSeconds;

            // Perbarui tampilan jam digital
            if (clockEl) {
                clockEl.textContent = padZero(curHours) + ':' + padZero(curMinutes) + ':' + padZero(curSeconds);
            }

            // Perbarui tanggal lokal jika ada
            if (dateEl) {
                const days = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                dateEl.textContent = days[targetDate.getDay()] + ', ' + targetDate.getDate() + ' ' + months[targetDate.getMonth()] + ' ' + targetDate.getFullYear();
            }

            // Hitung status sholat aktif dan hitung mundur
            const imsakSec = timeToSeconds(todayPrayers.imsak);
            const subuhSec = timeToSeconds(todayPrayers.subuh);
            const terbitSec = timeToSeconds(todayPrayers.terbit);
            const dhuhaSec = timeToSeconds(todayPrayers.dhuha);
            const dhuhurSec = timeToSeconds(todayPrayers.dhuhur);
            const asharSec = timeToSeconds(todayPrayers.ashar);
            const maghribSec = timeToSeconds(todayPrayers.maghrib);
            const isyaSec = timeToSeconds(todayPrayers.isya);

            const nextImsakTomorrowSec = timeToSeconds(tomorrowPrayers.imsak) || imsakSec;

            let currentKey = 'isya';
            let nextKey = 'imsak';
            let diffSec = 0;

            if (curTotalSec < imsakSec) {
                currentKey = 'isya';
                nextKey = 'imsak';
                diffSec = imsakSec - curTotalSec;
            } else if (curTotalSec < subuhSec) {
                currentKey = 'imsak';
                nextKey = 'subuh';
                diffSec = subuhSec - curTotalSec;
            } else if (curTotalSec < terbitSec) {
                currentKey = 'subuh';
                nextKey = 'terbit';
                diffSec = terbitSec - curTotalSec;
            } else if (curTotalSec < dhuhaSec) {
                currentKey = 'terbit';
                nextKey = 'dhuha';
                diffSec = dhuhaSec - curTotalSec;
            } else if (curTotalSec < dhuhurSec) {
                currentKey = 'dhuha';
                nextKey = 'dhuhur';
                diffSec = dhuhurSec - curTotalSec;
            } else if (curTotalSec < asharSec) {
                currentKey = 'dhuhur';
                nextKey = 'ashar';
                diffSec = asharSec - curTotalSec;
            } else if (curTotalSec < maghribSec) {
                currentKey = 'ashar';
                nextKey = 'maghrib';
                diffSec = maghribSec - curTotalSec;
            } else if (curTotalSec < isyaSec) {
                currentKey = 'maghrib';
                nextKey = 'isya';
                diffSec = isyaSec - curTotalSec;
            } else {
                currentKey = 'isya';
                nextKey = 'imsak';
                diffSec = (86400 - curTotalSec) + nextImsakTomorrowSec;
            }

            if (currentPrayerEl) {
                currentPrayerEl.textContent = PRAYER_LABELS[currentKey] || currentKey;
            }

            if (nextPrayerEl) {
                nextPrayerEl.textContent = PRAYER_LABELS[nextKey] || nextKey;
            }

            if (countdownEl) {
                countdownEl.textContent = secondsToTime(diffSec);
            }
        }

        updateWidget();
        setInterval(updateWidget, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPrayerWidget);
    } else {
        initPrayerWidget();
    }
})();
