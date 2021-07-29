@extends('layouts.backend.forgot_password')
@section('content')
    @include('layouts.backend.elements.logo')
    <div class="row mx-auto col-md-4 d-flex align-items-center">
        <form method="post" action="{{ route('backend.password.forgot.valid') }}" class="form-horizontal mt-5 register" enctype="multipart/form-data">
            @csrf
            <div class="row text-center">
                <h3>Quên mật khẩu</h3>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
                    </div>
                    <div class="col-12">
                        <input type="text" name="email" class="form-control" id="email" aria-label="email" value="{{ old('email') }}" maxlength="255">
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-lg btn-danger btn-block col-12" type="submit" onclick="showLoading()">Gửi lại email</button>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12 text-start text-danger-custom">
                        <a href="{{ route('backend.login') }}">Đăng nhập vào hệ thống</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
