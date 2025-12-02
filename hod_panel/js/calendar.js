function updateDateTime() {
    var now = moment();
    var hijriDate = moment().format('iYYYY/iM/iD');
    var hijriDay = moment().format('dddd');
    var hijriMonth = moment().format('iMMMM');
    var hijriYear = moment().format('iYYYY');

    document.getElementById('datetime').innerHTML = 
        now.format('dddd, MMMM D, YYYY [at] hh:mm:ss A') + 
        ' | ' + 
        hijriDay + ', ' + hijriMonth + ' ' + moment().format('iD') + ', ' + hijriYear + ' AH';
}

setInterval(updateDateTime, 1000);
document.addEventListener('DOMContentLoaded', updateDateTime);