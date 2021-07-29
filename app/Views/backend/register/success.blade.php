@extends('layouts.backend.register')
@section('content')
    <div class="row text-center mx-auto col-md-6 mt-4">
        <img class="img-fluid img-logo col-12" src="{{ public_url('images/backend/logo.png') }}" alt="">
    </div>
    <div class="row mx-auto col-md-4 d-flex align-items-center mt-4">
        <div class="row">
            <h2>Đăng ký thành công</h2>
        </div>
        <div class="mt-4">
            <div class="row">
                <div class="col-12">
                    <p class="mt-4">
                        Chúng tôi đã gửi một liên kết xác nhận tới <a href="mailto:{{ $registEmail }}" class="text-info">{{ $registEmail }}</a>.
                        <br>
                        Vui lòng truy cập vào mail để xác nhận.
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
