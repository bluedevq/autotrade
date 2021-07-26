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
            $(button).find('.show-hide-password').addClass('fa-eye').removeClass('fa-eye-slash');
        } else if ($(passwordInput).attr('type') == 'password') {
            $(passwordInput).attr('type', 'text');
            $(button).find('.show-hide-password').addClass('fa-eye-slash').removeClass('fa-eye');
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
            BotController.updatePrices(betOrder.candles);

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
                    result = (listClosedOrders[i].result === 'WIN') ? '<span class="fw-bold text-success">' + BotController.config.orderStatus.win + '</span>' : '<span class="fw-bold text-danger">' + BotController.config.orderStatus.lose + '</span>';

                lastTimeOrder = new Date(lastTimeOrder);
                lastTimeOrder = BotController.pad(lastTimeOrder.getHours()) + ':' + BotController.pad(lastTimeOrder.getMinutes());
                if (timeOrder == lastTimeOrder && lastOrderStatus == BotController.config.orderStatus.new) {
                    // update status
                    $(childrens[i]).length > 0 ? $(childrens[i]).find('.bet-order-result').empty().html(result) : null;
                    // update profit
                    BotController.config.profit += listClosedOrders[i].win_amount;
                }
            }
            if (BotController.config.profit > 0) {
                $('.profit').empty().html('<span class="text-success">' + parseFloat(BotController.config.profit).toFixed(2) + '</span>');
            }
            if (BotController.config.profit < 0) {
                $('.profit').empty().html('<span class="text-danger">' + parseFloat(BotController.config.profit).toFixed(2) + '</span>');
            }
        }
    },
    updateNewOrders: function (listOpenOrders) {
        $('.no-item').remove();
        for (let i = 0; i < listOpenOrders.length; i++) {
            let dateTime = new Date(listOpenOrders[i].time),
                newOrder = "<tr>\n" +
                    '<td class="time-order">' + BotController.pad(dateTime.getHours()) + ':' + BotController.pad(dateTime.getMinutes()) + '</td>\n' +
                    '<td>' + listOpenOrders[i].method + '</td>\n' +
                    '<td>' + BotController.getBetTypeText(listOpenOrders[i].type) + '</td>\n' +
                    '<td class="text-info"><span class="fas fa-dollar-sign"></span><span class="fw-bold">' + listOpenOrders[i].amount + '</span></td>\n' +
                    '<td class="fw-bold bet-order-result">' + BotController.config.orderStatus.new + '</td>\n' +
                    '</tr>';

            $('.bet-result').prepend(newOrder);

            // update volume
            BotController.config.volume += listOpenOrders[i].amount;
        }
        $('.volume').empty().text(BotController.config.volume);
    },
    updatePrices: function (candles) {
        if (candles.length == 0 || typeof candles == 'undefined') {
            return false;
        }
        let date = new Date(candles[0].open_order);
        date = BotController.pad(date.getHours()) + ':' + BotController.pad(date.getMinutes()) + ' ' + BotController.pad(date.getDate()) + '-' + BotController.pad(date.getMonth()) + '-' + BotController.pad(date.getFullYear());
        $('.list-prices').append('<li class="list-inline-item new" data-bs-toggle="tooltip" data-bs-placement="top" title="' + date + '"><span class="candle-item btn-' + (candles[0].order_result == BotController.config.orderTypeText.up ? 'success' : 'danger') + '">&nbsp;</span></li>');
        $('.list-prices').scrollLeft($('.list-prices').width());
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
                        interaction: {
                            intersect: false,
                        },
                        scales: {
                            xAxes: [{
                                display: true,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Giờ'
                                }
                            }],
                            yAxes: [{
                                display: true,
                                scaleLabel: {
                                    display: true,
                                    labelString: '$'
                                }
                            }]
                        },
                        plugins: {
                            title: {
                                position: 'bottom',
                                display: false
                            },
                            legend: {
                                position: 'bottom',
                            },
                        }
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
            $('#form-method #stop_win').val(entity.stop_win);
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
                stop_win: $('#form-method #stop_win').val(),
                status: $('#form-method #status').val(),
            },
        }, function (response) {
            if (!response.status) {
                return false;
            }
            let entity = response.data,
                methodItemHtml = '<td>' + entity.name + '</td>' +
                    '<td class="pc">' + entity.type + '</td>' +
                    '<td>' + entity.signal + '</td>' +
                    '<td>' + entity.pattern + '</td>' +
                    '<td class="pc">' + entity.stop.loss + '</td>' +
                    '<td class="pc">' + entity.stop.win + '</td>' +
                    '<td>' + entity.status + '</td>' +
                    '<td><div class="pc">' +
                    '<a class="btn btn-info" onclick="BotController.editMethod(this)" data-href="' + entity.url.edit + '" href="javascript:void(0)"><span class="fas fa-edit">&nbsp;</span>Sửa</a>' +
                    '&nbsp;<a class="btn btn-danger" onclick="BotController.deleteMethodConfirm(\'' + entity.name + '\', \'' + entity.id + '\')" href="javascript:void(0)"><span class="fas fa-trash">&nbsp;</span>Xóa</a>' +
                    '</div><div class="sp" style="min-width: 70px;"><ul class="list-inline">' +
                    '<li class="list-inline-item"><a class="text-info" onclick="BotController.editMethod(this)" data-href="' + entity.url.edit + '" href="javascript:void(0)"><span class="fas fa-edit"></span></a></li>' +
                    '<li class="list-inline-item"><a class="text-danger" onclick="BotController.deleteMethodConfirm(\'' + entity.name + '\', \'' + entity.id + '\')" href="javascript:void(0)"><span class="fas fa-trash"></span></a></li>' +
                    '</ul></div></td>';
            if (entity.create) {
                $('.method-item').append('<tr id="method_' + entity.id + '">' + methodItemHtml + '</tr>');
            } else {
                $('.method-item tr#method_' + entity.id).empty().html(methodItemHtml);
            }
            $('#form-method').modal('hide');
            BotController.resetForm();
        });
    },
    resetForm: function () {
        $('#form-method form').get(0).reset();
        $('#form-method #id').val('');
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
};
