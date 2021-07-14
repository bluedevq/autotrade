@extends('layouts.backend.default')
@section('content')
    <div class="wrapper">
        <div class="mt-2">&nbsp;</div>
        @include('layouts.backend.elements.messages')
        @if(isset($require2Fa) && $require2Fa)
            @include('backend.bot_ares._2falogin')
        @else
            @if(!isset($userInfo) || blank($userInfo))
                @include('backend.bot_ares._login')
            @else
                @include('backend.bot_ares._user_info')
            @endif
        @endif
    </div>
@stop
