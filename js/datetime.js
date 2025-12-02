function updateDateTime() {
    // Update Gregorian DateTime
    const now = new Date();
    const gregorianDate = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const time = now.toLocaleTimeString('en-US');
    document.getElementById('gregorianDateTime').textContent = `${gregorianDate} ${time}`;

    // Update Hijri DateTime
    const hijriDate = new Intl.DateTimeFormat('en-US-u-ca-islamic', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(now);
    document.getElementById('hijriDateTime').textContent = hijriDate;
}

// Update every second
setInterval(updateDateTime, 1000);
// Initial call
updateDateTime();