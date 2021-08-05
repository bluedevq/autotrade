$(document).ready(function () {
    $('form').on('submit', function (e) {
        e.preventDefault();
        $(this).attr('show-loading') == 1 ? showLoading() : null;
        $(this).off('submit').submit();
    });

    $('.navbar-toggler').on('click', function () {
        if ($('.navbar-collapse').hasClass('not-active')) {
            $('.navbar-collapse').removeClass('not-active').slideDown(400);
        } else {
            $('.navbar-collapse').addClass('not-active').slideUp(400);
        }
    });

    $('#form-method').on('hide.bs.modal', function (e) {
        BotController.resetForm();
    })
});

let BotController = {
    options: {
        isRunning: false,
        hasOrder: false,
    },
    config: {
        url: {
            login: null,
            bet: null,
            research: null,
        },
        orderStatus: {
            new: 'Đang đợi',
            win: 'Thắng',
            lose: 'Thua',
        },
        clockTitle: {
            bet: 'Có thể đặt lệnh',
            wait: 'Đang chờ kết quả',
        },
        orderTypeText: {
            up: 'T',
            down: 'G',
        },
        moveMoneyType: {
            walletToTrade: 1,
            tradeToWallet: 2
        },
        startAt: null,
        profit: 0,
        volume: 0,
    },
    notification: {
        success: [],
        errors: [],
    },
    showHidePassword: function (button) {
        let passwordInput = $(button).parent('.input-group').find('input#password');
        console.log($(passwordInput).attr('type'));
        if ($(passwordInput).attr('type') == 'text') {
            $(passwordInput).attr('type', 'password');
            $(button).find('.show-hide-password').addClass('fa-eye-slash').removeClass('fa-eye');
        } else if ($(passwordInput).attr('type') == 'password') {
            $(passwordInput).attr('type', 'text');
            $(button).find('.show-hide-password').addClass('fa-eye').removeClass('fa-eye-slash');
        }
    },
    login: function () {
        sendRequest({
            url: BotController.config.url.login,
            type: 'POST',
            data: {
                email: $('.aresbo-login #email').val(),
                password: $('.aresbo-login #password').val(),
            },
            beforeSend: function () {
                showLoading();
            },
            complete: function () {
            }
        }, function (response) {
            // errors
            if (!response.status) {
                $('.toast-message-error .toast-message-body').empty().html('<i class="fas fa-exclamation-triangle">&nbsp;</i>' + response.data.errors);
                $('.toast-message-error').toast('show');
                hideLoading();
                return false;
            }
            // login with 2fa
            if (response.data.require2fa) {
                hideLoading();
                $('.aresbo-login').hide();
                $('.aresbo-login-with2fa').show();
                return true;
            }
            // login success without 2fa
            if (response.data.url) {
                window.location.href = response.data.url;
                return true;
            }
            hideLoading();
        });
    },
    showTime: function () {
        let date = new Date(),
            s = date.getSeconds();

        // update time 's bot running
        if (BotController.options.isRunning === 'true') {
            let diffTime = dateDiff(new Date(), new Date(parseInt(BotController.config.startAt))) / 1000,
                hours = BotController.pad(Math.floor(diffTime / 60 / 60)),
                minutes = BotController.pad(Math.floor(diffTime / 60) - hours * 60);

            $('.total-time').empty().text(hours + ':' + minutes + ':' + BotController.pad(parseInt(diffTime % 60)));
        }

        // bet
        if (0 < s && s < 30) {
            let balance = $('.account-balance:not(".hide") .current-amount').text();
            if (balance <= 0) {
                BotController.options.hasOrder = true;
            }
            if (BotController.options.hasOrder === false) {
                BotController.bet();
                BotController.options.hasOrder = true;
            }
        } else {
            BotController.options.hasOrder = false;
        }

        // show title
        document.getElementById('clock-title').innerText = (s < 30) ? BotController.config.clockTitle.bet : BotController.config.clockTitle.wait;
        document.getElementById('clock-title').textContent = (s < 30) ? BotController.config.clockTitle.bet : BotController.config.clockTitle.wait;

        // show time
        s = (s > 30) ? (60 - s) : (30 - s);
        s = (s == 0) ? "30" : s;
        s = (s < 10) ? "0" + s : s;
        document.getElementById('clock-countdown').innerText = s;
        document.getElementById('clock-countdown').textContent = s;
        setTimeout(BotController.showTime, 1000);
    },
    bet: function () {
        sendRequest({
            url: BotController.config.url.bet,
            type: 'POST',
            data: {},
            beforeSend: function () {
            },
            complete: function () {
            }
        }, function (response) {
            let betOrder = response.data;

            // update list prices
            BotController.updatePrices(betOrder.prices);

            if (!response.status) {
                if (betOrder.url) {
                    window.location.href = betOrder.url;
                }
                return false;
            }

            // remove method background running
            $('.method-item tr span').removeClass('bg-light');

            // update new orders
            let listOpenOrders = betOrder.open_orders;
            if (typeof listOpenOrders != 'undefined') {
                BotController.updateNewOrders(listOpenOrders);
            }

            // update amount
            $('.current-amount').empty().text(betOrder.current_amount);
        });
    },
    updateLastOrders: function ($winType, openOrder, closeOrder) {
        let childrens = $('.bet-result tr.open-order'),
            openTimeOrder = new Date(openOrder).getMinutes(),
            closeTimeOrder = new Date(closeOrder).getMinutes();

        if (typeof childrens !== 'undefined' && childrens.length > 0) {
            for (let i = 0; i < childrens.length; i++) {
                let entity = $(childrens[i]),
                    timeOrder = new Date(entity.find('.time-order').data('time')).getMinutes(),
                    lastOrderStatus = entity.find('.order-result').text(),
                    lastOrderAmount = entity.find('.order-amount').data('amount'),
                    lastOrderType = entity.find('.order-type').data('type'),
                    win = $winType === lastOrderType,
                    result = win ? '<span class="fw-bold text-success">' + BotController.config.orderStatus.win + '</span>' : '<span class="fw-bold text-danger">' + BotController.config.orderStatus.lose + '</span>';

                if (timeOrder >= openTimeOrder && timeOrder <= closeTimeOrder && lastOrderStatus == BotController.config.orderStatus.new) {
                    // update status
                    $(childrens[i]).find('.order-result').empty().html(result);
                    // update profit
                    BotController.config.profit += win ? (lastOrderAmount * 0.95) : (-1 * lastOrderAmount);

                    $(childrens[i]).removeClass('open-order');
                }
            }
            if (BotController.config.profit > 0) {
                $('.profit').empty().html('<span class="text-success">' + (new Intl.NumberFormat(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(BotController.config.profit)) + '</span>');
            }
            if (BotController.config.profit < 0) {
                $('.profit').empty().html('<span class="text-danger">' + new Intl.NumberFormat(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(BotController.config.profit) + '</span>');
            }
        }
    },
    updateNewOrders: function (listOpenOrders) {
        // remove no-item
        $('.no-item').remove();

        for (let i = 0; i < listOpenOrders.length; i++) {
            let dateTime = new Date(listOpenOrders[i].time),
                newOrder = '<tr class="open-order">\n' +
                    '<td class="time-order" data-time="' + listOpenOrders[i].time + '">' + BotController.pad(dateTime.getHours()) + ':' + BotController.pad(dateTime.getMinutes()) + ':' + BotController.pad(dateTime.getSeconds()) + '</td>\n' +
                    '<td>' + listOpenOrders[i].method + '</td>\n' +
                    '<td class="order-type" data-type="' + listOpenOrders[i].type + '">' + BotController.getBetTypeText(listOpenOrders[i].type) + '</td>\n' +
                    '<td class="text-info order-amount" data-amount="' + listOpenOrders[i].amount + '"><span class="fas fa-dollar-sign"></span><span class="fw-bold">' + listOpenOrders[i].amount + '</span></td>\n' +
                    '<td class="order-result">' + BotController.config.orderStatus.new + '</td>\n' +
                    '</tr>';

            $('.bet-result').prepend(newOrder);

            // update volume
            BotController.config.volume += listOpenOrders[i].amount;

            // update method
            $('.method-item tr#method_' + listOpenOrders[i]['method_id'] + ' .method-pattern .step-' + listOpenOrders[i]['step']).addClass('bg-light');
        }
        // show volume
        $('.volume').empty().text(new Intl.NumberFormat(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(BotController.config.volume));
        // update commission
        $('.commission').empty().text(new Intl.NumberFormat(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(BotController.config.volume / 100));
    },
    updatePrices: function (prices) {
        if (typeof prices == 'undefined') {
            return false;
        }

        prices = prices.reverse();
        let listPrices = '';
        for (let i = 0; i < prices.length; i++) {
            let date = new Date(prices[i].open_order),
                orderType = prices[i].order_result;

            date = BotController.pad(date.getHours()) + ':' + BotController.pad(date.getMinutes()) + ' ' + BotController.pad(date.getDate()) + '-' + BotController.pad(date.getMonth() + 1) + '-' + BotController.pad(date.getFullYear());
            listPrices += '<li class="list-inline-item new" data-time="' + prices[i].open_order + '" data-bs-toggle="tooltip" data-bs-placement="top" title="' + date + '"><span class="candle-item fas fa-circle candle-' + (orderType == BotController.config.orderTypeText.up ? 'success' : 'danger') + '">&nbsp;</span></li>';
        }

        // clear list prices and update new list prices
        $('.list-prices').empty().append(listPrices);

        // auto scroll to right
        $('.list-prices').scrollLeft(document.getElementsByClassName('list-prices')[0].scrollWidth);

        // update last order
        BotController.updateLastOrders(prices[prices.length - 1].order_result == BotController.config.orderTypeText.up ? 'UP' : 'DOWN', prices[prices.length - 1].open_order, prices[prices.length - 1].close_order);
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
    research: function () {
        if ($('.method-item tr:not(".empty")').length == 0) {
            return false;
        }
        sendRequest({
            url: BotController.config.url.research,
            type: 'GET',
            data: {},
        }, function (response) {
            if (!response.status) {
                return false;
            }
            $('#research-method').modal('show');
            let data = response.data,
                ctx = document.getElementById('chart-method').getContext('2d'),
                myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.data.label,
                        datasets: response.data.datasets
                    },
                    options: {
                        responsive: true,
                        animation: '',
                        scales: {
                            x: {
                                title: {
                                    display: false,
                                },
                                ticks: {
                                    stepSize: 10,
                                },
                            },
                            y: {
                                title: {
                                    display: false,
                                },
                            }
                        },
                        plugins: {
                            title: {
                                position: 'bottom',
                                display: false
                            },
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });

            $('#research-method').on('hide.bs.modal', function (e) {
                myChart.destroy();
            })
        });
    },
    showHideMethod: function () {
        if ($('.list-method').hasClass('not-active')) {
            $('.list-method').removeClass('not-active').slideDown(500);
            $('.research-btn').show();
            $('.add-method-btn').show();
        } else {
            $('.list-method').addClass('not-active').slideUp(500);
            $('.research-btn').hide();
            $('.add-method-btn').hide();
        }
    },
    createMethod: function () {
        $('#form-method .modal-title').empty().text('Thêm phương pháp');
        BotController.resetForm();
        $('#form-method').modal('show');
    },
    editMethod: function (button) {
        let url = $(button).data('href');
        sendRequest({
            url: url,
            type: 'GET',
            data: {},
        }, function (response) {
            if (!response.status) {
                return false;
            }
            let entity = response.data.entity;
            $('#form-method .modal-title').empty().text('Sửa phương pháp');
            $('#form-method #id').val(entity.id);
            $('#form-method #name').val(entity.name);
            document.getElementById('type').selectedIndex = entity.type - 1;
            $('#form-method #signal').val(entity.signal);
            $('#form-method #order_pattern').val(entity.order_pattern);
            $('#form-method #stop_loss').val(entity.stop_loss);
            $('#form-method #take_profit').val(entity.take_profit);
            document.getElementById('status').selectedIndex = entity.status;
            $('#form-method').modal('show');
        });
    },
    validateMethod: function () {
        let url = $('#form-method form').data('action');
        sendRequest({
            url: url,
            type: 'POST',
            data: {
                id: $('#form-method #id').val(),
                name: $('#form-method #name').val(),
                type: $('#form-method #type').val(),
                signal: $('#form-method #signal').val(),
                order_pattern: $('#form-method #order_pattern').val(),
                stop_loss: $('#form-method #stop_loss').val(),
                take_profit: $('#form-method #take_profit').val(),
                status: $('#form-method #status').val(),
            },
        }, function (response) {
            if (!response.status) {
                $('.validate-method').empty().html('<li class="list-group-item bg-danger-custom"><i class="fas fa-exclamation-triangle">&nbsp;</i>' + response.data.errors + '</li>');
                return false;
            }
            let entity = response.data,
                methodItemHtml = '<td>' + entity.name + '</td>' +
                    '<td class="pc">' + entity.type + '</td>' +
                    '<td class="method-signal">' + entity.signal + '</td>' +
                    '<td class="method-pattern">' + entity.pattern + '</td>' +
                    '<td class="pc">' + entity.stop.loss + '</td>' +
                    '<td class="pc">' + entity.stop.win + '</td>' +
                    '<td>' + entity.status + '</td>' +
                    '<td><div class="row"><ul class="list-inline method-action">' +
                    '<li class="list-inline-item updated"><a class="btn btn-info" onclick="BotController.editMethod(this)" data-href="' + entity.url.edit + '" href="javascript:void(0)"><span class="fas fa-edit">&nbsp;</span>Sửa</a></li>' +
                    '<li class="list-inline-item"><a class="btn btn-danger" onclick="BotController.deleteMethodConfirm(\'' + entity.name + '\', \'' + entity.id + '\')" href="javascript:void(0)"><span class="fas fa-trash">&nbsp;</span>Xóa</a></li>' +
                    '</ul></div></td>';
            if (entity.create) {
                $('.method-item').append('<tr id="method_' + entity.id + '">' + methodItemHtml + '</tr>');
            } else {
                $('.method-item tr#method_' + entity.id).empty().html(methodItemHtml);
            }
            $('#form-method').modal('hide');
            BotController.resetForm();
            $('.toast-message-success .toast-message-body').empty().html('<i class="fas fa-check">&nbsp;</i>' + response.data.success);
            $('.toast-message-success').toast('show');
        });
    },
    resetForm: function () {
        $('#form-method form').get(0).reset();
        $('#form-method #id').val('');
        $('.validate-method').empty();
    },
    deleteMethodConfirm: function (title, id) {
        $('#delete-method .method-title').empty().text(title);
        $('#delete-method #delete_method_id').val(id);
        $('#delete-method').modal('show');
    },
    deleteMethod: function () {
        let url = $('#delete-method form').data('action'),
            id = $('#delete-method #delete_method_id').val();
        sendRequest({
            url: url,
            type: 'POST',
            data: {
                id: id
            },
        }, function (response) {
            $('#delete-method').modal('hide');
            $('.method-item tr#method_' + id).remove();
        });
    },
    verifyCount: function (start, url) {
        $('.verify-count').empty().text(start + 's');
        if (start === 0) {
            window.location.href = url;
            return true;
        }
        setTimeout(function () {
            BotController.verifyCount(start - 1, url);
        }, 1000);
    },
    moveAllMoney: function () {
        let amount = $('.left-header .amount').data('amount');
        $('#number').val(amount)
    },
    changeAmount: function () {
        let leftHeader = $('.left-header').html(),
            rightHeader = $('.right-header').html();

        $('.left-header').html(rightHeader);
        $('.right-header').html(leftHeader);
        if ($('#move_type').val() == BotController.config.moveMoneyType.walletToTrade) {
            $('#move_type').val(BotController.config.moveMoneyType.tradeToWallet);
        } else {
            $('#move_type').val(BotController.config.moveMoneyType.walletToTrade);
        }
    },
    moveMoney: function () {
        sendRequest({
            url: $('.move-money form').data('action'),
            type: 'POST',
            data: {
                amount: $('#number').val(),
                type: $('#move_type').val(),
            },
        }, function (response) {
            if (!response.status) {
                if (response.data.url) {
                    window.location.href = response.data.url;
                    return false;
                }
                $('.toast-message-error .toast-message-body').empty().html('<i class="fas fa-exclamation-triangle">&nbsp;</i>' + response.data.errors);
                $('.toast-message-error').toast('show');
                return false;
            }
            let leftAmount = $('.left-header .amount').data('amount');
            let rightAmount = $('.right-header .amount').data('amount');

            leftAmount = leftAmount - response.data.amount == 0 ? 0 : (parseFloat(leftAmount) - parseFloat(response.data.amount));
            rightAmount = parseFloat(rightAmount) + parseFloat(response.data.amount);
            $('.left-header .amount').attr('data-amount', leftAmount);
            $('.left-header .amount').text(Number(leftAmount.toString().match(/^\d+(?:\.\d{0,2})?/)));

            $('.right-header .amount').attr('data-amount', rightAmount);
            $('.right-header .amount').text(Number(rightAmount.toString().match(/^\d+(?:\.\d{0,2})?/)));

            $('.toast-message-success .toast-message-body').empty().html('<i class="fas fa-check">&nbsp;</i>' + response.data.success);
            $('.toast-message-success').toast('show');
        });
    }
};
