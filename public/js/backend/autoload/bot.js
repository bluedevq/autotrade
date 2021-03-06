$(document).ready(function () {
    $('.botUserProfile').on('hide.bs.collapse', function () {
        $('.profit-collapse').removeClass('hide');
    });
    $('.botUserProfile').on('show.bs.collapse', function () {
        $('.profit-collapse').addClass('hide');
    });
});

/**
 * Bot auto trade
 * @type {{updateStatusMethod: BotController.updateStatusMethod, data: {listMethodIds: *[], listPrices: *[]}, afterStopAuto: BotController.afterStopAuto, updatePrices: ((function(*=): (boolean|undefined))|*), editMethod: BotController.editMethod, createMethod: BotController.createMethod, login: BotController.login, saveProfitSetting: BotController.saveProfitSetting, research: BotController.research, profitSettingForm: BotController.profitSettingForm, resetProfitSettingForm: BotController.resetProfitSettingForm, deleteMethod: BotController.deleteMethod, bet: BotController.bet, validateMethod: BotController.validateMethod, pad: (function(*): string), changeAccountBalance: BotController.changeAccountBalance, updateProfit: ((function(*): (boolean|undefined))|*), selectAllMethod: BotController.selectAllMethod, options: {isRunning: boolean, hasOrder: boolean, isUpdated: boolean}, requestCode: BotController.requestCode, showTimeRequestCode: ((function(*=): (boolean|undefined))|*), afterStartAuto: BotController.afterStartAuto, updateLastOrders: BotController.updateLastOrders, updateMethods: BotController.updateMethods, showTime: BotController.showTime, selectMethod: BotController.selectMethod, getBetTypeText: (function(*): string), updateLastPrices: BotController.updateLastPrices, deleteMethodConfirm: BotController.deleteMethodConfirm, resetFormMethod: BotController.resetFormMethod, startAuto: BotController.startAuto, config: {volume: number, clockTitle: {bet: string, wait: string}, loginType: {notRequire2fa: number, require2fa: number}, orderStatus: {new: string, lose: string, win: string}, orderWaiting: number, delayTime: number, orderTypeText: {up: string, down: string}, profit: number, url: {bet: null, updateLastPrices: null, statusMethod: null, requestCode: null, login: null, startAuto: null, stopAuto: null, research: null}, startAt: null}, updateNewOrders: BotController.updateNewOrders}}
 */
let BotController = {
    options: {
        isRunning: false,
        hasOrder: false,
        isUpdated: false,
    },
    config: {
        url: {
            login: null,
            requestCode: null,
            updateLastPrices: null,
            bet: null,
            research: null,
            startAuto: null,
            stopAuto: null,
            statusMethod: null,
        },
        orderStatus: {
            new: '??ang ?????i',
            win: 'Th???ng',
            lose: 'Thua',
        },
        clockTitle: {
            bet: 'C?? th??? ?????t l???nh',
            wait: '??ang ch??? k???t qu???',
        },
        orderTypeText: {
            up: 'T',
            down: 'G',
        },
        loginType: {
            notRequire2fa: 0,
            require2fa: 1,
        },
        startAt: null,
        profit: 0,
        volume: 0,
        orderWaiting: 30000,
        delayTime: 30000,
    },
    data: {
        listPrices: [],
        listMethodIds: [],
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
                AdminController.showMessage(response.data.errors, 'error');
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
                AdminController.showMessage(response.data.errors, 'error');
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
            $('.request-code').removeClass('disabled').empty().text('G???i m??');
            return false;
        }
        $('.request-code').empty().text(time + 's');
        setTimeout(BotController.showTimeRequestCode, 1000, time);
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
                AdminController.showMessage(response.data.errors, 'error');
                return false;
            }
            BotController.options.isRunning === 'true' ? BotController.afterStopAuto() : BotController.afterStartAuto();
            AdminController.showMessage(response.data.success);
        });
    },
    afterStartAuto: function () {
        BotController.options.isRunning = 'true';
        BotController.config.startAt = Date.now();
        $('.bot-account').attr('disabled', 'disabled').addClass('disabled');
        $('.bot-status-btn').addClass('btn-danger').removeClass('btn-success');
        $('.bot-status-icon').addClass('fa-stop-circle').removeClass('fa-play-circle');
        $('.bot-status-text').empty().text('D???ng');
        $('.profit').empty().text('$0');
        $('.volume').empty().text('0');
        $('.commission').empty().text('0');
        $('.bet-result').empty().html('<tr class="no-item"><td colspan="5">Ch??a ?????t l???nh n??o</td></tr>');
    },
    afterStopAuto: function () {
        BotController.options.isRunning = 'false';
        // update bot status
        $('.bot-account').removeAttr('disabled').removeClass('disabled');
        $('.bot-status-btn').removeClass('btn-danger').addClass('btn-success');
        $('.bot-status-icon').removeClass('fa-stop-circle').addClass('fa-play-circle');
        $('.bot-status-text').empty().text('Ch???y');

        // update bot setting profit
        $('.setting-profit .stop-loss, .setting-profit .take-profit').empty().text('???');
        $('.stop-loss-percent').css({width: 0});
        $('.take-profit-percent').css({width: 0});

        // update method
        $('.method-profit').empty();
        $('.method-stop-loss').empty().text('???');
        $('.method-take-profit').empty().text('???');
        $('.method-item tr span').removeClass('bg-light');
    },
    // Bet
    showTime: function () {
        let date = new Date(Date.now() - BotController.config.delayTime),
            s = date.getSeconds();

        // update time 's bot running
        if (BotController.options.isRunning === 'true') {
            let diffTime = dateDiff(new Date(), new Date(parseInt(BotController.config.startAt))) / 1000,
                hours = BotController.pad(Math.floor(diffTime / 60 / 60)),
                minutes = BotController.pad(Math.floor(diffTime / 60) - hours * 60);

            $('.total-time').empty().text(hours + ':' + minutes + ':' + BotController.pad(parseInt(diffTime % 60)));
        }

        // update last prices
        if (0 < s && s < 2 && BotController.options.isUpdated === false) {
            BotController.options.isUpdated = true;
            BotController.updateLastPrices();
        } else {
            BotController.options.isUpdated = false;
        }
        // bet
        if (10 < s && s < 30) {
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
    updateLastPrices: function () {
        sendRequest({
            url: BotController.config.url.updateLastPrices,
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
        });
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
            let accountType = betOrder.bot_queue.account_type;
            $(accountType == 1 ? '.demo-balance .current-amount' : '.live-balance .current-amount').empty().text(betOrder.current_amount);

            // update reward
            if (betOrder.reward_info.length > 0) {
                let rewardDate = new Date(betOrder.reward_info.history.createdDate);
                $('.total-reward').empty().text(new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(betOrder.reward_info.rewardBalance));
                $('.reward-time').empty().text(BotController.pad(rewardDate.getHours()) + ':' + BotController.pad(rewardDate.getSeconds()) + ' ' + rewardDate.getDate() + '-' + BotController.pad((rewardDate.getMonth() + 1)) + '-' + rewardDate.getFullYear());
                $('.distributedAmount').empty().text(new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(betOrder.reward_info.history.distributedAmount));
                $('.userBlockVolume').empty().text(new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(betOrder.reward_info.history.userBlockVolume));
            }

            // update bot queue if stop loss / take profit
            BotController.updateProfit(betOrder.bot_queue);
        });
    },
    // Update order, last result
    updatePrices: function (prices) {
        if (typeof prices == 'undefined') {
            return false;
        }

        prices = prices.reverse();
        let lastPricePos = prices.length - 1,
            updateFirstTime = BotController.data.listPrices.length === 0,
            lastResult = $('.list-prices .list-inline-item');

        for (let i = 0; i < prices.length; i++) {
            // check last price with new list prices
            if (i === lastPricePos || updateFirstTime) {
                BotController.data.listPrices.push(prices[i]);
            }
        }

        if ($(lastResult[lastResult.length - 1]).data('time') != prices[lastPricePos].open_order) {
            let date = new Date(prices[lastPricePos].open_order - BotController.config.orderWaiting),
                orderType = prices[lastPricePos].order_result;

            date = BotController.pad(date.getHours()) + ':' + BotController.pad(date.getMinutes()) + ' ' + BotController.pad(date.getDate()) + '-' + BotController.pad(date.getMonth() + 1) + '-' + BotController.pad(date.getFullYear());
            let listPrices = '<li class="list-inline-item new" data-time="' + prices[lastPricePos].open_order + '" data-bs-toggle="tooltip" data-bs-placement="top" title="' + date + '"><span class="candle-item fas fa-circle candle-' + (orderType == BotController.config.orderTypeText.up ? 'success' : 'danger') + '">&nbsp;</span></li>';
            // clear list prices and update new list prices
            $('.list-prices').append(listPrices);
        }

        // auto scroll to right
        $('.list-prices').scrollLeft(document.getElementsByClassName('list-prices')[0].scrollWidth);

        // update last order
        BotController.updateLastOrders(prices);
    },
    updateLastOrders: function (prices) {
        let childrens = $('.bet-result tr.open-order');

        if (typeof childrens !== 'undefined' && childrens.length > 0) {
            for (let i = 0; i < childrens.length; i++) {
                let entity = $(childrens[i]),
                    timeOrder = entity.find('.time-order').data('time'),
                    lastOrderStatus = entity.find('.order-result').text(),
                    lastOrderAmount = entity.find('.order-amount').data('amount'),
                    lastOrderType = entity.find('.order-type').data('type');
                for (let j = 0; j < prices.length; j++) {
                    let winType = prices[j].order_result == BotController.config.orderTypeText.up ? 'UP' : 'DOWN',
                        isWin = winType === lastOrderType,
                        openOrder = prices[j].open_order - BotController.config.orderWaiting,
                        closeOrder = prices[j].close_order - BotController.config.orderWaiting,
                        result = isWin ? '<span class="fw-bold text-success-custom">' + BotController.config.orderStatus.win + '</span>' : '<span class="fw-bold text-danger-custom">' + BotController.config.orderStatus.lose + '</span>';

                    if (timeOrder >= openOrder && timeOrder <= closeOrder && lastOrderStatus == BotController.config.orderStatus.new) {
                        // update status
                        $(childrens[i]).find('.order-result').empty().html(result);
                        // update profit
                        BotController.config.profit += isWin ? (lastOrderAmount * 0.95) : (-1 * lastOrderAmount);

                        $(childrens[i]).removeClass('open-order');
                    }
                }
            }
            if (BotController.config.profit > 0) {
                $('.profit').empty().html('<span class="text-success">$&nbsp;' + (new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(BotController.config.profit)) + '</span>');
            }
            if (BotController.config.profit < 0) {
                $('.profit').empty().html('<span class="text-danger">$&nbsp;' + new Intl.NumberFormat(undefined, {
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
                    '<td class="order-type" data-type="' + listOpenOrders[i].type + '"><div class="order-type-justify">' + BotController.getBetTypeText(listOpenOrders[i].type) + '</div></td>\n' +
                    '<td class="order-amount" data-amount="' + listOpenOrders[i].amount + '">' + listOpenOrders[i].amount + '</td>\n' +
                    '<td class="order-result">' + BotController.config.orderStatus.new + '</td>\n' +
                    '</tr>';

            $('.bet-result').prepend(newOrder);

            // update volume
            BotController.config.volume += listOpenOrders[i].amount;

            // update method pattern
            $('.method-item #method_' + listOpenOrders[i]['method_id'] + ' .method-pattern .step-' + listOpenOrders[i]['step']).addClass('bg-light');
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
    updateMethods: function (methods) {
        for (let i = 0; i < methods.length; i++) {
            // update method profit
            $('.method-item #method_' + methods[i]['id'] + ' .method-profit').empty().html(methods[i]['profit']);
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
        return (betType == 'UP' ? '<span class="text-success-custom fas fa-arrow-alt-circle-up">&nbsp;</span><span class="fw-bold">Mua</span>' : '<span class="text-danger-custom fas fa-arrow-alt-circle-down">&nbsp;</span><span class="fw-bold">B??n</span>')
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
    research: function () {
        sendRequest({
            url: BotController.config.url.research,
            type: 'POST',
            data: {
                'list_prices': JSON.stringify(BotController.data.listPrices)
            },
        }, function (response) {
            if (!response.status) {
                AdminController.showMessage(response.data.errors, 'error');
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
                                text: 'Bi???u ????? gi??? l???p ' + response.data.total_methods + ' ph????ng ph??p t??? ' + response.data.from + ' ?????n ' + response.data.to
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
        $('#form-method .modal-title').empty().text('Th??m ph????ng ph??p');
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
            $('#form-method .modal-title').empty().text('S???a ph????ng ph??p');
            $('#form-method #id').val(entity.id);
            $('#form-method #name').val(entity.name);
            document.getElementById('type').selectedIndex = entity.type - 1;
            $('#form-method #signal').val(entity.signal);
            $('#form-method #order_pattern').val(entity.order_pattern);
            $('#form-method #step').val(entity.step);
            $('#form-method #profit').val(entity.profit);
            $('#form-method #stop_loss').val(entity.stop_loss);
            $('#form-method #take_profit').val(entity.take_profit);
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
            },
        }, function (response) {
            if (!response.status) {
                $('.validate-method').empty().html('<li class="list-group-item bg-danger-custom"><i class="fas fa-exclamation-triangle">&nbsp;</i>' + response.data.errors + '</li>');
                return false;
            }
            let entity = response.data,
                methodItemHtml =
                    '<td class="method-id"><input type="checkbox" class="form-check-input" onclick="BotController.selectMethod(this)" value="' + entity.id + '"></td>' +
                    '<td>' + entity.name + '</td>' +
                    '<td class="pc">' + entity.type + '</td>' +
                    '<td class="method-signal">' + entity.signal + '</td>' +
                    '<td class="method-pattern">' + entity.pattern + '</td>' +
                    '<td class="method-profit">' + entity.profit + '</td>' +
                    '<td class="pc method-stop-loss">' + entity.stop.loss + '</td>' +
                    '<td class="pc method-take-profit">' + entity.stop.win + '</td>' +
                    '<td class="method-action-wrap"><a class="btn btn-info" onclick="BotController.editMethod(this)" data-href="' + entity.url.edit + '" href="javascript:void(0)"><span class="fas fa-edit">&nbsp;</span>S???a</a></td>';
            if (entity.create) {
                $('.method-item').append('<tr id="method_' + entity.id + '" class="' + (entity.status ? 'active' : 'stop') + '">' + methodItemHtml + '</tr>');
            } else {
                $('.method-item #method_' + entity.id).empty().html(methodItemHtml);
            }
            $('#form-method').modal('hide');
            BotController.resetFormMethod();
            AdminController.showMessage(response.data.success);
        });
    },
    resetFormMethod: function () {
        $('#form-method form').get(0).reset();
        $('#form-method #id').val('');
        $('.validate-method').empty();
    },
    deleteMethodConfirm: function () {
        $('#delete-method').modal('show');
    },
    deleteMethod: function () {
        let url = $('#delete-method form').data('action');
        sendRequest({
            url: url,
            type: 'POST',
            data: {
                method_ids: BotController.data.listMethodIds
            },
        }, function (response) {
            $('#delete-method').modal('hide');
            if (!response.status) {
                AdminController.showMessage(response.data.errors, 'error');
                return false;
            }

            // update list method
            let listMethodIds = response.data.method_ids;
            for (let i = 0; i < listMethodIds.length; i++) {
                let tr = $('.method-item #method_' + listMethodIds[i]),
                    input = $('.method-item #method_' + listMethodIds[i] + ' .method-id input[type="checkbox"]');

                input.prop('checked', false);
                BotController.selectMethod(input);
                tr.remove();
            }
            AdminController.showMessage(response.data.success);
        });
    },
    selectMethod: function (input) {
        let id = $(input).val();
        if ($(input).is(':checked')) {
            BotController.data.listMethodIds.push(id);
            $('.run-method, .stop-method, .delete-method').removeClass('disabled');
        } else {
            let index = BotController.data.listMethodIds.indexOf(id);
            if (index > -1) {
                BotController.data.listMethodIds.splice(index, 1);
            }
        }
        if (BotController.data.listMethodIds.length === 0) {
            $('#select-all-methods').prop('checked', false);
            $('.run-method, .stop-method, .delete-method').addClass('disabled');
        }
    },
    selectAllMethod: function () {
        let listMethods = $('.method-id input[type="checkbox"]');
        for (let i = 0; i < listMethods.length; i++) {
            $(listMethods[i]).prop('checked', $('#select-all-methods').prop('checked'));
            BotController.selectMethod($(listMethods[i]));
        }
    },
    updateStatusMethod: function (active) {
        sendRequest({
            url: BotController.config.url.statusMethod,
            type: 'POST',
            data: {
                method_ids: BotController.data.listMethodIds,
                status: active
            },
        }, function (response) {
            if (!response.status) {
                AdminController.showMessage(response.data.errors, 'error');
                return false;
            }

            // update list method
            let listMethodIds = response.data.method_ids;
            for (let i = 0; i < listMethodIds.length; i++) {
                let tr = $('.method-item #method_' + listMethodIds[i]),
                    input = $('.method-item #method_' + listMethodIds[i] + ' .method-id input[type="checkbox"]');

                active ? tr.addClass('active').removeClass('stop') : tr.addClass('stop').removeClass('active');
                input.prop('checked', false);
                BotController.selectMethod(input);
            }
            AdminController.showMessage(response.data.success);
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
            AdminController.showMessage(response.data.success);
        });
    },
    resetProfitSettingForm: function () {
        $('#bot_stop_loss').val('');
        $('#bot_take_profit').val('');
        $('.validate-profit').empty();
    },
};
