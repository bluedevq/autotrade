@extends('layouts.backend.default')
@section('content')
    <div class="row mt-2 mx-auto col-md-8">
        <h3>Chào mừng bạn đến với {{ env('APP_NAME') }} !</h3>
        <br>
        <p class="mt-5">
            Chúng tôi chuyên cung cấp các giải pháp về bot autotrade cho các bạn.
            <br>
            {{ env('APP_NAME') }} là một trong những sản phẩm độc quyền của chúng tôi.
            <br>
            Kính mời các bạn trải nghiệm <a class="btn btn-danger" href="{{ route('bot.index') }}">tại đây</a>
        </p>
    </div>
@stop
