function updateDateTime() {
    // Update Gregorian DateTime with Dubai timezone
    const now = new Date();
    const dubaiOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Dubai'
    };
    document.getElementById('gregorianDateTime').textContent = now.toLocaleString('en-US', dubaiOptions);

    // Convert to Hijri with Dubai timezone
    const hijriDate = new Intl.DateTimeFormat('en-US-u-ca-islamic', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        weekday: 'long',
        timeZone: 'Asia/Dubai'
    }).format(now);
    document.getElementById('hijriDateTime').textContent = hijriDate;
}

// Update every second
setInterval(updateDateTime, 1000);
// Initial call
updateDateTime();