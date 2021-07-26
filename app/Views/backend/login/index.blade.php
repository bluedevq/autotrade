@extends('layouts.backend.auth')
@section('content')
    <div class="row text-center mx-auto col-md-4">
        <img class="img-fluid col-12" style="height:144px;object-fit:cover;" src="{{ public_url('images/backend/logo.png') }}" alt="">
    </div>
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
                        <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}">
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
                        <span class="input-group-text password-hover" onclick="BotController.showHidePassword(this)"><span class="fas fa-eye show-hide-password"></span></span>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-lg btn-danger btn-block col-12" type="submit" onclick="showLoading()">
                            <span class="fas fa-sign-in-alt">&nbsp;</span>Đăng nhập
                        </button>
                        <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
