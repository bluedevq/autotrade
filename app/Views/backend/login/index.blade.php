@extends('layouts.backend.auth')
@section('content')
    <div class="wrapper">
        <div class="container mt-5">
            <div class="row pt-2 text-center col-12">
                <div class="mx-auto">
                    <img src="{{ public_url('images/backend/logo.jpg') }}" alt="Aresbo" style="height: 100px;">
                </div>
                <h3 class="mt-5">Đăng nhập vào hệ thống Sparta</h3>
            </div>
            <form method="post" action="{{ route('backend.auth') }}" class="form-horizontal mt-5" enctype="multipart/form-data">
                @csrf

                @include('layouts.backend.elements.messages')

                <div class="container m-2">
                    <div class="row mx-auto col-md-6">
                        <div class="col-md-4">
                            <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
                        </div>
                    </div>
                </div>

                <div class="container m-2">
                    <div class="row mx-auto col-md-6">
                        <div class="col-md-4">
                            <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" name="password" class="form-control" id="password">
                        </div>
                    </div>
                </div>

                <div class="container m-2">
                    <div class="row mx-auto col-md-6">
                        <button class="btn btn-lg btn-danger btn-block mx-auto col-md-4" name="submit" type="submit">
                            <span class="ls-icon ls-icon-login" aria-hidden="true">Đăng nhập</span>
                        </button>
                        <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
