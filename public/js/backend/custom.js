function showTime() {
    var date = new Date(),
        s = date.getSeconds();

    // show title
    document.getElementById('clock-title').innerText = (s < 30) ? 'Có thể đặt lệnh' : 'Đang chờ kết quả';
    document.getElementById('clock-title').textContent = (s < 30) ? 'Có thể đặt lệnh' : 'Đang chờ kết quả';

    // show time
    s = (s > 30) ? (60 - s) : (30 - s);
    s = (s == 30) ? "0" : s;
    s = (s < 10) ? "0" + s : s;
    document.getElementById('clock-countdown').innerText = s;
    document.getElementById('clock-countdown').textContent = s;
    setTimeout(showTime, 1000);
}
