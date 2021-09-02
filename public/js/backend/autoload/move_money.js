
let MoveMoneyController = {
    config: {
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
    data: {
        listPrices: [],
        listMethodIds: [],
    },
    // Move money
    moveAllMoney: function () {
        let amount = $('.left-header .amount').attr('data-amount');
        $('#number').val(amount)
    },
    changeAmount: function () {
        let leftHeader = $('.left-header').html(),
            rightHeader = $('.right-header').html();

        $('.left-header').html(rightHeader);
        $('.right-header').html(leftHeader);
        if ($('#move_type').val() == MoveMoneyController.config.moveMoneyType.walletToTrade) {
            $('#move_type').val(MoveMoneyController.config.moveMoneyType.tradeToWallet);
        } else {
            $('#move_type').val(MoveMoneyController.config.moveMoneyType.walletToTrade);
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
                AdminController.showMessage(response.data.errors, 'error');
                return false;
            }
            let leftAmount = $('.left-header .amount').data('amount');
            let rightAmount = $('.right-header .amount').data('amount');

            leftAmount = leftAmount - response.data.amount == 0 ? 0 : (parseFloat(leftAmount) - parseFloat(response.data.amount));
            rightAmount = parseFloat(rightAmount) + parseFloat(response.data.amount);
            $('.left-header .amount').attr('data-amount', leftAmount);
            $('.left-header .amount').text(new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(leftAmount));

            $('.right-header .amount').attr('data-amount', rightAmount);
            $('.right-header .amount').text(new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(rightAmount));

            AdminController.showMessage(response.data.success);
        });
    },
};
