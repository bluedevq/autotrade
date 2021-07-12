@extends('layouts.backend.default')
@section('content')
    <div class="wrapper mt-5">
        @if(!isset($userInfo) || blank($userInfo))
            @include('backend.bot._login')
        @else
            @include('backend.bot._user_info')
        @endif
    </div>
@stop
