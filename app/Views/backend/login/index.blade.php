@extends('layouts.backend.auth')
@section('content')
    <div class="wrapper">
        <div class="container mt-5">
            <div class="row pt-2 text-center col-12">
                <h3 class="mt-5">Đăng nhập vào hệ thống</h3>
            </div>
            <form method="post" action="{{ route('backend.auth') }}" class="form-horizontal mt-5" enctype="multipart/form-data">
                @csrf

                @include('layouts.backend.elements.messages')

                <div class="m-2">
                    <div class="row mx-auto col-md-6">
                        <div class="col-md-4">
                            <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
                        </div>
                    </div>
                </div>

                <div class="m-2">
                    <div class="row mx-auto col-md-6">
                        <div class="col-md-4">
                            <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" name="password" class="form-control" id="password">
                        </div>
                    </div>
                </div>

                <div class="m-4">
                    <div class="row mx-auto col-md-6">
                        <div class="col-md-4 col-0">&nbsp;</div>
                        <div class="col-md-4 col-12">
                            <button class="btn btn-lg btn-danger btn-block col-12" name="submit" type="submit">
                                <span class="ls-icon ls-icon-login" aria-hidden="true">Đăng nhập</span>
                            </button>
                            <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
