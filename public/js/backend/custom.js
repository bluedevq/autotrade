let BotController = {
    betUrl: null,
    canBet: false,
    hasOrder: false,
    profit: 0,
    volume: 0,
    newOrderStatus: 'Đang đợi',
    winStatus: 'Thắng',
    loseStatus: 'Thua',
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
            let betOrder = response.data;

            if (!response.status) {
                if (betOrder.url) {
                    window.location.href = betOrder.url;
                }
                return false;
            }

            // update last result
            let listClosedOrders = betOrder.closed_orders;
            if (typeof listClosedOrders != 'undefined') {
                BotController.updateLastOrders(listClosedOrders);
            }

            // update new orders
            let listOpenOrders = betOrder.open_orders;
            if (typeof listOpenOrders != 'undefined') {
                BotController.updateNewOrders(listOpenOrders);
            }

            // update amount
            $('.current-amount').empty().text(betOrder.current_amount);
        });
    },
    updateLastOrders: function (listClosedOrders) {
        let childrens = $('.bet-result tr');
        if (typeof childrens !== 'undefined' && childrens.length > 0) {
            for (let i = 0; i < listClosedOrders.length; i++) {
                let timeOrder = $(childrens[i]).find('.time-order').text(),
                    lastTimeOrder = listClosedOrders[i].time,
                    lastOrderStatus = $(childrens[i]).find('.bet-order-result').text(),
                    result = (listClosedOrders[i].result === 'WIN') ? '<span class="fw-bold text-success">' + BotController.winStatus + '</span>' : '<span class="fw-bold text-danger">' + BotController.loseStatus + '</span>';

                lastTimeOrder = new Date(lastTimeOrder);
                lastTimeOrder = BotController.pad(lastTimeOrder.getHours()) + ':' + BotController.pad(lastTimeOrder.getMinutes());
                if (timeOrder == lastTimeOrder && lastOrderStatus == BotController.newOrderStatus) {
                    // update status
                    $(childrens[i]).length > 0 ? $(childrens[i]).find('.bet-order-result').empty().html(result) : null;
                    // update profit
                    BotController.profit += listClosedOrders[i].win_amount;
                }
            }
            $('.profit').empty().html(BotController.profit > 0 ? ('<span class="text-success">' + BotController.profit + '</span>') : ('<span class="text-danger">' + BotController.profit + '</span>'));
        }
    },
    updateNewOrders: function (listOpenOrders) {
        for (let i = 0; i < listOpenOrders.length; i++) {
            let dateTime = new Date(listOpenOrders[i].time),
                newOrder = "<tr>\n" +
                    '<td class="time-order">' + BotController.pad(dateTime.getHours()) + ':' + BotController.pad(dateTime.getMinutes()) + '</td>\n' +
                    '<td>' + listOpenOrders[i].method + '</td>\n' +
                    '<td>' + BotController.getBetTypeText(listOpenOrders[i].type) + '</td>\n' +
                    '<td class="text-info"><span class="fas fa-dollar-sign"></span><span class="fw-bold">' + listOpenOrders[i].amount + '</span></td>\n' +
                    '<td class="fw-bold bet-order-result">' + BotController.newOrderStatus + '</td>\n' +
                    '</tr>';

            $('.bet-result').prepend(newOrder);

            // update volume
            BotController.volume += listOpenOrders[i].amount;
        }
        $('.volume').empty().text(BotController.volume);
    },
    pad: function (t) {
        let st = "" + t;
        while (st.length < 2) {
            st = "0" + st;
        }

        return st;
    },
    getBetTypeText: function (betType) {
        return (betType == 'UP' ? '<span class="fw-bold">Mua</span>' : '<span class="fw-bold">Bán</span>')
    },
    showTime: function () {
        let date = new Date(),
            s = date.getSeconds();

        // bet
        if (0 < s && s < 30) {
            let balance = $('.account-balance:not(".hide") .current-amount').text();
            if (balance <= 0) {
                BotController.hasOrder = true;
            }
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
    },
    changeAccountBalance: function (select) {
        let accountType = $(select).val();
        if (accountType == 1) {
            $('.demo-balance').removeClass('hide');
            $('.live-balance').addClass('hide');
        } else {
            $('.demo-balance').addClass('hide');
            $('.live-balance').removeClass('hide');
        }
    },
};
