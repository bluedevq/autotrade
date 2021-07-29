@extends('layouts.backend.register')
@section('content')
    @include('layouts.backend.elements.logo')
    <div class="row mx-auto col-md-4 d-flex align-items-center mt-4">
        <div class="row">
            <h2>Yêu cầu mật khẩu mới thành công!</h2>
        </div>
        <div class="mt-4">
            <div class="row">
                <div class="col-12">
                    <p class="mt-4">
                        Một liên kết xác nhận đã được gửi đến hòm thư điện tử <a href="mailto:{{ $email }}" class="text-info">{{ $email }}</a> của bạn.
                        <br>
                        Vui lòng chờ trong ít phút để nhận liên kết để đặt lại mật khẩu.
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <div class="row">
                <div class="col-12 text-start text-danger-custom register">
                    <a href="{{ route('backend.login') }}">Đăng nhập vào hệ thống</a>
                </div>
            </div>
        </div>
    </div>
@stop
