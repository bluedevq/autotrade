$(document).ready(function () {
    $('form').on('submit', function (e) {
        e.preventDefault();
        $(this).attr('show-loading') == 1 ? showLoading() : null;
        $(this).off('submit').submit();
    });
    // upload image
    $(".input-image").change(function () {
        previewFile(this);
    });
    $(".input-image2").change(function () {
        previewFile2(this);
    });
    //date picker
    $('.datepicker').datepicker({
        todayHighlight: true,
        autoclose: true,
        format: 'yyyy-mm-dd',
        language: 'ja'
    });
    // time picker
    $('.timepicker').timepicker({
        minuteStep: 1,
        showSeconds: false,
        showMeridian: false,
        defaultTime: ''
    });
    // phone format
    $(".telephone").mask("999-999-9999");
    // search location by name on google map
    $('.google_map_search_by_name').on('keydown', function () {
        setTimeout(function(){
            GoogleMap.searchByName($('.google_map_search_by_name').val(), $('.station-lat-lng'));
        }, 1000);
    });
});

//modal delete confirm
$(document).ready(function () {
    //delete
    $('.delete-action').on('click', function () {
            var href = $(this).data('action');
            $('#del_form').attr('action', href);
        }
    );
    // mass destroy
    $('.mass-destroy-btn').on('click', function () {
        var href = $(this).data('action');
        $('#mass_del_form').attr('action', href);
        pushItemToDestroy();
    });
    $("#check_all_mass_destroy").click(function () {
        $(".mass-destroy").prop('checked', $(this).prop('checked')).trigger('change');
    });
    $(".mass-destroy").on('change', function () {
        if ($(".mass-destroy:checked").length > 0) {
            $('.mass-destroy-btn').removeClass('disabled');
        } else {
            $('.mass-destroy-btn').addClass('disabled');
        }
    });

    function pushItemToDestroy() {
        var ids = [];
        $(".mass-destroy:checked").each(function () {
            ids.push($(this).val());
        });
        $('#mass_destroy_id').val(ids.join(','));
    }

    // close
    $('.close-parent-modal').on('click', function () {
        var parent = $(this).data('parent-modal');
        $('#' + parent).find('.close').click();
    });
});
//  sorting
$('.sorting').on('click', function (e) {
    e.preventDefault();
    var url = $(this).data('url');
    if (!url) {
        return false;
    }
    return window.location.href = url;
});

// /reset btn

$('a.reset').on('click', function (e) {
    e.preventDefault();
    var form = $(this).closest('form');
    var href = $(form).attr('action');
    return window.location.href = href;
});

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
});

$('#load').click(function () {
    var $this = $(this);
    $this.button('loading');
    setTimeout(function() {
        $this.button('reset');
    }, 2000);
});

var SystemController = {
    showModal: function (e, isRegister) {
        var modal = $('.modal#modal-main');
        var button = $(modal).find('button.btn-primary');
        SystemController.resetModal(modal);

        $(button).off('click').on("click", function() {
            if (isRegister) {
                SystemController.register(this);
            } else {
                SystemController.update(this);
            }
        });

        if (isRegister) {
            $(button).text($(button).attr("data-label-register"));
            SystemController.showCreate(e);
        } else {
            $(button).text($(button).attr("data-label-update"));
            SystemController.showUpdate(e)
        }
    },

    register: function (e) {
        var modal = $('.modal#modal-main');
        var data = $('.modal#modal-main .modal-form.main-form').serialize();
        var url = $(e).attr("data-url-register");

        sendRequest({
            url: url,
            type: 'POST',
            data: data
        }, function (response) {
            if (response.status) {
                $(modal).modal('hide');
                location.reload();
            } else {
                showErrorFlash(response.message, modal);
            }
        });
    },

    update: function (e) {
        var modal = $('.modal#modal-main');
        var data = $('.modal-form.main-form').serialize();
        var url = $(e).attr("data-url-update");
        sendRequest({
            url: url,
            type: 'PUT',
            data: data
        }, function (response) {
            if (response.status) {
                $(modal).modal('hide');
                location.reload();
            } else {
                showErrorFlash(response.message, modal);
            }
        });
    },

    showCreate: function (e) {
        var modal = $('.modal#modal-main');
        var url = $(e).attr("data-url-create");

        sendRequest({
            url: url,
            type: 'GET'
        }, function (response) {
            if (response.status) {
                $(modal).find('.modal-form-wrap').html(response.data.modalForm);
                var label = $(modal).find('#modal-label');
                $(label).html($(label).data('model-name') + $(label).data('register-label'));
                SystemController.showHideRequiredText('create');
                $(modal).modal('show');
            } else {
                showErrorFlash(response.message, modal);
            }
        });
    },

    showUpdate: function (e) {
        var modal = $('.modal#modal-main');
        var url = $(e).attr("data-url-edit");
        var id = $(e).attr("data-id");
        var updateUrl = $(modal).find('button.btn-primary').attr("data-url-update");
        updateUrl = updateUrl.replace("_id_", id);

        sendRequest({
            url: url,
            type: 'GET'
        }, function (response) {
            if (response.status) {
                $(modal).find('.modal-form-wrap').html(response.data.modalForm);
                $(modal).find('button.btn-primary').attr("data-url-update", updateUrl);
                var label = $(modal).find('#modal-label');
                $(label).html($(label).data('model-name') + $(label).data('edit-label'));
                SystemController.showHideRequiredText('edit');
                $(modal).modal('show');
            } else {
                showErrorFlash(response.message, modal);
            }
        });
    },

    getList: function (message) {
        var modal = $('.modal#modal-main');
        var url = document.location.origin + '/' + document.location.pathname + '/get-list' + document.location.search;
        sendRequest({
            url: url,
            type: 'GET'
        }, function (response) {
            if (response.status) {
                $('.main-wrap .table tbody').html(response.data.data);
                $('.main-wrap .paging-wrap').html(response.data.paginator);
                $('.main-wrap .paging-info-wrap').html(response.data.paginatorInfo);
                showSuccessFlash(message);
            }
            $(modal).modal('hide');
        });
    },

    resetModal: function(modal) {
        $(modal).find("#error_msg").html("");
        $(modal).find(".modal-form-wrap").html("");
    },
    resizeModal: function () {
        if (!$("body").hasClass("modal-open")) return;

        var modalDialog = $(".modal.in > .modal-dialog");
        var backdrop = $(".modal.in > .modal-backdrop");
        var backdropHeight = backdrop.height();
        var modalDialogHeight = modalDialog.height();

        if (modalDialogHeight > backdropHeight) backdrop.height(modalDialogHeight + 150);
    },
    showHideRequiredText: function (flag) {
        if (flag == 'create') {
            return $('.hide-on-edit').removeClass('hide');
        }
        return $('.hide-on-edit').addClass('hide');
    }
};
