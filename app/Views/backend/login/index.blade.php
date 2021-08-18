@extends('layouts.backend.auth')
@section('content')
    @include('layouts.backend.elements.logo')
    <div class="row mx-auto col-md-4 d-flex align-items-center">
        <form method="post" action="{{ route('backend.auth') }}" class="form-horizontal mt-5" enctype="multipart/form-data">
            @csrf
            <div class="row text-center">
                <h3>Đăng nhập vào hệ thống</h3>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
                    </div>
                    <div class="col-12">
                        <input type="text" name="email" class="form-control" id="email" value="{{ old('email') }}">
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <div class="row">
                    <div class="col-12">
                        <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
                    </div>
                    <div class="col-12 input-group">
                        <input type="password" name="password" class="form-control" id="password" aria-label="password">
                        <span class="input-group-text password-hover" onclick="BotController.togglePassword(this)"><span class="fas fa-eye-slash show-hide-password"></span></span>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-lg btn-danger btn-block col-12" type="submit" onclick="showLoading()"><span class="fas fa-sign-in-alt">&nbsp;</span>Đăng nhập</button>
                        <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                    </div>
                </div>
            </div>
            @if (false)
            <div class="mt-4 register">
                <div class="row">
                    <div class="col-12 text-start">
                        <a href="{{ route('backend.register') }}">Đăng ký mới</a>
                    </div>
                </div>
            </div>
            <div class="mt-2 register">
                <div class="row">
                    <div class="col-12 text-start">
                        <a href="{{ route('backend.password.forgot') }}">Quên mật khẩu?</a>
                    </div>
                </div>
            </div>
            @endif
        </form>
    </div>
@stop
