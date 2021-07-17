var BotController = {
    betUrl: null,
    canBet: false,
    hasOrder: false,
    bet: function () {
        sendRequest({
            url: BotController.betUrl,
            type: 'POST',
            data: {},
            beforeSend: function () {
            },
            complete: function () {
            }
        }, function (response) {
            var betOrder = response.data;

            if (!response.status) {
                window.location.href = betOrder.url;
                return false;
            }

            // update last result
            var firstChild = $('.bet-result tr:first-child'),
                lastResult = (betOrder.last_result == 1) ? '<span class="fw-bold text-success">Thắng</span>' : '<span class="fw-bold text-danger">Thua</span>';
            firstChild.length > 0 ? firstChild.find('.bet-order-result').empty().html(lastResult) : null;

            // add new order
            var newOrder = "<tr>\n" +
                '<td>' + betOrder.time + '</td>\n' +
                '<td>' + betOrder.method + '</td>\n' +
                '<td>' + BotController.getBetTypeText(betOrder.type) + '</td>\n' +
                '<td class="text-info"><span class="fas fa-dollar-sign"></span><span class="fw-bold">' + betOrder.amount + '</span></td>\n' +
                '<td class="fw-bold bet-order-result">Đang đợi</td>\n' +
                '</tr>';
            $('.bet-result').prepend(newOrder);

            // update amount
            $('.current-amount').empty().text(betOrder.current_amount);
        });
    },
    getBetTypeText: function (betType) {
        return (betType == 'UP' ? '<span class="fw-bold">Mua</span>' : '<span class="fw-bold">Bán</span>')
    },
    showTime: function () {
        var date = new Date(),
            s = date.getSeconds();

        // bet
        if (2 < s && s < 30) {
            if (BotController.hasOrder === false && BotController.canBet === 'true') {
                BotController.bet();
                BotController.hasOrder = true;
            }
        } else {
            BotController.hasOrder = false;
        }

        // show title
        document.getElementById('clock-title').innerText = (s < 30) ? 'Có thể đặt lệnh' : 'Đang chờ kết quả';
        document.getElementById('clock-title').textContent = (s < 30) ? 'Có thể đặt lệnh' : 'Đang chờ kết quả';

        // show time
        s = (s > 30) ? (60 - s) : (30 - s);
        s = (s == 0) ? "30" : s;
        s = (s < 10) ? "0" + s : s;
        document.getElementById('clock-countdown').innerText = s;
        document.getElementById('clock-countdown').textContent = s;
        setTimeout(BotController.showTime, 1000);
    }
};
