@extends('layouts.backend.register')
@section('content')
    @include('layouts.backend.elements.logo')
    <div class="row mx-auto col-md-6 d-flex align-items-center mt-4 text-center">
        <div class="text-success-custom">
            <div class="verify-status"><span class="fas fa-check-circle"></span></div>
            <h2>Đổi mật khẩu thành công</h2>
        </div>
        <div class="mt-4">
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
@stop
@push('scripts')
    <script type="application/javascript">
        BotController.verifyCount(5, '{{ route('backend.login') }}');
    </script>
@endpush
