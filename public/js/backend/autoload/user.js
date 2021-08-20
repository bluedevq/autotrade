/**
 * Management user
 * @type {{deleteConfirm: UserController.deleteConfirm}}
 */
let UserController = {
    // Delete user
    deleteConfirm: function (url, username) {
        $('.delete-confirm form').attr('action', url);
        $('.delete-confirm .username').empty().text(username);
        $('.delete-confirm').modal('show');
    }
};
