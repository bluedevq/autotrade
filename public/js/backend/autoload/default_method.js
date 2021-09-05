/**
 * Management Default Method
 * @type {{deleteConfirm: DefaultMethodController.deleteConfirm}}
 */
let DefaultMethodController = {
    // Delete default method
    deleteConfirm: function (url, methodName) {
        $('.delete-confirm form').attr('action', url);
        $('.delete-confirm .default-method-name').empty().text(methodName);
        $('.delete-confirm').modal('show');
    }
};
