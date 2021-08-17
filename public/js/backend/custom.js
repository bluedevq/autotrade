$(document).ready(function () {
    $('form').on('submit', function (e) {
        e.preventDefault();
        if ($(this).attr('show-loading') == 1) {
            showLoading();
        }
        $(this).off('submit').submit();
    });

    $('.navbar-toggler').on('click', function () {
        if ($('.navbar-collapse').hasClass('not-active')) {
            $('.navbar-collapse').removeClass('not-active').slideDown(400);
        } else {
            $('.navbar-collapse').addClass('not-active').slideUp(400);
        }
    });

    // reset form method
    $('#form-method').on('hide.bs.modal', function (e) {
        BotController.resetFormMethod();
    });

    // reset profit
    $('#update-bot-queue').on('hide.bs.modal', function (e) {
        BotController.resetProfitSettingForm();
    });
});

/**
 * Bot auto trade
 * @type {{moveMoney: BotController.moveMoney, afterStopAuto: BotController.afterStopAuto, verifyCount: BotController.verifyCount, updatePrices: BotController.updatePrices, editMethod: BotController.editMethod, createMethod: BotController.createMethod, showMessage: BotController.showMessage, listPrices: [], login: BotController.login, changeAmount: BotController.changeAmount, saveProfitSetting: BotController.saveProfitSetting, showHidePassword: BotController.showHidePassword, research: BotController.research, profitSettingForm: BotController.profitSettingForm, resetProfitSettingForm: BotController.resetProfitSettingForm, deleteMethod: BotController.deleteMethod, bet: BotController.bet, notification: {success: [], errors: []}, validateMethod: BotController.validateMethod, pad: (function(*): string), changeAccountBalance: BotController.changeAccountBalance, updateProfit: BotController.updateProfit, options: {isRunning: boolean, hasOrder: boolean}, requestCode: BotController.requestCode, showTimeRequestCode: BotController.showTimeRequestCode, afterStartAuto: BotController.afterStartAuto, updateLastOrders: BotController.updateLastOrders, updateMethods: BotController.updateMethods, moveAllMoney: BotController.moveAllMoney, toggleMethods: BotController.toggleMethods, showTime: BotController.showTime, getBetTypeText: (function(*): string), deleteMethodConfirm: BotController.deleteMethodConfirm, resetFormMethod: BotController.resetFormMethod, startAuto: BotController.startAuto, config: {volume: number, clockTitle: {bet: string, wait: string}, loginType: {notRequire2fa: number, require2fa: number}, orderStatus: {new: string, lose: string, win: string}, orderTypeText: {up: string, down: string}, moveMoneyType: {walletToTrade: number, tradeToWallet: number}, profit: number, url: {bet: null, requestCode: null, login: null, startAuto: null, stopAuto: null, research: null}, startAt: null}, updateNewOrders: BotController.updateNewOrders}}
 */
let BotController = {
    options: {
        isRunning: false,
        hasOrder: false,
    },
    config: {
        url: {
            login: null,
            requestCode: null,
            bet: null,
            research: null,
            startAuto: null,
            stopAuto: null,
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
        loginType: {
            notRequire2fa: 0,
            require2fa: 1,
        },
        startAt: null,
        profit: 0,
        volume: 0,
    },
    notification: {
        success: [],
        errors: [],
    },
    listPrices: [],
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
    // Login
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
                hideLoading();
            }
        }, function (response) {
            // errors
            if (!response.status) {
                BotController.showMessage(response.data.errors, 'error');
                return false;
            }
            $('.aresbo-login').hide();
            // login with 2fa or verify device
            if (response.data.require2fa || response.data.verifyDevice) {
                $('.aresbo-login-with2fa').show();
                $('.aresbo-login-verify-device').show();
                $('#require_2fa').val(BotController.config.loginType.require2fa);
                if (!response.data.require2fa) {
                    $('.aresbo-login-authentication').hide();
                    $('#require_2fa').val(BotController.config.loginType.notRequire2fa);
                }
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
    requestCode: function () {
        sendRequest({
            url: BotController.config.url.requestCode,
            type: 'POST',
            data: {},
            beforeSend: function () {
                showLoading();
            },
            complete: function () {
                hideLoading();
            }
        }, function (response) {
            // errors
            if (!response.status) {
                BotController.showMessage(response.data.errors, 'error');
                return false;
            }
            // count time if success
            $('.request-code').addClass('disabled');
            BotController.showTimeRequestCode(60);
        });
    },
    showTimeRequestCode: function (time) {
        time = time - 1;
        time = (time < 10) ? "0" + time : time;
        if (time == 0) {
            $('.request-code').removeClass('disabled').empty().text('Gửi mã');
            return false;
        }
        $('.request-code').empty().text(time + 's');
        setTimeout(BotController.showTimeRequestCode, 1000, time);
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
    // Start or stop auto
    startAuto: function () {
        let url = BotController.options.isRunning === 'true' ? BotController.config.url.stopAuto : BotController.config.url.startAuto;
        sendRequest({
            url: url,
            type: 'POST',
            data: {
                account_type: $('.bot-account').val(),
            },
        }, function (response) {
            if (!response.status) {
                BotController.showMessage(response.data.errors, 'error');
                return false;
            }
            BotController.options.isRunning === 'true' ? BotController.afterStopAuto() : BotController.afterStartAuto();
            BotController.showMessage(response.data.success);
        });
    },
    afterStartAuto: function () {
        BotController.options.isRunning = 'true';
        if (BotController.config.startAt == null) {
            BotController.config.startAt = Date.now();
        }
        $('.bot-account').attr('disabled', 'disabled').addClass('disabled');
        $('.bot-status-btn').addClass('btn-danger').removeClass('btn-success');
        $('.bot-status-icon').addClass('fa-stop-circle').removeClass('fa-play-circle');
        $('.bot-status-text').empty().text('Dừng');
    },
    afterStopAuto: function () {
        BotController.options.isRunning = 'false';
        // update bot status
        $('.bot-account').removeAttr('disabled').removeClass('disabled');
        $('.bot-status-btn').removeClass('btn-danger').addClass('btn-success');
        $('.bot-status-icon').removeClass('fa-stop-circle').addClass('fa-play-circle');
        $('.bot-status-text').empty().text('Chạy');

        // update bot setting profit
        $('.setting-profit .stop-loss, .setting-profit .take-profit').empty().text('∞');
        $('.stop-loss-percent').css({width: 0});
        $('.take-profit-percent').css({width: 0});

        // update method
        $('.method-profit').empty();
        $('.method-stop-loss').empty().text('∞');
        $('.method-take-profit').empty().text('∞');
        $('.method-item tr span').removeClass('bg-light');
    },
    // Bet
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

            // remove method background running
            $('.method-item tr span').removeClass('bg-light');

            if (!response.status) {
                if (betOrder.url) {
                    window.location.href = betOrder.url;
                }
                return false;
            }

            // update new orders
            let listOpenOrders = betOrder.open_orders;
            if (typeof listOpenOrders != 'undefined') {
                BotController.updateNewOrders(listOpenOrders);
            }

            // update method
            BotController.updateMethods(betOrder.methods);

            // update amount
            $('.current-amount').empty().text(betOrder.current_amount);

            // update bot queue if stop loss / take profit
            BotController.updateProfit(betOrder.bot_queue);
        });
    },
    // Update order, last result
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
                $('.profit').empty().html('<span class="text-success">' + (new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(BotController.config.profit)) + '</span>');
            }
            if (BotController.config.profit < 0) {
                $('.profit').empty().html('<span class="text-danger">' + new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(BotController.config.profit) + '</span>');
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
                    '<td>' + listOpenOrders[i].method_name + '</td>\n' +
                    '<td class="order-type" data-type="' + listOpenOrders[i].type + '">' + BotController.getBetTypeText(listOpenOrders[i].type) + '</td>\n' +
                    '<td class="text-info order-amount" data-amount="' + listOpenOrders[i].amount + '"><span class="fas fa-dollar-sign"></span><span class="fw-bold">' + listOpenOrders[i].amount + '</span></td>\n' +
                    '<td class="order-result">' + BotController.config.orderStatus.new + '</td>\n' +
                    '</tr>';

            $('.bet-result').prepend(newOrder);

            // update volume
            BotController.config.volume += listOpenOrders[i].amount;

            // update method pattern
            $('.method-item tr#method_' + listOpenOrders[i]['method_id'] + ' .method-pattern .step-' + listOpenOrders[i]['step']).addClass('bg-light');
        }
        // show volume
        $('.volume').empty().text(new Intl.NumberFormat(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(BotController.config.volume));
        // update commission
        $('.commission').empty().text(new Intl.NumberFormat(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(BotController.config.volume / 100));
    },
    updatePrices: function (prices) {
        if (typeof prices == 'undefined') {
            return false;
        }

        prices = prices.reverse();
        let listPrices = '',
            updateFirstTime = BotController.listPrices.length === 0;
        for (let i = 0; i < prices.length; i++) {
            if (i === prices.length - 1 || updateFirstTime) {
                BotController.listPrices.push(prices[i]);
            }
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
    updateMethods: function (methods) {
        for (let i = 0; i < methods.length; i++) {
            // update method profit
            $('.method-item tr#method_' + methods[i]['id'] + ' .method-profit').empty().html(methods[i]['profit']);
        }
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
    // List methods
    toggleMethods: function () {
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
    research: function () {
        if ($('.method-item tr:not(".empty")').length == 0) {
            return false;
        }
        sendRequest({
            url: BotController.config.url.research,
            type: 'POST',
            data: {
                'list_prices': JSON.stringify(BotController.listPrices)
            },
        }, function (response) {
            if (!response.status) {
                return false;
            }
            let profit = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(response.data.total_profit),
                volume = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(response.data.total_volume),
                highestNegative = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(response.data.highest_negative),
                profitHtml = null;

            // format profit
            if (profit < 0) {
                profitHtml = '<span class="text-danger-custom">' + profit + '</span>';
            } else {
                profitHtml = '<span class="text-success-custom">' + profit + '</span>';
            }
            $('#research-method').modal('show');
            // update modal info
            $('#research-method .total-candles').empty().text(response.data.total_prices);
            $('#research-method .volume').empty().text(volume);
            $('#research-method .profit').empty().html(profitHtml);
            $('#research-method .highest-negative').empty().text(highestNegative);
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
                                grid: {
                                    borderDash: [2, 2],
                                    color: function (context) {
                                        return '#5F2413';
                                    },
                                }
                            },
                            y: {
                                title: {
                                    display: false,
                                },
                                grid: {
                                    borderDash: [2, 2],
                                    color: function (context) {
                                        return '#5f2413';
                                    },
                                },
                            }
                        },
                        plugins: {
                            title: {
                                position: 'bottom',
                                display: true,
                                color: '#ffffff',
                                font: {
                                    weight: 400,
                                    size: 16
                                },
                                text: 'Biểu đồ giả lập ' + response.data.total_methods + ' phương pháp từ ' + response.data.from + ' đến ' + response.data.to
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#ffffff',
                                    padding: 15,
                                    boxWidth: 20,
                                    boxHeight: 1,
                                },
                            },
                        },
                    },
                });

            $('#research-method').on('hide.bs.modal', function (e) {
                myChart.destroy();
            })
        });
    },
    createMethod: function () {
        $('#form-method .modal-title').empty().text('Thêm phương pháp');
        BotController.resetFormMethod();
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
            $('#form-method #step').val(entity.step);
            $('#form-method #profit').val(entity.profit);
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
                step: $('#form-method #step').val(),
                profit: $('#form-method #profit').val(),
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
                    '<td class="method-profit">' + entity.profit + '</td>' +
                    '<td class="pc method-stop-loss">' + entity.stop.loss + '</td>' +
                    '<td class="pc method-take-profit">' + entity.stop.win + '</td>' +
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
            BotController.resetFormMethod();
            BotController.showMessage(response.data.success);
        });
    },
    resetFormMethod: function () {
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
    // Move money
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
                BotController.showMessage(response.data.errors, 'error');
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

            BotController.showMessage(response.data.success);
        });
    },
    // Profit setting
    updateProfit: function (botQueue) {
        let profit = botQueue.profit;
        // update stop loss percent
        if (profit <= 0 && botQueue.stop_loss) {
            $('.stop-loss-percent').css({width: profit * 100 / botQueue.stop_loss + '%'});
            $('.take-profit-percent').css({width: 0});
        }
        // update take profit percent
        if (profit > 0 && botQueue.take_profit) {
            let percent = profit >= botQueue.take_profit ? 100 : (profit * 100 / botQueue.take_profit);
            $('.take-profit-percent').css({width: percent + '%'});
            $('.stop-loss-percent').css({width: 0});
        }

        // check bot queue was stopped
        if (botQueue.status === 0) {
            showLoading();
            BotController.afterStopAuto();
            setTimeout(function () {
                hideLoading();
            }, 1000);
            return false;
        }
    },
    profitSettingForm: function () {
        $('#update-bot-queue').modal('show');
    },
    saveProfitSetting: function () {
        let url = $('#update-bot-queue form').data('action');
        sendRequest({
            url: url,
            type: 'POST',
            data: {
                id: $('#update-bot-queue #bot_queue_id').val(),
                stop_loss: $('#update-bot-queue #bot_stop_loss').val(),
                take_profit: $('#update-bot-queue #bot_take_profit').val(),
            },
        }, function (response) {
            if (!response.status) {
                $('.validate-profit').empty().html('<li class="list-group-item bg-danger-custom"><i class="fas fa-exclamation-triangle">&nbsp;</i>' + response.data.errors + '</li>');
                return false;
            }
            $('.stop-loss').empty().text(response.data.stop_loss);
            $('.take-profit').empty().text(response.data.take_profit);
            BotController.resetProfitSettingForm();
            $('#update-bot-queue').modal('hide');
            BotController.showMessage(response.data.success);
        });
    },
    resetProfitSettingForm: function () {
        $('#bot_stop_loss').val('');
        $('#bot_take_profit').val('');
        $('.validate-profit').empty();
    },
    // Show toast message
    showMessage: function (message, type) {
        if (type === 'error') {
            $('.toast-message-error .toast-message-body').empty().html('<i class="fas fa-exclamation-triangle">&nbsp;</i>' + message);
            $('.toast-message-error').toast('show');
        } else {
            $('.toast-message-success .toast-message-body').empty().html('<i class="fas fa-check">&nbsp;</i>' + message);
            $('.toast-message-success').toast('show');
        }

        hideLoading();
    },
};
