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

    // reset search form
    $('.reset').on('click', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var href = $(form).attr('action');
        showLoading();
        return window.location.href = href;
    });
});

/**
 * Admin controller
 * @type {{notification: {success: *[], errors: *[]}, showMessage: AdminController.showMessage}}
 */
let AdminController = {
    notification: {
        success: [],
        errors: [],
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
