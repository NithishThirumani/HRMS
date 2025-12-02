// Smooth scroll to appraisals section
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash === '#appraisals') {
        const element = document.querySelector(hash);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});