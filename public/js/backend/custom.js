$(document).ready(function () {
    $('form').on('submit', function (e) {
        e.preventDefault();
        if ($(this).attr('show-loading') == 1) {
            showLoading();
        }
        $(this).off('submit').submit();
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
    // toggle password
    togglePassword: function (button) {
        let passwordInput = $(button).parent('.input-group').find('input#password');
        if ($(passwordInput).attr('type') == 'text') {
            $(passwordInput).attr('type', 'password');
            $(button).find('.show-hide-password').addClass('fa-eye-slash').removeClass('fa-eye');
        } else if ($(passwordInput).attr('type') == 'password') {
            $(passwordInput).attr('type', 'text');
            $(button).find('.show-hide-password').addClass('fa-eye').removeClass('fa-eye-slash');
        }
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
    verifyCount: function (start, url) {
        $('.verify-count').empty().text(start + 's');
        if (start === 0) {
            window.location.href = url;
            return true;
        }
        setTimeout(function () {
            AdminController.verifyCount(start - 1, url);
        }, 1000);
    },
};
