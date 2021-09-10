@extends('layouts.backend.default')
@section('content')
    <div class="container">
        <h3>Chỉnh sửa thông tin cá nhân</h3>
        <div class="row mt-2">
            <form action="{{ route('user.valid') }}" method="POST" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
                @csrf
                <div class="row mt-2">
                    <div class="col-md-2 col-12">
                        <label for="email" class="form-label fw-bold" aria-hidden="true">Email</label>
                    </div>
                    <div class="col-md-4 col-12">
                        <span class="form-control">{{ $entity->email }}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2 col-12">
                        <label for="password" class="form-label fw-bold" aria-hidden="true">Mật khẩu</label>
                    </div>
                    <div class="col-md-4 col-12">
                        <input type="password" name="password" class="form-control" id="password" value="">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2 col-12">
                        <label for="name" class="form-label fw-bold" aria-hidden="true">Tên người dùng</label>
                    </div>
                    <div class="col-md-4 col-12">
                        <input type="text" name="name" class="form-control" id="name" value="{{ $entity->name }}">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2 col-12">
                        <label for="phone" class="form-label fw-bold" aria-hidden="true">Số điện thoại</label>
                    </div>
                    <div class="col-md-4 col-12">
                        <input type="text" name="phone" class="form-control" id="phone" value="{{ $entity->phone }}">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2 col-12">
                        <label for="address" class="form-label fw-bold" aria-hidden="true">Địa chỉ</label>
                    </div>
                    <div class="col-md-4 col-12">
                        <textarea class="form-control" name="address" id="address" rows="5">{!! $entity->address !!}</textarea>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2 col-12">&nbsp;</div>
                    <div class="col-md-2 col-12 mt-sp-2">
                        <input type="hidden" name="id" value="{{ $entity->id }}">
                        <input type="hidden" name="profile" value="true">
                        <button class="btn btn-outline-primary-custom col-12" type="submit"><span class="fas fa-save" aria-hidden="true">&nbsp;</span>Lưu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
