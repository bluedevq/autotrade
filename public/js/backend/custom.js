var BotController = {
    betUrl: null,
    canBet: false,
    hasOrder: false,
    profit: 0,
    volume: 0,
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
                if (betOrder.url) {
                    window.location.href = betOrder.url;
                }
                return false;
            }

            // update last result
            var listClosedOrders = betOrder.closed_orders;
            if (typeof listClosedOrders != 'undefined') {
                BotController.updateLastOrders(listClosedOrders);
            }

            // update new orders
            var listOpenOrders = betOrder.open_orders;
            if (typeof listOpenOrders != 'undefined') {
                BotController.updateNewOrders(listOpenOrders);
            }

            // update amount
            $('.current-amount').empty().text(betOrder.current_amount);
        });
    },
    updateLastOrders: function (listClosedOrders) {
        var childrens = $('.bet-result tr');
        if (typeof childrens !== 'undefined' && childrens.length > 0) {
            for (var i = 0; i < listClosedOrders.length; i++) {
                // update status
                var result = (listClosedOrders[i].result === 'WIN') ? '<span class="fw-bold text-success">Thắng</span>' : '<span class="fw-bold text-danger">Thua</span>';
                $(childrens[i]).length > 0 ? $(childrens[i]).find('.bet-order-result').empty().html(result) : null;

                // update profit
                BotController.profit += listClosedOrders[i].win_amount;

                // update volume
                BotController.volume += listClosedOrders[i].amount;
            }
            $('.profit').empty().html(BotController.profit > 0 ? ('<span class="text-success">' + BotController.profit + '</span>') : ('<span class="text-danger">' + BotController.profit + '</span>'));
            $('.volume').empty().text(BotController.volume);
        }
    },
    updateNewOrders: function (listOpenOrders) {
        for (var i = 0; i < listOpenOrders.length; i++) {
            var dateTime = new Date(listOpenOrders[i].time);
            var newOrder = "<tr>\n" +
                '<td>' + BotController.pad(dateTime.getHours()) + ':' + BotController.pad(dateTime.getMinutes()) + '</td>\n' +
                '<td>' + listOpenOrders[i].method + '</td>\n' +
                '<td>' + BotController.getBetTypeText(listOpenOrders[i].type) + '</td>\n' +
                '<td class="text-info"><span class="fas fa-dollar-sign"></span><span class="fw-bold">' + listOpenOrders[i].amount + '</span></td>\n' +
                '<td class="fw-bold bet-order-result">Đang đợi</td>\n' +
                '</tr>';
            $('.bet-result').prepend(newOrder);
        }
    },
    pad: function (t) {
        var st = "" + t;
        while (st.length < 2) {
            st = "0" + st;
        }

        return st;
    },
    getBetTypeText: function (betType) {
        return (betType == 'UP' ? '<span class="fw-bold">Mua</span>' : '<span class="fw-bold">Bán</span>')
    },
    showTime: function () {
        var date = new Date(),
            s = date.getSeconds();

        // bet
        if (0 < s && s < 30) {
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
