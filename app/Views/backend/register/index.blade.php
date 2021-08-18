@extends('layouts.backend.register')
@section('content')
    @include('layouts.backend.elements.logo')
    <div class="row mx-auto col-md-4 d-flex align-items-center">
        <form method="post" action="{{ route('backend.register.valid') }}" class="form-horizontal mt-5 register" enctype="multipart/form-data">
            @csrf
            <div class="row text-center">
                <h3>Tạo tài khoản {{ env('APP_NAME') }}</h3>
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
            <div class="mt-2">
                <div class="row">
                    <div class="col-12">
                        <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
                    </div>
                    <div class="col-12 input-group">
                        <input type="password" name="password" class="form-control" id="password" aria-label="password" maxlength="20">
                        <span class="input-group-text password-hover" onclick="BotController.togglePassword(this)"><span class="fas fa-eye-slash show-hide-password"></span></span>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <div class="row">
                    <div class="col-12">
                        <label for="name" class="form-label" aria-hidden="true">Tên *</label>
                    </div>
                    <div class="col-12 input-group">
                        <input type="text" name="name" class="form-control" id="name" aria-label="name" value="{{ old('name') }}" maxlength="255">
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-lg btn-danger btn-block col-12" type="submit" onclick="showLoading()">Đăng ký</button>
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
