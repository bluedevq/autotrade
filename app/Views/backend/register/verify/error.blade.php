<div class="row mx-auto col-md-6 d-flex align-items-center mt-4 text-center">
    <div class="text-danger-custom">
        <div class="verify-status"><span class="fas fa-times-circle"></span></div>
        <h2>Kích hoạt không thành công</h2>
    </div>
    <div class="mt-4">
        <div class="row">
            <div class="col-12">
                <p class="mt-4">
                    {!! $verifyErrors !!}
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                Quay trở lại trang đăng nhập trong <span class="verify-count text-info"></span>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <div class="row">
            <div class="col-12 text-danger-custom register">
                <a href="{{ route('backend.login') }}">Đăng nhập vào hệ thống</a>
            </div>
        </div>
    </div>
</div>
