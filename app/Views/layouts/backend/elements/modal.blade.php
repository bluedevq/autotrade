<div class="toast-message-custom toast-message-error border-radius-custom toast fade position-absolute end-10 bg-danger-custom hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body border-radius-custom">
        <span class="toast-message-body"></span><button type="button" class="btn-close position-absolute end-10" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>
<div class="toast-message-custom toast-message-success border-radius-custom toast fade position-absolute end-10 bg-success-custom hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body border-radius-custom">
        <span class="toast-message-body"></span><button type="button" class="btn-close position-absolute end-10" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>
@if(session()->has('success'))
    @php $successMsg = session()->get('success'); @endphp
    @foreach($successMsg as $msg)
        <script type="application/javascript">
            AdminController.notification.success.push('{{ $msg }}');
        </script>
    @endforeach
@endif
@if (!empty($errors) && count($errors) > 0)
    @foreach ($errors->all() as $error)
        <script type="application/javascript">
            AdminController.notification.errors.push('{{ $error }}');
        </script>
    @endforeach
@endif
<script type="application/javascript">
    $(document).ready(function () {
        if (AdminController.notification.errors.length != 0) {
            let errorsHtml = '';
            $('.toast-message-error .toast-message-body').empty();
            for (let i = 0; i < AdminController.notification.errors.length; i++) {
                errorsHtml += '<i class="fas fa-exclamation-triangle">&nbsp;</i>' + AdminController.notification.errors[i];
            }
            $('.toast-message-error .toast-message-body').html(errorsHtml);
            $('.toast-message-error').toast('show');
        }

        if (AdminController.notification.success.length != 0) {
            let successHtml = '';
            $('.toast-message-success .toast-message-body').empty();
            for (let i = 0; i < AdminController.notification.success.length; i++) {
                successHtml += '<i class="fas fa-check">&nbsp;</i>' + AdminController.notification.success[i];
            }
            $('.toast-message-success .toast-message-body').html(successHtml);
            $('.toast-message-success').toast('show');
        }
    })
</script>
